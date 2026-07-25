<?php
declare(strict_types=1);

namespace FCP\Horizon\Admin;

use FCP\Horizon\Data\AuditLogRepository;
use FCP\Horizon\Data\CommunicationRepository;
use FCP\Horizon\Data\EnquiryNoteRepository;
use FCP\Horizon\Data\EnquiryRepository;
use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Data\SupabaseClient;
use FCP\Horizon\Data\SupabaseException;
use FCP\Horizon\Domain\EnquiryStatus;
use FCP\Horizon\Domain\PublicReference;
use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;

/**
 * Back-office Horizon (Sprint 4) — LECTURE principalement.
 *
 * Écritures explicitement autorisées (arbitrage 1) : changement de statut
 * contrôlé + ajout d'une note interne courte. Tout le reste est en lecture.
 * Accès protégé par capacité (moindre privilège) + nonce sur chaque écriture.
 */
final class BackOffice
{
    public const CAP = 'fcp_horizon_access';
    private const MENU = 'fcp-horizon';
    private const DETAIL = 'fcp-horizon-enquiry';

    public function __construct(private Config $config)
    {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_post_fcp_update_status', [$this, 'handleUpdateStatus']);
        add_action('admin_post_fcp_add_note', [$this, 'handleAddNote']);
        add_action('admin_post_fcp_requeue_message', [$this, 'handleRequeueMessage']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'Horizon',
            'Horizon',
            self::CAP,
            self::MENU,
            [$this, 'renderDashboard'],
            'dashicons-tickets-alt',
            26
        );
        add_submenu_page(self::MENU, 'Tableau de bord', 'Tableau de bord', self::CAP, self::MENU, [$this, 'renderDashboard']);
        add_submenu_page(self::MENU, 'Demandes', 'Demandes', self::CAP, self::MENU . '-list', [$this, 'renderList']);
        // Fiche : page routable mais absente du menu.
        add_submenu_page('', 'Demande', 'Demande', self::CAP, self::DETAIL, [$this, 'renderDetail']);
    }

    // --- Pages ---

    public function renderDashboard(): void
    {
        $this->guard();
        echo '<div class="wrap"><h1>Horizon — Tableau de bord</h1>';
        try {
            $counts = (new EnquiryRepository($this->client()))->countByStatus();
        } catch (SupabaseException $e) {
            $this->notice('Lecture impossible pour le moment.', 'error');
            echo '</div>';
            return;
        }
        $total = array_sum($counts);
        echo '<p>Total des demandes : <strong>' . (int) $total . '</strong></p>';
        echo '<table class="widefat striped" style="max-width:520px"><thead><tr><th>Statut</th><th>Nombre</th></tr></thead><tbody>';
        foreach (EnquiryStatus::all() as $key => $label) {
            echo '<tr><td>' . esc_html($label) . '</td><td>' . (int) ($counts[$key] ?? 0) . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=' . self::MENU . '-list')) . '">Voir les demandes</a></p>';
        echo '</div>';
    }

    public function renderList(): void
    {
        $this->guard();
        echo '<div class="wrap"><h1>Horizon — Demandes</h1>';
        $this->maybeShowNotice();
        try {
            $rows = (new EnquiryRepository($this->client()))->listRecent(50, 0);
        } catch (SupabaseException $e) {
            $this->notice('Lecture impossible pour le moment.', 'error');
            echo '</div>';
            return;
        }
        if ($rows === []) {
            echo '<p>Aucune demande pour l’instant.</p></div>';
            return;
        }
        echo '<table class="widefat striped"><thead><tr>'
            . '<th>Référence</th><th>Client</th><th>Trajet</th><th>Date service</th><th>Statut</th><th></th>'
            . '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $contact = $this->toOne($row['contacts'] ?? null);
            $details = $this->toOne($row['enquiry_details'] ?? null);
            $ref = (string) ($row['public_reference'] ?? '');
            $client = trim(((string) ($contact['first_name'] ?? '')) . ' ' . ((string) ($contact['last_name'] ?? '')));
            $trajet = trim((string) ($details['origin'] ?? '')) . ' → ' . trim((string) ($details['destination'] ?? ''));
            $url = admin_url('admin.php?page=' . self::DETAIL . '&ref=' . rawurlencode($ref));
            echo '<tr>'
                . '<td><a href="' . esc_url($url) . '"><code>' . esc_html($ref) . '</code></a></td>'
                . '<td>' . esc_html($client !== '' ? $client : '—') . '</td>'
                . '<td>' . esc_html($trajet !== ' → ' ? $trajet : '—') . '</td>'
                . '<td>' . esc_html((string) ($details['service_date'] ?? '—')) . '</td>'
                . '<td>' . esc_html(EnquiryStatus::label((string) ($row['status'] ?? ''))) . '</td>'
                . '<td><a class="button button-small" href="' . esc_url($url) . '">Voir</a></td>'
                . '</tr>';
        }
        echo '</tbody></table></div>';
    }

    public function renderDetail(): void
    {
        $this->guard();
        $ref = isset($_GET['ref']) ? sanitize_text_field(wp_unslash((string) $_GET['ref'])) : '';
        echo '<div class="wrap"><h1>Demande ' . esc_html($ref) . '</h1>';
        $this->maybeShowNotice();

        if (!PublicReference::isValid($ref)) {
            $this->notice('Référence invalide.', 'error');
            echo '</div>';
            return;
        }

        try {
            $client = $this->client();
            $enquiry = (new EnquiryRepository($client))->findFullByReference($ref);
            if ($enquiry === null) {
                $this->notice('Demande introuvable.', 'error');
                echo '</div>';
                return;
            }
            $id = (string) $enquiry['id'];
            $communications = (new CommunicationRepository($client))->listForEnquiry($id);
            $messages = (new MessageRepository($client))->listForEnquiry($id);
            $journal = (new AuditLogRepository($client))->listForRecord($id);
            $notes = (new EnquiryNoteRepository($client))->listForEnquiry($id);
        } catch (SupabaseException $e) {
            $this->notice('Lecture impossible pour le moment.', 'error');
            echo '</div>';
            return;
        }

        $contact = $this->toOne($enquiry['contacts'] ?? null);
        $details = $this->toOne($enquiry['enquiry_details'] ?? null);
        $status = (string) ($enquiry['status'] ?? 'new');

        // Contact
        echo '<h2>Client</h2><table class="widefat" style="max-width:640px"><tbody>';
        $this->kv('Nom', trim(((string) ($contact['first_name'] ?? '')) . ' ' . ((string) ($contact['last_name'] ?? ''))));
        $this->kv('E-mail', (string) ($contact['email'] ?? ''));
        $this->kv('Téléphone', (string) ($contact['phone'] ?? ''));
        $this->kv('Canal préféré', (string) ($contact['preferred_channel'] ?? ''));
        $this->kv('Consentement marketing', !empty($contact['consent_marketing']) ? 'Oui' : 'Non');
        echo '</tbody></table>';

        // Mission
        echo '<h2>Mission</h2><table class="widefat" style="max-width:640px"><tbody>';
        $this->kv('Trajet', trim((string) ($details['origin'] ?? '')) . ' → ' . trim((string) ($details['destination'] ?? '')));
        $this->kv('Date / heure', trim(((string) ($details['service_date'] ?? '')) . ' ' . ((string) ($details['service_time'] ?? ''))));
        $this->kv('Passagers', (string) ($details['passengers'] ?? ''));
        $this->kv('Bagages', (string) ($details['luggage'] ?? ''));
        $this->kv('Vol', (string) ($details['flight_number'] ?? ''));
        $this->kv('Train', (string) ($details['train_number'] ?? ''));
        $this->kv('Message', (string) ($details['notes'] ?? ''));
        echo '</tbody></table>';

        // Statut (écriture contrôlée)
        echo '<h2>Statut</h2>';
        echo '<p>Statut actuel : <strong>' . esc_html(EnquiryStatus::label($status)) . '</strong></p>';
        $next = EnquiryStatus::nextStatuses($status);
        if ($next !== []) {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-bottom:1.5rem">';
            wp_nonce_field('fcp_update_status_' . $ref);
            echo '<input type="hidden" name="action" value="fcp_update_status">';
            echo '<input type="hidden" name="ref" value="' . esc_attr($ref) . '">';
            echo '<select name="status">';
            foreach ($next as $s) {
                echo '<option value="' . esc_attr($s) . '">' . esc_html(EnquiryStatus::label($s)) . '</option>';
            }
            echo '</select> <button class="button button-primary">Changer le statut</button>';
            echo '</form>';
        } else {
            echo '<p><em>Statut terminal — aucune transition disponible.</em></p>';
        }

        // Lien WhatsApp (wa.me) — suivi synthétique
        echo '<h2>Lien WhatsApp (wa.me)</h2>';
        $this->renderRows($communications, ['channel' => 'Canal', 'type' => 'Type', 'status' => 'Statut', 'created_at' => 'Date']);

        // Messages transactionnels (e-mail / futur WhatsApp) — source de vérité
        echo '<h2>Messages transactionnels</h2>';
        $this->renderMessages($messages, $ref);

        // Notes internes (écriture autorisée)
        echo '<h2>Notes internes</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-bottom:1rem">';
        wp_nonce_field('fcp_add_note_' . $ref);
        echo '<input type="hidden" name="action" value="fcp_add_note">';
        echo '<input type="hidden" name="ref" value="' . esc_attr($ref) . '">';
        echo '<textarea name="body" rows="2" style="width:100%;max-width:640px" maxlength="500" placeholder="Note interne courte…" required></textarea><br>';
        echo '<button class="button">Ajouter la note</button>';
        echo '</form>';
        if ($notes === []) {
            echo '<p>Aucune note.</p>';
        } else {
            echo '<ul>';
            foreach ($notes as $n) {
                echo '<li><strong>' . esc_html((string) ($n['author'] ?? '')) . '</strong> — '
                    . esc_html((string) ($n['created_at'] ?? '')) . '<br>' . esc_html((string) ($n['body'] ?? '')) . '</li>';
            }
            echo '</ul>';
        }

        // Journal
        echo '<h2>Journal</h2>';
        $this->renderRows($journal, ['action' => 'Événement', 'created_at' => 'Date']);

        echo '<p><a href="' . esc_url(admin_url('admin.php?page=' . self::MENU . '-list')) . '">← Retour à la liste</a></p>';
        echo '</div>';
    }

    // --- Écritures (POST) ---

    public function handleUpdateStatus(): void
    {
        $this->guard();
        $ref = isset($_POST['ref']) ? sanitize_text_field(wp_unslash((string) $_POST['ref'])) : '';
        check_admin_referer('fcp_update_status_' . $ref);

        $newStatus = isset($_POST['status']) ? sanitize_text_field(wp_unslash((string) $_POST['status'])) : '';

        if (!PublicReference::isValid($ref) || !EnquiryStatus::isValid($newStatus)) {
            $this->redirectDetail($ref, 'error');
        }

        try {
            $client = $this->client();
            $repo = new EnquiryRepository($client);
            $enquiry = $repo->findFullByReference($ref);
            if ($enquiry === null) {
                $this->redirectDetail($ref, 'error');
            }
            $id = (string) $enquiry['id'];
            $current = (string) ($enquiry['status'] ?? 'new');

            if (!EnquiryStatus::canTransition($current, $newStatus)) {
                $this->redirectDetail($ref, 'error'); // transition non autorisée
            }

            $repo->updateStatusById($id, $newStatus);
            (new AuditLogRepository($client))->record(
                'enquiry.status_changed',
                'enquiries',
                $id,
                ['from' => $current, 'to' => $newStatus, 'actor' => $this->currentActor()],
            );
        } catch (SupabaseException $e) {
            Logger::error('Échec changement de statut', ['code' => $e->getCode()]);
            $this->redirectDetail($ref, 'error');
        }

        $this->redirectDetail($ref, 'status');
    }

    public function handleAddNote(): void
    {
        $this->guard();
        $ref = isset($_POST['ref']) ? sanitize_text_field(wp_unslash((string) $_POST['ref'])) : '';
        check_admin_referer('fcp_add_note_' . $ref);

        $body = isset($_POST['body']) ? sanitize_textarea_field(wp_unslash((string) $_POST['body'])) : '';
        $body = trim(mb_substr($body, 0, 500));

        if (!PublicReference::isValid($ref) || $body === '') {
            $this->redirectDetail($ref, 'error');
        }

        try {
            $client = $this->client();
            $id = (new EnquiryRepository($client))->findIdByReference($ref);
            if ($id === null) {
                $this->redirectDetail($ref, 'error');
            }
            (new EnquiryNoteRepository($client))->add($id, $this->currentActor(), $body);
            (new AuditLogRepository($client))->record(
                'note.added',
                'enquiry_notes',
                $id,
                ['actor' => $this->currentActor()],
            );
        } catch (SupabaseException $e) {
            Logger::error('Échec ajout note', ['code' => $e->getCode()]);
            $this->redirectDetail($ref, 'error');
        }

        $this->redirectDetail($ref, 'note');
    }

    /** Relance manuelle contrôlée d'un message échoué. */
    public function handleRequeueMessage(): void
    {
        $this->guard();
        $ref = isset($_POST['ref']) ? sanitize_text_field(wp_unslash((string) $_POST['ref'])) : '';
        check_admin_referer('fcp_requeue_' . $ref);

        $messageId = isset($_POST['message_id']) ? sanitize_text_field(wp_unslash((string) $_POST['message_id'])) : '';
        if (!PublicReference::isValid($ref) || $messageId === '') {
            $this->redirectDetail($ref, 'error');
        }

        try {
            (new MessageRepository($this->client()))->requeue($messageId);
            // Réveille l'outbox pour un renvoi rapide.
            wp_schedule_single_event(time(), 'fcp_horizon_process_outbox_now');
            if (function_exists('spawn_cron')) {
                spawn_cron();
            }
        } catch (SupabaseException $e) {
            Logger::error('Échec relance message', ['code' => $e->getCode()]);
            $this->redirectDetail($ref, 'error');
        }

        $this->redirectDetail($ref, 'requeued');
    }

    // --- Helpers ---

    private function guard(): void
    {
        if (!current_user_can(self::CAP)) {
            wp_die('Accès refusé.');
        }
    }

    private function client(): SupabaseClient
    {
        return new SupabaseClient($this->config);
    }

    private function currentActor(): string
    {
        $user = wp_get_current_user();
        return $user && $user->user_login ? (string) $user->user_login : 'inconnu';
    }

    /** @param mixed $value @return array<string,mixed> */
    private function toOne($value): array
    {
        if (is_array($value)) {
            if (isset($value[0]) && is_array($value[0])) {
                return $value[0];
            }
            return $value;
        }
        return [];
    }

    private function kv(string $label, string $value): void
    {
        echo '<tr><th style="text-align:left;width:180px">' . esc_html($label) . '</th><td>'
            . esc_html($value !== '' ? $value : '—') . '</td></tr>';
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,string> $columns clé => libellé
     */
    private function renderRows(array $rows, array $columns): void
    {
        if ($rows === []) {
            echo '<p>—</p>';
            return;
        }
        echo '<table class="widefat striped"><thead><tr>';
        foreach ($columns as $label) {
            echo '<th>' . esc_html($label) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($columns as $key => $_) {
                $cell = $row[$key] ?? '';
                if (is_array($cell)) {
                    $cell = wp_json_encode($cell);
                }
                echo '<td>' . esc_html((string) $cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     */
    private function renderMessages(array $rows, string $ref): void
    {
        if ($rows === []) {
            echo '<p>—</p>';
            return;
        }
        echo '<table class="widefat striped"><thead><tr>'
            . '<th>Sens</th><th>Canal</th><th>Finalité</th><th>Destinataire</th>'
            . '<th>Statut</th><th>Horodatage</th><th>Erreur</th><th></th>'
            . '</tr></thead><tbody>';
        foreach ($rows as $m) {
            $status = (string) ($m['status'] ?? '');
            $stamp = (string) ($m['sent_at'] ?? $m['failed_at'] ?? $m['created_at'] ?? '');
            $error = trim((string) ($m['error_code'] ?? '') . ' ' . (string) ($m['error_message'] ?? ''));
            echo '<tr>'
                . '<td>' . esc_html($m['direction'] === 'inbound' ? '← entrant' : '→ sortant') . '</td>'
                . '<td>' . esc_html((string) ($m['channel'] ?? '')) . '</td>'
                . '<td>' . esc_html((string) ($m['message_type'] ?? '')) . '</td>'
                . '<td>' . esc_html((string) ($m['recipient'] ?? '')) . '</td>'
                . '<td>' . esc_html($status) . '</td>'
                . '<td>' . esc_html($stamp) . '</td>'
                . '<td>' . esc_html($error !== '' ? $error : '—') . '</td>'
                . '<td>';
            if ($status === 'failed') {
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin:0">';
                wp_nonce_field('fcp_requeue_' . $ref);
                echo '<input type="hidden" name="action" value="fcp_requeue_message">';
                echo '<input type="hidden" name="ref" value="' . esc_attr($ref) . '">';
                echo '<input type="hidden" name="message_id" value="' . esc_attr((string) ($m['id'] ?? '')) . '">';
                echo '<button class="button button-small">Relancer</button>';
                echo '</form>';
            } else {
                echo '—';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }

    private function redirectDetail(string $ref, string $notice): void
    {
        wp_safe_redirect(admin_url('admin.php?page=' . self::DETAIL . '&ref=' . rawurlencode($ref) . '&fcp_notice=' . $notice));
        exit;
    }

    private function maybeShowNotice(): void
    {
        $notice = isset($_GET['fcp_notice']) ? sanitize_key((string) $_GET['fcp_notice']) : '';
        $map = [
            'status'   => ['Statut mis à jour.', 'success'],
            'note'     => ['Note ajoutée.', 'success'],
            'requeued' => ['Message remis en file d’envoi.', 'success'],
            'error'    => ['Action impossible (droits, transition ou données invalides).', 'error'],
        ];
        if (isset($map[$notice])) {
            $this->notice($map[$notice][0], $map[$notice][1]);
        }
    }

    private function notice(string $message, string $type): void
    {
        $class = $type === 'error' ? 'notice-error' : 'notice-success';
        echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
}
