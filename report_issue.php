<?php
/**
 * report_issue.php — handles "Report an Issue" form submissions via fetch().
 * Validates input server-side (never trust the client) and returns JSON.
 */
declare(strict_types=1);
require_once __DIR__ . '/data.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$allowedCategories = ['Plumbing', 'Electrical', 'Structural', 'Other'];
$category    = trim((string)($_POST['category'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));

$errors = [];

if (!in_array($category, $allowedCategories, true)) {
    $errors['category'] = 'Please choose a valid issue category.';
}

if ($description === '') {
    $errors['description'] = 'Please describe the issue.';
} elseif (mb_strlen($description) < 10) {
    $errors['description'] = 'Please add a few more details (at least 10 characters).';
} elseif (mb_strlen($description) > 1000) {
    $errors['description'] = 'Description is too long (max 1000 characters).';
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$tenantId = current_tenant_id();
$success  = insert_issue($tenantId, $category, $description);

if (!$success) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save your report. Please try again.']);
    exit;
}

echo json_encode([
    'ok'      => true,
    'message' => 'Your issue has been reported to the landlord.',
]);
