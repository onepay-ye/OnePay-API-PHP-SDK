<?php
namespace OnePay;

class ApiHandler
{
    private OnePayGuzzle $client;

    public function __construct(OnePayGuzzle $client)
    {
        $this->client = $client;
    }

    private function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function validateCreateOrder(array $body, array &$errors = []): bool
    {
        $errors = [];
        $required = ['payment_name','currency_id','payerPhone','payerEmail','beneficiaryList'];
        foreach ($required as $r) {
            if (!isset($body[$r]) || $body[$r] === '') {
                $errors[] = $r . ' is required';
            }
        }
        $gateways = ['cashpay','jawali','paypal'];
        if (isset($body['payment_name']) && !in_array($body['payment_name'],$gateways)) {
            $errors[] = 'payment_name must be one of: ' . implode(', ',$gateways);
        }
        $curr = ['YER','SAR','USD'];
        if (isset($body['currency_id']) && !in_array($body['currency_id'],$curr)) {
            $errors[] = 'currency_id must be one of: ' . implode(', ',$curr);
        }
        if (isset($body['payerPhone']) && !preg_match('/^[0-9+]{6,15}$/', (string)$body['payerPhone'])) {
            $errors[] = 'payerPhone invalid format';
        }
        if (isset($body['payerEmail']) && !filter_var($body['payerEmail'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'payerEmail invalid';
        }
        if (isset($body['beneficiaryList']) && !is_array($body['beneficiaryList'])) {
            $errors[] = 'beneficiaryList must be an array';
        } else {
            foreach ($body['beneficiaryList'] ?? [] as $i => $b) {
                if (!isset($b['amount']) || !is_numeric($b['amount'])) $errors[] = "beneficiaryList[$i].amount required numeric";
                if (!isset($b['itemName']) || $b['itemName'] === '') $errors[] = "beneficiaryList[$i].itemName required";
                if (!isset($b['quantity']) || !is_numeric($b['quantity']) || (int)$b['quantity'] < 1) $errors[] = "beneficiaryList[$i].quantity required positive integer";
            }
        }
        return empty($errors);
    }

    private function validateCheckOrder(array $body, array &$errors = []): bool
    {
        $errors = [];
        $required = ['payment_name','payerPhone','payerEmail'];
        foreach ($required as $r) {
            if (!isset($body[$r]) || $body[$r] === '') {
                $errors[] = $r . ' is required';
            }
        }
        if (isset($body['payerEmail']) && !filter_var($body['payerEmail'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'payerEmail invalid';
        }
        return empty($errors);
    }

    public function handleAccountInfo(string $gateway): void
    {
        if (empty($gateway)) $this->jsonResponse(['error'=>'gateway required'],400);
        $res = $this->client->accountInfo($gateway);
        $this->jsonResponse($res['body'] ?? ['error'=>'no response'], $res['status'] ?? 500);
    }

    public function handleCreateOrder(): void
    {
        $body = $this->getJsonInput();
        if (!$this->validateCreateOrder($body, $errs)) {
            $this->jsonResponse(['error'=>'validation_failed','details'=>$errs], 422);
        }
        $res = $this->client->createOrder($body);
        $this->jsonResponse($res['body'] ?? ['error'=>'no response'], $res['status'] ?? 500);
    }

    public function handleCheckOrder(): void
    {
        $body = $this->getJsonInput();
        if (!$this->validateCheckOrder($body, $errs)) {
            $this->jsonResponse(['error'=>'validation_failed','details'=>$errs], 422);
        }
        $res = $this->client->checkOrder($body);
        $this->jsonResponse($res['body'] ?? ['error'=>'no response'], $res['status'] ?? 500);
    }

    public function handleInvoiceList(string $gateway, string $payerEmail): void
    {
        if (empty($gateway) || empty($payerEmail)) $this->jsonResponse(['error'=>'gateway and payerEmail required'],400);
        $res = $this->client->invoiceList($gateway, $payerEmail);
        $this->jsonResponse($res['body'] ?? ['error'=>'no response'], $res['status'] ?? 500);
    }
}
