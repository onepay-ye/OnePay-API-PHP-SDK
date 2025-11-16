# OnePay API — All Parameters (extracted from OpenAPI)

## Common Headers (required for all requests)
- Authorization (header) — required — Bearer <JWT_TOKEN>
- Content-Type (header) — required — application/json
- User-Agent (header) — required — ONEPAY/1.0

---

## 1) GET /{gateway}/accountinfo
Path parameters:
- gateway (string) — required — enum: cashpay, jawali, paypal

Responses examples available in OpenAPI.

---

## 2) POST /createorder
Body parameters (JSON):
- payment_name (string) — required — enum: cashpay, jawali, paypal
- currency_id (string) — required — enum: YER, SAR, USD
- payerPhone (string/number) — required — pattern example: 967770000000
- payerEmail (string) — required — must be a valid email
- beneficiaryList (array) — required — each item:
  - amount (number) — required
  - itemName (string) — required
  - quantity (integer) — required, min 1
  - des (string) — optional
- des (string) — optional root description

Example:
{
  "payment_name": "cashpay",
  "currency_id": "USD",
  "payerPhone": "967770000000",
  "payerEmail": "buyer@example.com",
  "beneficiaryList": [
    {"amount":100,"itemName":"ساعة","quantity":2}
  ],
  "des":"شراء ساعة"
}

---

## 3) POST /checkorder
Body parameters:
- payment_name (string) — required
- payerPhone (string) — required
- payerEmail (string) — required
- requestIdRes (string) — optional but recommended
- orderID (string) — optional but recommended

Example:
{
  "payment_name":"cashpay",
  "payerPhone":"967770000000",
  "payerEmail":"buyer@example.com",
  "requestIdRes":"66ae540d9736d",
  "orderID":"order.pay-379-5825"
}

---

## 4) GET /{gateway}/invoice/list/{payerEmail}
Path parameters:
- gateway (string) — required
- payerEmail (string) — required
