<?php
declare(strict_types=1);

namespace FCP\Horizon\Data\Providers\Brevo;

use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;

/**
 * Client HTTP Brevo (transport bas niveau).
 *
 * La clé API est lue via la configuration serveur, jamais exposée ni journalisée.
 * Ce client est le SEUL endroit qui connaît les structures Brevo.
 */
final class BrevoClient
{
    private const EMAIL_ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private Config $config)
    {
    }

    public function isConfigured(): bool
    {
        return $this->config->get('BREVO_API_KEY') !== '';
    }

    /**
     * Envoie un e-mail transactionnel. Retourne le messageId fournisseur.
     *
     * @throws \RuntimeException en cas d'échec (sans exposer de secret)
     */
    public function sendEmail(
        string $senderEmail,
        string $senderName,
        string $toEmail,
        string $subject,
        string $html,
        string $text
    ): string {
        $response = wp_remote_post(self::EMAIL_ENDPOINT, [
            'timeout' => 15,
            'headers' => [
                'api-key'      => $this->config->get('BREVO_API_KEY'),
                'accept'       => 'application/json',
                'content-type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'sender'      => ['email' => $senderEmail, 'name' => $senderName],
                'to'          => [['email' => $toEmail]],
                'subject'     => $subject,
                'htmlContent' => $html,
                'textContent' => $text,
            ]),
        ]);

        if (is_wp_error($response)) {
            Logger::error('Brevo email transport error', ['error' => $response->get_error_message()]);
            throw new \RuntimeException('transport_error');
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code >= 400) {
            // On journalise le code seul, jamais le corps (peut contenir des détails).
            Logger::error('Brevo email HTTP error', ['code' => $code]);
            throw new \RuntimeException('http_' . $code);
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        return is_array($body) && isset($body['messageId']) ? (string) $body['messageId'] : '';
    }
}
