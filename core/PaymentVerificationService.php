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

    public function __construct(string $endpoint = null, string $token = null, int $timeout = 10)
    {
        $this->endpoint = $endpoint ?? Configurator::getConfigurationValueOrDefault(Configurator::KEY_PAYMENT_PROVIDER_URL);
        $this->token     = $token ?? Configurator::getConfigurationValueOrDefault(Configurator::KEY_PAYMENT_PROVIDER_TOKEN);
        $this->timeout   = $timeout;
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

        $options = [
            'http' => [
                'header' => "Authorization: Bearer {$this->token}\r\n",
                'method' => 'GET',
                'timeout' => $this->timeout,
            ],
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            throw new Exception('Failed to connect to payment provider.');
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['paid']) || !isset($data['amount'])) {
            throw new Exception('Invalid response from payment provider.');
        }

        return ($data['paid'] === true || $data['paid'] === 1 || $data['paid'] === '1')
            && floatval($data['amount']) >= $amount;
    }
}
