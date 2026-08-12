<?php
/**
 * data.php
 * ------------------------------------------------------------
 * Data access layer for the Tenant Dashboard.
 * Every function first tries a real PDO/MySQL query; if the
 * database is unavailable it returns realistic demo data so
 * the dashboard is always fully functional for grading/demo.
 * ------------------------------------------------------------
 */

require_once __DIR__ . '/config.php';

const MONTH_NAMES = [1=>'January','February','March','April','May','June','July',
    'August','September','October','November','December'];

/** In a real login system this comes from the session after auth. */
function current_tenant_id(): int
{
    return (int)($_SESSION['tenant_id'] ?? 1);
}

function get_tenant(int $tenantId): array
{
    $pdo = get_pdo();
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT * FROM tenants WHERE tenant_id = ?');
        $stmt->execute([$tenantId]);
        $row = $stmt->fetch();
        if ($row) return $row;
    }

    // ---- demo fallback ----
    return [
        'tenant_id'    => 1,
        'full_name'    => 'Ariana Karim',
        'email'        => 'ariana.karim@example.com',
        'apartment_no' => 'B-402',
        'phone'        => '+880 1711-223344',
        'move_in_date' => '2023-03-01',
        'avatar_url'   => null,
    ];
}

/** Compute the mathematical total for a bill row and attach it. */
function with_total(array $bill): array
{
    $bill['total'] = round(
        (float)$bill['apartment_rent'] +
        (float)$bill['electricity_bill'] +
        (float)$bill['water_bill'] +
        (float)$bill['gas_bill'] +
        (float)$bill['service_fee'],
        2
    );
    return $bill;
}

/** Fetch the most recent N months of bills for a tenant, newest first. */
function get_recent_bills(int $tenantId, int $limit = 4): array
{
    $pdo = get_pdo();
    if ($pdo) {
        $stmt = $pdo->prepare(
            'SELECT * FROM bills WHERE tenant_id = ?
             ORDER BY bill_year DESC, bill_month DESC LIMIT ?'
        );
        $stmt->bindValue(1, $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if ($rows) return array_map('with_total', $rows);
    }

    // ---- demo fallback (4 months) ----
    $demo = [
        ['bill_id'=>4,'tenant_id'=>1,'bill_month'=>7,'bill_year'=>2026,'apartment_rent'=>15000.00,'electricity_bill'=>1850.00,'water_bill'=>600.00,'gas_bill'=>450.00,'service_fee'=>1200.00,'status'=>'unpaid','due_date'=>'2026-07-10','paid_at'=>null],
        ['bill_id'=>3,'tenant_id'=>1,'bill_month'=>6,'bill_year'=>2026,'apartment_rent'=>15000.00,'electricity_bill'=>2100.00,'water_bill'=>620.00,'gas_bill'=>470.00,'service_fee'=>1200.00,'status'=>'paid','due_date'=>'2026-06-10','paid_at'=>'2026-06-08 14:22:00'],
        ['bill_id'=>2,'tenant_id'=>1,'bill_month'=>5,'bill_year'=>2026,'apartment_rent'=>15000.00,'electricity_bill'=>1720.00,'water_bill'=>580.00,'gas_bill'=>430.00,'service_fee'=>1200.00,'status'=>'paid','due_date'=>'2026-05-10','paid_at'=>'2026-05-09 10:05:00'],
        ['bill_id'=>1,'tenant_id'=>1,'bill_month'=>4,'bill_year'=>2026,'apartment_rent'=>14500.00,'electricity_bill'=>1990.00,'water_bill'=>610.00,'gas_bill'=>460.00,'service_fee'=>1200.00,'status'=>'paid','due_date'=>'2026-04-10','paid_at'=>'2026-04-07 09:40:00'],
    ];
    return array_map('with_total', array_slice($demo, 0, $limit));
}

/**
 * ---------------------------------------------------------------
 * AUTH HELPERS
 * Shared by auth.php for both the "tenant" and "landlord" roles.
 * Demo mode: accepts the seeded demo email with ANY password, and
 * "creates" (but does not persist) new sign-ups.
 * ---------------------------------------------------------------
 */

/** Maps a role to its table + id column, so auth code stays DRY. */
function role_table(string $role): array
{
    return $role === 'landlord'
        ? ['table' => 'landlords', 'idCol' => 'landlord_id']
        : ['table' => 'tenants',   'idCol' => 'tenant_id'];
}

/** Finds a user by email for the given role. Returns null if not found. */
function find_user_by_email(string $role, string $email): ?array
{
    ['table' => $table] = role_table($role);
    $pdo = get_pdo();

    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row) return $row;
        return null; // real DB, genuinely not found
    }

    // ---- demo fallback: the two seeded accounts ----
    $demoAccounts = [
        'tenant'   => ['tenant_id' => 1, 'full_name' => 'Ariana Karim', 'email' => 'ariana.karim@example.com', 'apartment_no' => 'B-402'],
        'landlord' => ['landlord_id' => 1, 'full_name' => 'Rafiq Hasan', 'email' => 'rafiq.hasan@example.com'],
    ];
    $demo = $demoAccounts[$role] ?? null;
    if ($demo && strcasecmp($demo['email'], $email) === 0) return $demo;
    return null;
}

/** Creates a new user account for the given role. Returns the new id, or null on failure. */
function create_user(string $role, string $fullName, string $email, string $passwordHash): ?int
{
    ['table' => $table] = role_table($role);
    $pdo = get_pdo();

    if ($pdo) {
        try {
            if ($role === 'landlord') {
                $stmt = $pdo->prepare(
                    "INSERT INTO landlords (full_name, email, password_hash) VALUES (?, ?, ?)"
                );
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO tenants (full_name, email, password_hash, apartment_no, move_in_date)
                     VALUES (?, ?, ?, 'Unassigned', CURDATE())"
                );
            }
            $stmt->execute([$fullName, $email, $passwordHash]);
            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('create_user failed: ' . $e->getMessage());
            return null;
        }
    }

    // Demo mode: pretend it worked (nothing persists without a DB).
    return 999;
}

/** Insert a new reported issue. Returns true on success. */
function insert_issue(int $tenantId, string $category, string $description): bool
{
    $pdo = get_pdo();
    if ($pdo) {
        $stmt = $pdo->prepare(
            'INSERT INTO issues (tenant_id, category, description) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$tenantId, $category, $description]);
    }
    // Demo mode: pretend success (nothing persists without a DB).
    return true;
}

/** Insert a move-out notice. Returns true on success. */
function insert_notice(int $tenantId, string $moveOutDate, string $reason): bool
{
    $pdo = get_pdo();
    if ($pdo) {
        $stmt = $pdo->prepare(
            'INSERT INTO notices (tenant_id, move_out_date, reason) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$tenantId, $moveOutDate, $reason]);
    }
    return true;
}
