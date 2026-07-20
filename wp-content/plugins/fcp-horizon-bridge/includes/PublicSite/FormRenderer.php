<?php
declare(strict_types=1);

namespace FCP\Horizon\PublicSite;

/**
 * Rendu du formulaire intelligent de demande (Mobilité).
 *
 * Présentation uniquement : aucune règle métier ici. La validation fait
 * autorité côté serveur. Le formulaire s'intègre via le shortcode
 * [fcp_enquiry_form] et reste simple, élégant, mobile-first (ADN).
 */
final class FormRenderer
{
    public const NONCE_ACTION = 'fcp_enquiry';

    public function render(): string
    {
        $nonce = wp_create_nonce(self::NONCE_ACTION);

        ob_start();
        ?>
        <form class="fcp-form" id="fcp-enquiry-form" novalidate
              aria-describedby="fcp-form-intro">
            <p id="fcp-form-intro" class="fcp-form__intro">
                <?php esc_html_e('Confiez-nous votre demande. Nous revenons vers vous rapidement.', 'fcp-horizon'); ?>
            </p>

            <input type="hidden" name="_fcp_nonce" value="<?php echo esc_attr($nonce); ?>">
            <input type="hidden" name="category" value="mobility">
            <input type="hidden" name="locale" value="fr">

            <!-- Honeypot anti-spam : ne pas remplir (masqué). -->
            <div class="fcp-hp" aria-hidden="true">
                <label>Ne pas remplir
                    <input type="text" name="company_website" tabindex="-1" autocomplete="off">
                </label>
            </div>

            <fieldset class="fcp-form__group">
                <legend><?php esc_html_e('Votre profil', 'fcp-horizon'); ?></legend>
                <label><input type="radio" name="profile" value="individual" checked> <?php esc_html_e('Particulier', 'fcp-horizon'); ?></label>
                <label><input type="radio" name="profile" value="company"> <?php esc_html_e('Société', 'fcp-horizon'); ?></label>
                <p class="fcp-field" data-when-company hidden>
                    <label for="fcp-org"><?php esc_html_e('Nom de la société', 'fcp-horizon'); ?></label>
                    <input type="text" id="fcp-org" name="organization_name" autocomplete="organization">
                </p>
            </fieldset>

            <div class="fcp-form__grid">
                <p class="fcp-field">
                    <label for="fcp-first"><?php esc_html_e('Prénom', 'fcp-horizon'); ?> *</label>
                    <input type="text" id="fcp-first" name="first_name" required autocomplete="given-name">
                </p>
                <p class="fcp-field">
                    <label for="fcp-last"><?php esc_html_e('Nom', 'fcp-horizon'); ?> *</label>
                    <input type="text" id="fcp-last" name="last_name" required autocomplete="family-name">
                </p>
                <p class="fcp-field">
                    <label for="fcp-email"><?php esc_html_e('E-mail', 'fcp-horizon'); ?> *</label>
                    <input type="email" id="fcp-email" name="email" required autocomplete="email" inputmode="email">
                </p>
                <p class="fcp-field">
                    <label for="fcp-phone"><?php esc_html_e('Téléphone', 'fcp-horizon'); ?> *</label>
                    <input type="tel" id="fcp-phone" name="phone" required autocomplete="tel" inputmode="tel">
                </p>
                <p class="fcp-field">
                    <label for="fcp-date"><?php esc_html_e('Date', 'fcp-horizon'); ?> *</label>
                    <input type="date" id="fcp-date" name="service_date" required>
                </p>
                <p class="fcp-field">
                    <label for="fcp-time"><?php esc_html_e('Heure', 'fcp-horizon'); ?></label>
                    <input type="time" id="fcp-time" name="service_time">
                </p>
                <p class="fcp-field">
                    <label for="fcp-origin"><?php esc_html_e('Départ', 'fcp-horizon'); ?> *</label>
                    <input type="text" id="fcp-origin" name="origin" required>
                </p>
                <p class="fcp-field">
                    <label for="fcp-destination"><?php esc_html_e('Destination', 'fcp-horizon'); ?> *</label>
                    <input type="text" id="fcp-destination" name="destination" required>
                </p>
                <p class="fcp-field">
                    <label for="fcp-passengers"><?php esc_html_e('Passagers', 'fcp-horizon'); ?> *</label>
                    <input type="number" id="fcp-passengers" name="passengers" min="1" max="60" value="1" required inputmode="numeric">
                </p>
                <p class="fcp-field">
                    <label for="fcp-luggage"><?php esc_html_e('Bagages', 'fcp-horizon'); ?></label>
                    <input type="number" id="fcp-luggage" name="luggage" min="0" inputmode="numeric">
                </p>
                <p class="fcp-field">
                    <label for="fcp-flight"><?php esc_html_e('N° de vol', 'fcp-horizon'); ?></label>
                    <input type="text" id="fcp-flight" name="flight_number" autocomplete="off">
                </p>
                <p class="fcp-field">
                    <label for="fcp-train"><?php esc_html_e('N° de train', 'fcp-horizon'); ?></label>
                    <input type="text" id="fcp-train" name="train_number" autocomplete="off">
                </p>
                <p class="fcp-field">
                    <label for="fcp-channel"><?php esc_html_e('Canal préféré', 'fcp-horizon'); ?></label>
                    <select id="fcp-channel" name="preferred_channel">
                        <option value="email"><?php esc_html_e('E-mail', 'fcp-horizon'); ?></option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="phone"><?php esc_html_e('Téléphone', 'fcp-horizon'); ?></option>
                    </select>
                </p>
            </div>

            <p class="fcp-field">
                <label for="fcp-message"><?php esc_html_e('Votre message', 'fcp-horizon'); ?></label>
                <textarea id="fcp-message" name="message" rows="3" maxlength="1500"></textarea>
            </p>

            <!-- Consentements : traitement (nécessaire) SÉPARÉ du marketing (facultatif). -->
            <p class="fcp-field fcp-consent">
                <label>
                    <input type="checkbox" name="consent_processing" value="1" required>
                    <?php esc_html_e('J’accepte que ma demande soit traitée par French Class Prestige.', 'fcp-horizon'); ?> *
                </label>
            </p>
            <p class="fcp-field fcp-consent">
                <label>
                    <input type="checkbox" name="consent_marketing" value="1">
                    <?php esc_html_e('J’accepte de recevoir les actualités de la Maison (facultatif).', 'fcp-horizon'); ?>
                </label>
            </p>

            <div class="fcp-form__errors" id="fcp-form-errors" role="alert" aria-live="assertive" hidden></div>

            <div class="fcp-form__actions">
                <button type="button" class="fcp-cta" id="fcp-review-btn"><?php esc_html_e('Vérifier ma demande', 'fcp-horizon'); ?></button>
            </div>

            <!-- Récapitulatif avant envoi -->
            <section class="fcp-summary" id="fcp-summary" hidden aria-live="polite">
                <h3><?php esc_html_e('Récapitulatif', 'fcp-horizon'); ?></h3>
                <dl id="fcp-summary-list"></dl>
                <div class="fcp-form__actions">
                    <button type="button" class="fcp-cta fcp-cta--ghost" id="fcp-edit-btn"><?php esc_html_e('Modifier', 'fcp-horizon'); ?></button>
                    <button type="submit" class="fcp-cta fcp-cta--gold" id="fcp-submit-btn"><?php esc_html_e('Confirmer et envoyer', 'fcp-horizon'); ?></button>
                </div>
            </section>
        </form>

        <!-- Écran de confirmation -->
        <section class="fcp-confirmation" id="fcp-confirmation" hidden aria-live="polite">
            <h2><?php esc_html_e('Votre demande est enregistrée', 'fcp-horizon'); ?></h2>
            <p class="fcp-confirmation__ref"><?php esc_html_e('Référence', 'fcp-horizon'); ?> : <strong id="fcp-ref"></strong></p>
            <p id="fcp-confirmation-msg"></p>
            <p><a class="fcp-cta fcp-cta--gold" id="fcp-whatsapp-btn" href="#" rel="noopener"><?php esc_html_e('Poursuivre sur WhatsApp', 'fcp-horizon'); ?></a></p>
            <p class="fcp-confirmation__note"><?php esc_html_e('Nos équipes restent joignables à chaque étape.', 'fcp-horizon'); ?></p>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
