<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require __DIR__ . '/admin/config.php';
    ensure_sector_media_table();

    $slug = strtolower(trim((string) ($_GET['id'] ?? '')));
    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
        echo json_encode(['media' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $stmt = db()->prepare("
        SELECT media_type, title_fr, media_path, poster_path
        FROM sector_media
        WHERE sector_slug = ? AND is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([$slug]);

    $media = array_map(static function (array $row): array {
        return [
            'type' => $row['media_type'] === 'video' ? 'video' : 'image',
            'title' => $row['title_fr'],
            'src' => $row['media_path'],
            'poster' => $row['poster_path'] ?: null,
        ];
    }, $stmt->fetchAll());

    echo json_encode(['media' => $media], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['media' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
