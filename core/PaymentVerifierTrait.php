<?php
namespace catechesis;

use Exception;
use catechesis\Configurator;

/**
 * Shared functionality for payment verification services.
 */
trait PaymentVerifierTrait
{
    private string $endpoint;
    private string $token;
    private int $timeout;

    public function __construct(?string $endpoint = null, ?string $token = null, ?int $timeout = null)
    {
        $this->endpoint = ($endpoint ?? Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_API_URL)) ?? '';
        $this->token     = ($token ?? Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_API_TOKEN)) ?? '';
        if ($timeout === null)
            $timeout = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_API_TIMEOUT);
        $this->timeout   = $timeout ?? 10;
    }

    /**
     * Queries the Pix provider and checks if payment was received.
     * Either $enrollmentId or $pixKey must be provided.
     *
     * @param int|null    $enrollmentId  Enrollment identifier
     * @param string|null $pixKey        Pix key
     * @param float       $amount        Expected amount
     *
     * @return bool True if the provider reports payment with at least the given amount.
     * @throws Exception When the provider is unreachable or returns an invalid response.
     */
    public function verifyPayment(?int $enrollmentId, ?string $pixKey, float $amount): bool
    {
        if (!$this->endpoint || !$this->token) {
            throw new Exception('Pix provider not configured.');
        }
        if ($enrollmentId === null && $pixKey === null) {
            throw new Exception('Enrollment ID or Pix key must be provided.');
        }

        $params = [];
        if ($enrollmentId !== null) {
            $params['enrollment_id'] = $enrollmentId;
        }
        if ($pixKey !== null) {
            $params['pix_key'] = $pixKey;
        }

        $query = http_build_query($params);
        $url   = rtrim($this->endpoint, '/') . '/?' . $query;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$this->token}"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

        if (stripos($url, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log('Pix provider connection error: ' . $error);
            throw new Exception('Failed to connect to Pix provider.');
        }
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            error_log("Pix provider HTTP error ({$status}): {$response}");
            throw new Exception('Pix provider returned an error.');
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['paid']) || !isset($data['amount'])) {
            error_log('Pix provider invalid response: ' . $response);
            throw new Exception('Invalid response from Pix provider.');
        }

        return ($data['paid'] === true || $data['paid'] === 1 || $data['paid'] === '1')
            && floatval($data['amount']) >= $amount;
    }
}
