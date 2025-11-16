<?php
namespace OnePay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * OnePayGuzzle - wrapper for One Pay API using Guzzle
 */
class OnePayGuzzle
{
    private $client;
    private $token;

    public function __construct(string $token, bool $sandbox = true, array $options = [])
    {
        $this->token = $token;
        $base = $sandbox ? 'https://one-pay.info/api/v2/sandbox' : 'https://one-pay.info/api/v2';

        $default = [
            'base_uri' => $base,
            'timeout' => 30,
            'http_errors' => false
        ];
        $this->client = new Client(array_replace_recursive($default, $options));
    }

    private function send(string $method, string $uri, ?array $data = null): array
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type'  => 'application/json',
                'User-Agent'    => 'ONEPAY/1.0'
            ]
        ];
        if ($data !== null) {
            $options['json'] = $data;
        }
        try {
            $resp = $this->client->request($method, $uri, $options);
            $status = $resp->getStatusCode();
            $body = (string)$resp->getBody();
            $decoded = json_decode($body, true);
            return ['status'=>$status, 'body'=>$decoded ?? $body, 'headers'=>$resp->getHeaders()];
        } catch (RequestException $e) {
            return ['status' => $e->getCode() ?: 0, 'error' => $e->getMessage(), 'body' => null];
        }
    }

    // GET /{gateway}/accountinfo
    public function accountInfo(string $gateway): array
    {
        $gateway = urlencode($gateway);
        return $this->send('GET', '/' . $gateway . '/accountinfo');
    }

    // POST /createorder
    public function createOrder(array $payload): array
    {
        return $this->send('POST', '/createorder', $payload);
    }

    // POST /checkorder
    public function checkOrder(array $payload): array
    {
        return $this->send('POST', '/checkorder', $payload);
    }

    // GET /{gateway}/invoice/list/{PayerEmail}
    public function invoiceList(string $gateway, string $payerEmail): array
    {
        $gateway = urlencode($gateway);
        $payerEmail = urlencode($payerEmail);
        return $this->send('GET', '/' . $gateway . '/invoice/list/' . $payerEmail);
    }
}
