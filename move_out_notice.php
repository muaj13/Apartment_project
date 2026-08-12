<?php
/**
 * move_out_notice.php — handles the "Notice for Leaving the Apartment"
 * form via fetch(). Validates the departure date server-side
 * (must be a real date, at least 30 days out — standard notice period).
 */
declare(strict_types=1);
require_once __DIR__ . '/data.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$dateInput = trim((string)($_POST['move_out_date'] ?? ''));
$reason    = trim((string)($_POST['reason'] ?? ''));

$errors = [];

$moveOutDate = DateTime::createFromFormat('Y-m-d', $dateInput);
$dateErrors  = DateTime::getLastErrors();

if (!$moveOutDate || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
    $errors['move_out_date'] = 'Please choose a valid date.';
} else {
    $today       = new DateTime('today');
    $minDate     = (clone $today)->modify('+30 days');
    if ($moveOutDate < $minDate) {
        $errors['move_out_date'] = 'Notices require at least 30 days before move-out.';
    }
}

if (mb_strlen($reason) > 500) {
    $errors['reason'] = 'Reason is too long (max 500 characters).';
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$tenantId = current_tenant_id();
$success  = insert_notice($tenantId, $moveOutDate->format('Y-m-d'), $reason);

if (!$success) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not submit your notice. Please try again.']);
    exit;
}

echo json_encode([
    'ok'      => true,
    'message' => 'Your move-out notice has been sent to the landlord for ' . $moveOutDate->format('F j, Y') . '.',
]);
