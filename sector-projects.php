<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require __DIR__ . '/admin/config.php';
    ensure_sector_projects_table();

    $slug = strtolower(trim((string) ($_GET['id'] ?? '')));
    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
        echo json_encode(['projects' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $stmt = db()->prepare("
        SELECT title_fr, status_fr, description_fr, image_path
        FROM sector_projects
        WHERE sector_slug = ? AND is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([$slug]);

    $projects = array_map(static function (array $row): array {
        return [
            'title' => $row['title_fr'],
            'status' => $row['status_fr'] ?: '',
            'text' => $row['description_fr'] ?: '',
            'image' => $row['image_path'] ?: null,
        ];
    }, $stmt->fetchAll());

    echo json_encode(['projects' => $projects], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['projects' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
