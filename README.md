# 🎟️ Coupon Redemption API & CouponLab Testing Suite

A robust, production-grade coupon redemption service built on **Laravel 12**, featuring atomic concurrency safety, granular business rule validation, and **CouponLab**—an interactive manual testing workbench.

---

## 📖 Overview

The **Coupon Redemption API** provides an endpoint for validating and applying discount promotional codes against shopping cart totals. It enforces comprehensive business constraints such as date ranges, minimum order thresholds, discount caps, global usage limits, and per-user redemption limits.

To eliminate the need for manual `curl` or Postman requests, the project includes **CouponLab**: a browser-based manual testing UI served directly from the application.

---

## 🛠️ Technology Stack

- **Backend Framework**: PHP 8.2+, Laravel 12.x
- **Database**: MySQL 8.0+ (Local / Production), SQLite (In-Memory for automated tests)
- **Frontend / UI**: Laravel Blade, Vanilla JavaScript (ES6+), Modern CSS & Tailwind CSS v4
- **Asset Bundler**: Vite 7 (`laravel-vite-plugin`, `@tailwindcss/vite`)
- **Testing**: PHPUnit 11

---

## 🔄 Core Workflow

```
[ Client / Storefront ]
          │
          │  POST /api/coupons/apply
          │  { code, cart_total, user_id?, order_reference? }
          ▼
[ ApplyCouponRequest ] ──▶ Validates payload syntax (code required, cart_total > 0)
          │
          ▼
[ CouponController@apply ]
          │
          ▼
[ CouponService@apply ]
          │
          ├─▶ 1. Begin DB::transaction()
          ├─▶ 2. Lock coupon row for update (lockForUpdate)
          ├─▶ 3. Case-insensitive lookup (UPPER(code))
          ├─▶ 4. Validate active status & date windows (starts_at, expires_at)
          ├─▶ 5. Validate minimum order threshold (min_order_value)
          ├─▶ 6. Check global usage limit (usage_limit)
          ├─▶ 7. Check per-user usage limit (usage_limit_per_user)
          ├─▶ 8. Calculate discount (fixed or percentage with max_discount_amount cap)
          ├─▶ 9. Insert redemption record into coupon_redemptions
          └─▶ 10. Commit transaction & return response
                    ├─▶ HTTP 200: { valid: true, coupon_code, discount_amount, final_total }
                    └─▶ HTTP 422: { valid: false, reason, discount_amount: 0, final_total }
```

---

## 🚀 Setup & Run Instructions

### Prerequisites
- **PHP** >= 8.2 with PDO, cURL, MBString, and OpenSSL extensions
- **Composer** (v2.x)
- **Node.js** (v18+) & **npm**
- **MySQL** running locally (e.g., via XAMPP or native service)

### 1. Clone & Install Dependencies
```bash
git clone <repository-url>
cd coupon-redemption-api

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

### 2. Configure Environment
Copy the example environment file and generate the application key:
```bash
cp .env.example .env
php artisan key:generate
```

Ensure your `.env` contains your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Migrations & Seed Database
Run the database migrations and seed the preconfigured test coupons:
```bash
php artisan migrate --seed
```

### 4. Build Frontend Assets
Compile the CSS and JavaScript bundles using Vite:
```bash
npm run build
```

*(Optional: For live hot-module reloading during frontend development, run `npm run dev`.)*

### 5. Start the Application Server
```bash
php artisan serve
```

- Open **[http://127.0.0.1:8000](http://127.0.0.1:8000)** in your browser to access the **CouponLab** testing suite.
- The API endpoint is available at **`POST http://127.0.0.1:8000/api/coupons/apply`**.

---

## 🧪 How to Run the Tests

The project includes an automated test suite verifying every business rule and API edge case. Tests execute against an isolated, in-memory SQLite database (`:memory:`), ensuring zero impact on your local database.

### Run All Tests
```bash
php artisan test
```

### Run Coupon API Tests Specifically
```bash
php artisan test --filter=CouponApplyApiTest
```

### Test Coverage Summary:
- ✅ Successful fixed discount calculation (`SAVE10`)
- ✅ Successful percentage discount calculation (`TENOFF`)
- ✅ Percentage discount capped at `max_discount_amount` (`HALFOFF`)
- ✅ Minimum order threshold enforcement and rejection (`MIN500`)
- ✅ Expired coupon rejection (`EXPIRED10`)
- ✅ Future/not-yet-active coupon rejection (`COMINGSOON`)
- ✅ Inactive/disabled coupon rejection (`DISABLED10`)
- ✅ Exhausted global usage limit rejection (`ONLYONE`)
- ✅ Per-user usage limit rejection for previous user (`ONEPERUSER` with `user_id = 1`)
- ✅ Per-user usage limit allowed for new user (`ONEPERUSER` with `user_id = 2`)
- ✅ Payload validation for negative/zero amounts and missing parameters (422)
- ✅ Helper endpoints (Coupons Catalog, Redemptions Log, 1-Click Database Reset)

---

## 🖥️ Manual Testing with CouponLab

When visiting `http://localhost:8000`, the **CouponLab** dashboard provides:

1. **1-Click Test Scenarios Carousel**:
   - Click any pre-configured scenario (`SAVE10`, `TENOFF`, `HALFOFF`, `MIN500 (Fail)`, `MIN500 (Pass)`, `EXPIRED10`, `COMINGSOON`, `DISABLED10`, `ONLYONE`, `ONEPERUSER`) to immediately populate the form and inspect the server response.
2. **Shopping Cart Simulator**:
   - Adjust product quantities (+ / -) or enter custom cart totals to simulate real store purchases.
   - Switch customer context between **Guest** (`null`), **User #1**, **User #2**, or custom IDs.
   - Auto-generate or customize order references (`ORD-XXXXX`).
3. **Real-Time API & Network Inspector**:
   - View HTTP status codes (`200 OK` vs `422 Unprocessable Content`).
   - Measure real request latency in milliseconds.
   - Inspect formatted visual breakdowns as well as raw JSON request and response payloads with a 1-click copy button.
4. **Database State & Redemption Audit Trail**:
   - Live view of all seeded coupons with real-time remaining use counters and status badges.
   - Live audit trail of records inserted into the `coupon_redemptions` table.
   - **Reset Test Data** button to clear redemptions and re-seed fresh test data at any time.

---

## 📋 API Specification

### Endpoint
`POST /api/coupons/apply`

### Headers
```http
Content-Type: application/json
Accept: application/json
```

### Request Body
| Field | Type | Required | Description |
|---|---|---|---|
| `code` | string | **Yes** | The coupon code (case-insensitive, max 50 chars). |
| `cart_total` | numeric | **Yes** | The order subtotal before discount (minimum 0.01). |
| `user_id` | integer | No | ID of the authenticated user (nullable for guest checkouts). |
| `order_reference` | string | No | Optional external order ID / checkout reference. |

#### Example Request
```json
{
  "code": "SAVE10",
  "cart_total": 120.00,
  "user_id": 1,
  "order_reference": "ORD-48291"
}
```

### Response Schema

#### Success (HTTP 200 OK)
```json
{
  "valid": true,
  "reason": null,
  "coupon_code": "SAVE10",
  "discount_amount": 10.00,
  "final_total": 110.00
}
```

#### Business Rule Rejection (HTTP 422 Unprocessable Content)
```json
{
  "valid": false,
  "reason": "Minimum order value for this coupon is 500.00.",
  "coupon_code": "MIN500",
  "discount_amount": 0.00,
  "final_total": 150.00
}
```

#### Validation Failure (HTTP 422 Unprocessable Content)
```json
{
  "message": "The code field is required. (and 1 more error)",
  "errors": {
    "code": ["The code field is required."],
    "cart_total": ["The cart total field is required."]
  }
}
```

---

## 📌 Assumptions Made

1. **Apply Equals Redemption**: In a standalone coupon API without an external payment gateway, a successful `apply` request represents the final redemption and records an entry into `coupon_redemptions`.
2. **Case-Insensitive Codes**: Coupon codes are normalized to uppercase (`UPPER(TRIM(code))`) during evaluation so `save10` and `SAVE10` are treated identically.
3. **Guest Checkout Support**: `user_id` is nullable. Coupons with no per-user restrictions can be redeemed by guests; coupons requiring user limits require a non-null `user_id`.
4. **Discount Floor**: Fixed discounts that exceed the cart total will reduce the final total to `$0.00` rather than allowing a negative balance.
5. **Single Coupon Per Order**: The current service applies a single coupon code per transaction.

---

## 💡 Key Technical Decisions

1. **Pessimistic Locking for Concurrency Safety**:
   - Coupon usage limits are prone to race conditions under concurrent traffic (two shoppers attempting to redeem the last available coupon simultaneously).
   - We utilize `DB::transaction()` combined with `lockForUpdate()` on the coupon record to serialize requests and eliminate over-redemption anomalies.
2. **Explicit HTTP Status Codes**:
   - Failed business rules (such as expired codes or cart totals below the minimum) return `HTTP 422 Unprocessable Content` with a clear `reason` and `valid: false` instead of generic `200` responses with error flags.
3. **Database Indexing**:
   - Added a composite index on `['coupon_id', 'user_id']` in `coupon_redemptions` to ensure per-user and global usage lookups remain performant even with high redemption volumes.
4. **Zero-Dependency Test Isolation**:
   - Configured tests to run on in-memory SQLite (`:memory:`) with `RefreshDatabase`, ensuring unit and feature tests execute in under 1 second without touching the development database.
5. **Independent UI Architecture**:
   - The manual testing interface interacts with the backend strictly via the public JSON API (`POST /api/coupons/apply`) and read-only helper endpoints (`/test-api/*`), verifying the exact request-response contracts used by production clients.

---

## 🔮 Limitations & Future Improvements

If granted additional time, the following enhancements would be prioritized:

1. **Two-Phase Redemption Protocol (Quote vs Commit)**:
   - Split the workflow into:
     - `POST /api/coupons/preview` (read-only quote calculation for cart display)
     - `POST /api/coupons/redeem` (stateful redemption executed when payment is confirmed)
   - Implement temporary reservations (e.g., Redis reservation lock with a 15-minute TTL) while a user is in the checkout funnel.
2. **Category & Item-Level Eligibility Rules**:
   - Extend the schema to allow coupons applicable only to specific product categories or SKUs (e.g., "15% off Audio products only"), with item-level discount allocation.
3. **Stackable Coupons & Priority Rules**:
   - Support applying multiple promotions (e.g., a site-wide sale + personal voucher) with defined precedence rules (percentage calculated before fixed amounts).
4. **Rate Limiting & Abuse Protection**:
   - Introduce rate-limiting middleware (e.g., Laravel's `throttle:60,1`) and IP-based backoff algorithms to prevent brute-force promo code enumeration.
5. **Merchant Administration Dashboard**:
   - Build an administrative CRUD interface for creating, scheduling, pausing, and monitoring coupon analytics and redemption trends.
