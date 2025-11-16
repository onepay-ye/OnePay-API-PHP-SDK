# API Reference — OnePay Proxy

## GET /{gateway}/accountinfo
Description: Retrieve merchant account info.
Headers: Authorization, Content-Type, User-Agent
Path: gateway (cashpay|jawali|paypal)

## POST /createorder
Description: Create a new order request to OnePay.
Body: see docs/ALL_PARAMETERS.md

## POST /checkorder
Description: Check order status.
Body: see docs/ALL_PARAMETERS.md

## GET /{gateway}/invoice/list/{payerEmail}
Description: Get invoices for payer email.
