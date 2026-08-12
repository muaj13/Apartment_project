# Tenant Dashboard — Apartment Management System

A production-ready Tenant Dashboard page built for a Web Programming course project:
**HTML5 + Tailwind CSS** frontend, **Vanilla PHP + PDO/MySQL** backend.

## Files

| File | Purpose |
|---|---|
| `index.php` | **Home page** — role select (Tenant/Landlord), Sign In / Sign Up, Guest access |
| `dashboard.php` | Tenant dashboard — statement, trends table, action center |
| `tools.php` | Guest-accessible Monthly Bill Calculator (no login required) |
| `auth.php` | AJAX endpoint — Sign In / Sign Up for both roles |
| `config.php` | PDO connection settings + `h()` / `money()` helpers |
| `data.php` | Data-access layer: tenant/bill/auth functions, computes totals |
| `report_issue.php` | AJAX endpoint — "Report an Issue" form |
| `move_out_notice.php` | AJAX endpoint — move-out notice form |
| `logout.php` | Destroys the session and redirects to `index.php` |
| `schema.sql` | MySQL schema (`tenants`, `landlords`, `bills`, `issues`, `notices`) + demo seed data |
| `includes/header.php` | Shared `<head>` (fonts, Tailwind config, global styles) + opening `<body>` — included at the top of every page |
| `includes/footer.php` | Shared closing tags — included at the bottom of every page |
| `assets/js/app.js` | Shared `handleForm()` — the generic AJAX-form-submit helper used by every form on every page |

## How pages are structured (the pattern to copy for new pages)

```php
<?php
require_once __DIR__ . '/data.php';
// ...page-specific PHP logic...
$pageTitle = 'My Page · Nestly';
include __DIR__ . '/includes/header.php';
?>
  <!-- this page's unique HTML -->
<script src="assets/js/app.js"></script>
<script> /* page-specific JS, e.g. handleForm({...}) calls */ </script>
<?php include __DIR__ . '/includes/footer.php'; ?>
```

`index.php` and `tools.php` follow this exact pattern — open them side by side
with `dashboard.php` to see how little page-specific code is left once the
shared head/foot/JS are factored out.

## Setup (real database)

1. Import the schema:
   ```bash
   mysql -u root -p < schema.sql
   ```
2. Edit the credentials at the top of `config.php`:
   ```php
   const DB_HOST = '127.0.0.1';
   const DB_NAME = 'apartment_management';
   const DB_USER = 'root';
   const DB_PASS = '';
   ```
3. Serve the folder with PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```
4. Visit `http://localhost:8000/dashboard.php`.

## Demo mode (no database required)

If MySQL isn't reachable, `config.php` catches the `PDOException` and every
function in `data.php` transparently falls back to realistic in-memory demo
data (one current bill + 3 months of history for tenant "Ariana Karim",
apartment B-402). The page, tables, and forms all remain fully functional —
form submissions are validated server-side and return success, they simply
won't persist without a real database connection.

## Notes on the implementation

- **Total calculation** is always computed in PHP (`with_total()` in
  `data.php`) as `rent + electricity + water + gas + service_fee`, never
  trusted from a pre-stored column.
- **Status badges** (Paid/Unpaid) are driven by the `status` enum column in
  `bills` and rendered as green/red pills.
- **Forms** submit via `fetch()`/AJAX to dedicated PHP endpoints, with both
  native HTML5 validation (`required`, `minlength`, `min` date) and
  server-side re-validation (never trust the client) using prepared
  statements to prevent SQL injection.
- **Session**: `current_tenant_id()` reads `$_SESSION['tenant_id']`,
  defaulting to `1` for demo purposes — wire this to your real
  sign-in flow.
- **Demo sign-in**: on `index.php`, use `ariana.karim@example.com` (tenant)
  or `rafiq.hasan@example.com` (landlord) with any password — demo mode
  skips real password verification for the seeded accounts.
- **`landlord_dashboard.php` doesn't exist yet.** Signing in as a landlord
  will redirect there and 404 until that page is built — that's next.
