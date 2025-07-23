<?php
namespace catechesis;

use Exception;
use catechesis\Configurator;

/**
 * Service to check whether a multibanco reference was paid using an external
 * payment provider API. This implementation expects the API to return a JSON
 * payload with fields 'paid' and 'amount'.
 */
class PaymentVerificationService
{
    private string $endpoint;
    private string $token;
    private int $timeout;

    public function __construct(?string $endpoint = null, ?string $token = null, ?int $timeout = null)
    {
        $this->endpoint = $endpoint ?? Configurator::getConfigurationValueOrDefault(Configurator::KEY_PAYMENT_PROVIDER_URL);
        $this->token     = $token ?? Configurator::getConfigurationValueOrDefault(Configurator::KEY_PAYMENT_PROVIDER_TOKEN);
        if ($timeout === null)
            $timeout = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PAYMENT_PROVIDER_TIMEOUT);
        $this->timeout   = $timeout ?? 10;
    }

    /**
     * Queries the provider and checks if the reference has been paid.
     *
     * @param int    $entity   Payment entity number
     * @param string $reference Payment reference number
     * @param float  $amount    Expected amount
     *
     * @return bool True if the provider reports the reference was paid with at least the given amount.
     * @throws Exception When the provider is unreachable or returns an invalid response.
     */
    public function verifyPayment(int $entity, string $reference, float $amount): bool
    {
        if (!$this->endpoint || !$this->token) {
            throw new Exception('Payment provider not configured.');
        }

        $query = http_build_query(['entity' => $entity, 'reference' => $reference]);
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
            error_log('Payment provider connection error: ' . $error);
            throw new Exception('Failed to connect to payment provider.');
        }
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            error_log("Payment provider HTTP error ({$status}): {$response}");
            throw new Exception('Payment provider returned an error.');
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['paid']) || !isset($data['amount'])) {
            error_log('Payment provider invalid response: ' . $response);
            throw new Exception('Invalid response from payment provider.');
        }

        return ($data['paid'] === true || $data['paid'] === 1 || $data['paid'] === '1')
            && floatval($data['amount']) >= $amount;
    }
}
