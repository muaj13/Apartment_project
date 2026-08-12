<?php
/**
 * auth.php
 * ------------------------------------------------------------
 * Single AJAX endpoint for the Home page's Sign In / Sign Up
 * forms. Handles both roles ("tenant" and "landlord") via a
 * `role` field, so the frontend can reuse one JS handler.
 *
 * POST fields:
 *   action    = 'signin' | 'signup'
 *   role      = 'tenant' | 'landlord'
 *   email
 *   password
 *   full_name         (signup only)
 *   confirm_password  (signup only)
 * ------------------------------------------------------------
 */
declare(strict_types=1);
require_once __DIR__ . '/data.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$action = (string)($_POST['action'] ?? '');
$role   = (string)($_POST['role'] ?? '');
$email  = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$redirects = ['tenant' => 'dashboard.php', 'landlord' => 'landlord_dashboard.php'];

if (!in_array($role, ['tenant', 'landlord'], true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid account type.']);
    exit;
}

// ---------------------------------------------------------------
// SIGN IN
// ---------------------------------------------------------------
if ($action === 'signin') {
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if ($password === '') $errors['password'] = 'Password is required.';

    if ($errors) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    $user = find_user_by_email($role, $email);
    $pdo  = get_pdo();

    // Real DB: verify the hash. Demo mode: any password unlocks the seeded demo account.
    $passwordOk = $user && ($pdo ? password_verify($password, (string)$user['password_hash']) : true);

    if (!$user || !$passwordOk) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Incorrect email or password.']);
        exit;
    }

    ['idCol' => $idCol] = role_table($role);
    $_SESSION['role'] = $role;
    $_SESSION[$role . '_id'] = (int)$user[$idCol];

    echo json_encode([
        'ok'       => true,
        'message'  => 'Welcome back, ' . $user['full_name'] . '.',
        'redirect' => $redirects[$role],
    ]);
    exit;
}

// ---------------------------------------------------------------
// SIGN UP
// ---------------------------------------------------------------
if ($action === 'signup') {
    $fullName        = trim((string)($_POST['full_name'] ?? ''));
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $errors = [];

    if (mb_strlen($fullName) < 2) $errors['full_name'] = 'Enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (mb_strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirmPassword) $errors['confirm_password'] = 'Passwords do not match.';

    if (!$errors && find_user_by_email($role, $email)) {
        $errors['email'] = 'An account with this email already exists.';
    }

    if ($errors) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    $hash  = password_hash($password, PASSWORD_DEFAULT);
    $newId = create_user($role, $fullName, $email, $hash);

    if ($newId === null) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Could not create your account. Please try again.']);
        exit;
    }

    $_SESSION['role'] = $role;
    $_SESSION[$role . '_id'] = $newId;

    echo json_encode([
        'ok'       => true,
        'message'  => 'Account created — welcome to Nestly.',
        'redirect' => $redirects[$role],
    ]);
    exit;
}

http_response_code(422);
echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
