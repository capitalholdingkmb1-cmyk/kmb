<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

function clean_text($value, $limit) {
    $value = trim(str_replace("\0", '', (string) $value));
    return function_exists('mb_substr') ? mb_substr($value, 0, $limit, 'UTF-8') : substr($value, 0, $limit);
}

function clean_header($value, $limit) {
    return str_replace(["\r", "\n"], ' ', clean_text($value, $limit));
}

$name = clean_header($input['name'] ?? '', 120);
$email = filter_var(clean_header($input['email'] ?? '', 254), FILTER_VALIDATE_EMAIL);
$phone = clean_header($input['phone'] ?? '', 60);
$subject = clean_header($input['subject'] ?? '', 180);
$message = clean_text($input['message'] ?? '', 4000);

if (!$name || !$email || !$phone || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

$body = implode("\n", [
    'Nouveau message depuis le site Capital Holding KMB',
    '',
    'Nom: ' . $name,
    'Email: ' . $email,
    'Telephone: ' . $phone,
    '',
    'Message:',
    $message
]);

$stored = false;
if (file_exists(__DIR__ . '/admin/config.php')) {
    require_once __DIR__ . '/admin/config.php';
    try {
        ensure_contact_storage_schema();

        $stmt = db()->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $phone, $subject, $message, 'new']);

        $notes = implode("\n\n", [
            'Dernier sujet: ' . $subject,
            'Dernier message:',
            $message
        ]);

        $existing = db()->prepare('SELECT id FROM contacts WHERE email = ? LIMIT 1');
        $existing->execute([$email]);
        $contact = $existing->fetch();

        if ($contact) {
            $update = db()->prepare('UPDATE contacts SET name = ?, phone = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $update->execute([$name, $phone, $notes, (int) $contact['id']]);
        } else {
            $insert = db()->prepare('INSERT INTO contacts (name, email, phone, company, sector, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([$name, $email, $phone, '', '', $notes]);
        }

        $stored = true;
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database save failed']);
        exit;
    }
}

if (!$stored) {
    http_response_code(500);
    echo json_encode(['error' => 'Database unavailable']);
    exit;
}

echo json_encode(['ok' => true]);
