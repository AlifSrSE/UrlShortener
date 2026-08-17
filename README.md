<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# URL Shortener

A Laravel-based URL shortener service that converts long URLs into short, shareable links with analytics, custom aliases, expiration, password protection, and QR code generation.

---

## Features

- **Shorten Long URLs** — Converts any valid long URL into a short, unique URL.
- **Custom Aliases** — Users can choose their own short code (e.g., `/my-link`).
- **URL Expiration** — Set optional expiration dates for short URLs.
- **Password Protection** — Protect short URLs with a password.
- **Click Analytics** — Tracks IP, user agent, referrer, and timestamp for every click.
- **QR Code Generation** — Generates QR codes for any short URL.
- **Bulk Shortening API** — Shorten up to 10 URLs in a single API request.
- **Rate Limiting** — Prevents abuse with throttling on the web form.
- **Security Headers** — X-Frame-Options, HSTS, CSP, and more.
- **Responsive UI** — Tailwind CSS with AJAX form submission, copy button, and loading states.

---

## Technical Details

### Database Schema

- **`short_urls`**: Stores URL mappings
  - `id` — Auto-incrementing primary key
  - `original_url` — The original long URL (TEXT)
  - `short_code` — Auto-generated 6-character code
  - `alias` — Optional custom alias (unique, nullable)
  - `password` — Optional hashed password (nullable)
  - `expires_at` — Optional expiration timestamp (nullable)
  - `timestamps` — Creation and update times

- **`clicks`**: Stores click analytics
  - `id` — Auto-incrementing primary key
  - `short_url_id` — Foreign key to short_urls
  - `ip_address` — Visitor IP address
  - `user_agent` — Browser user agent
  - `referrer` — HTTP referrer
  - `country` — Country code (nullable)
  - `clicked_at` — Timestamp of the click
  - `timestamps`

### Key Considerations
- **Uniqueness:** Ensures short URLs and aliases are unique.
- **Validation:** Validates input URLs, aliases, and expiration dates.
- **Caching:** Redirect lookups are cached for performance.

---

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/VOID-ALIF/url-shortener.git
cd url-shortener
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Update the database settings in `.env`:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=url_shortener
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Start the Server
```bash
php artisan serve
```

The app will be accessible at **`http://127.0.0.1:8000`**.

---

## Usage

### Web Interface
1. Open the application in your browser at **`http://127.0.0.1:8000`**.
2. Enter a valid long URL in the input field.
3. Optionally set a custom alias, expiration date, or password.
4. Click **Shorten URL**.
5. Copy the short URL or download the QR code.

### API Endpoints

All API endpoints require an `X-API-Key` header.

#### Create Short URL
- Method: **`POST`**
- Endpoint: **`/api/shorten`**
- Headers: `X-API-Key: your-api-key`
- Payload:
  ```json
  {
    "url": "https://example.com/very-long-url",
    "alias": "my-link",
    "expires_at": "2026-09-01T12:00",
    "password": "secret123"
  }
  ```
- Response:
  ```json
  {
    "short_url": "http://127.0.0.1:8000/my-link"
  }
  ```

#### Bulk Shorten URLs
- Method: **`POST`**
- Endpoint: **`/api/shorten/bulk`**
- Headers: `X-API-Key: your-api-key`
- Payload:
  ```json
  {
    "urls": [
      "https://example1.com",
      "https://example2.com"
    ]
  }
  ```
- Response:
  ```json
  {
    "data": [
      {"original_url": "https://example1.com", "short_url": "http://127.0.0.1:8000/abc123"},
      {"original_url": "https://example2.com", "short_url": "http://127.0.0.1:8000/def456"}
    ]
  }
  ```

#### Redirect
- Method: **`GET`**
- Endpoint: **`/{code}`**
- Redirects to the original long URL (410 if expired)

#### QR Code
- Method: **`GET`**
- Endpoint: **`/qr/{code}`**
- Redirects to a generated QR code image

---

## Running Tests

```bash
php artisan test
```

---

## License

This project is licensed under the [MIT license](https://opensource.org/licenses/MIT).
