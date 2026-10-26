<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/config.php';

function blog_safe(callable $callback, $fallback) {
    try {
        ensure_blog_tables();
        return $callback();
    } catch (Throwable $error) {
        return $fallback;
    }
}

function blog_public_where(string $alias = ''): string {
    $prefix = $alias !== '' ? $alias . '.' : '';
    return "{$prefix}is_active = 1 AND ({$prefix}published_at IS NULL OR {$prefix}published_at <= NOW())";
}

function blog_latest_posts(int $limit = 9, int $excludeId = 0): array {
    $limit = max(1, min(60, $limit));

    return blog_safe(static function () use ($limit, $excludeId): array {
        $where = blog_public_where();
        $params = [];
        if ($excludeId > 0) {
            $where .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        $stmt = blog_db()->prepare("
            SELECT *
            FROM blog_posts
            WHERE {$where}
            ORDER BY is_featured DESC, COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC
            LIMIT {$limit}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }, []);
}

function blog_find_post(string $slug): ?array {
    $slug = trim($slug);
    if ($slug === '') return null;

    return blog_safe(static function () use ($slug): ?array {
        $stmt = blog_db()->prepare("
            SELECT *
            FROM blog_posts
            WHERE slug = ? AND " . blog_public_where() . "
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
        return $post ?: null;
    }, null);
}

function blog_post_blocks(int $postId): array {
    if ($postId <= 0) return [];

    return blog_safe(static function () use ($postId): array {
        $stmt = blog_db()->prepare("
            SELECT *
            FROM blog_blocks
            WHERE post_id = ? AND is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }, []);
}

function blog_related_posts(array $post, int $limit = 4): array {
    $postId = (int) ($post['id'] ?? 0);
    $date = (string) (($post['published_at'] ?? '') ?: ($post['created_at'] ?? ''));
    if ($postId <= 0 || $date === '') return blog_latest_posts($limit, $postId);

    return blog_safe(static function () use ($postId, $date, $limit): array {
        $items = [];
        $seen = [$postId => true];
        $dateExpression = 'COALESCE(published_at, created_at)';

        $queries = [
            [">", "ASC", 2],
            ["<", "DESC", 2],
        ];

        foreach ($queries as [$operator, $order, $queryLimit]) {
            $stmt = blog_db()->prepare("
                SELECT *
                FROM blog_posts
                WHERE " . blog_public_where() . "
                  AND id <> ?
                  AND {$dateExpression} {$operator} ?
                ORDER BY {$dateExpression} {$order}, id {$order}
                LIMIT {$queryLimit}
            ");
            $stmt->execute([$postId, $date]);
            foreach ($stmt->fetchAll() as $item) {
                $id = (int) ($item['id'] ?? 0);
                if ($id > 0 && empty($seen[$id])) {
                    $items[] = $item;
                    $seen[$id] = true;
                }
            }
        }

        if (count($items) < $limit) {
            $stmt = blog_db()->prepare("
                SELECT *
                FROM blog_posts
                WHERE " . blog_public_where() . "
                  AND id <> ?
                ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                LIMIT 12
            ");
            $stmt->execute([$postId]);
            foreach ($stmt->fetchAll() as $item) {
                $id = (int) ($item['id'] ?? 0);
                if ($id > 0 && empty($seen[$id])) {
                    $items[] = $item;
                    $seen[$id] = true;
                }
                if (count($items) >= $limit) break;
            }
        }

        usort($items, static function (array $a, array $b): int {
            $dateA = (string) (($a['published_at'] ?? '') ?: ($a['created_at'] ?? ''));
            $dateB = (string) (($b['published_at'] ?? '') ?: ($b['created_at'] ?? ''));
            return strcmp($dateB, $dateA);
        });

        return array_slice($items, 0, $limit);
    }, blog_latest_posts($limit, $postId));
}

function blog_featured_posts(int $limit = 4): array {
    $limit = max(1, min(12, $limit));

    return blog_safe(static function () use ($limit): array {
        $stmt = blog_db()->prepare("
            SELECT *
            FROM blog_posts
            WHERE " . blog_public_where() . " AND is_featured = 1
            ORDER BY COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }, []);
}

function blog_asset(?string $path, string $fallback = 'assets/about-kmb-office.png'): string {
    $path = trim((string) $path);
    return $path !== '' ? $path : $fallback;
}

function blog_url(array $post): string {
    return 'blog-detail.php?slug=' . rawurlencode((string) ($post['slug'] ?? ''));
}

function blog_truncate(string $text, int $limit = 150): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?: '');
    if ($text === '' || strlen($text) <= $limit) return $text;

    if (function_exists('mb_substr')) {
        return rtrim((string) mb_substr($text, 0, $limit - 1)) . '...';
    }

    return rtrim(substr($text, 0, $limit - 1)) . '...';
}

function blog_excerpt(array $post, int $limit = 155): string {
    $excerpt = trim((string) ($post['excerpt'] ?? ''));
    if ($excerpt === '') {
        $excerpt = trim((string) ($post['subtitle'] ?? ''));
    }
    if ($excerpt === '') {
        $excerpt = trim((string) ($post['body_text'] ?? ''));
    }

    return blog_truncate($excerpt, $limit);
}

function blog_date_parts(?string $date): array {
    $date = trim((string) $date);
    if ($date === '') {
        return ['day' => '', 'month' => '', 'year' => '', 'full' => ''];
    }

    try {
        $dt = new DateTimeImmutable($date);
    } catch (Throwable $error) {
        return ['day' => '', 'month' => '', 'year' => '', 'full' => ''];
    }

    $months = [
        1 => 'janvier',
        2 => 'fevrier',
        3 => 'mars',
        4 => 'avril',
        5 => 'mai',
        6 => 'juin',
        7 => 'juillet',
        8 => 'aout',
        9 => 'septembre',
        10 => 'octobre',
        11 => 'novembre',
        12 => 'decembre',
    ];

    $month = $months[(int) $dt->format('n')] ?? $dt->format('m');
    return [
        'day' => $dt->format('d'),
        'month' => ucfirst(substr($month, 0, 3)),
        'year' => $dt->format('Y'),
        'full' => $dt->format('d') . ' ' . $month . ' ' . $dt->format('Y'),
    ];
}

function blog_display_date(array $post): string {
    $parts = blog_date_parts((string) (($post['published_at'] ?? '') ?: ($post['created_at'] ?? '')));
    return $parts['full'];
}

function blog_render_paragraphs(string $text): void {
    $chunks = preg_split('/\R{2,}/', trim($text)) ?: [];
    foreach ($chunks as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '') continue;
        echo '<p>' . nl2br(h($chunk), false) . '</p>';
    }
}

function blog_render_media(?string $type, ?string $source, ?string $poster = '', ?string $caption = '', string $className = ''): void {
    $source = blog_asset($source);
    $type = $type === 'video' ? 'video' : 'image';
    $caption = trim((string) $caption);
    $className = trim('blog-media ' . $className . ($type === 'video' ? ' is-video' : ''));

    echo '<figure class="' . h($className) . '">';
    if ($type === 'video') {
        echo '<video src="' . h($source) . '" controls preload="metadata"';
        $poster = trim((string) $poster);
        if ($poster !== '') {
            echo ' poster="' . h($poster) . '"';
        }
        echo '></video>';
    } else {
        echo '<img src="' . h($source) . '" alt="' . h($caption ?: 'Capital Holding KMB blog') . '" loading="lazy" decoding="async">';
    }
    if ($caption !== '') {
        echo '<figcaption>' . h($caption) . '</figcaption>';
    }
    echo '</figure>';
}

function blog_render_card(array $post, string $variant = ''): void {
    $url = blog_url($post);
    $title = (string) ($post['title'] ?? 'Article blog');
    $date = blog_display_date($post);
    $image = blog_asset((string) (($post['cover_image'] ?? '') ?: ($post['cover_video_poster'] ?? '')));
    $category = trim((string) ($post['category'] ?? ''));
    $excerpt = blog_excerpt($post, $variant === 'compact' ? 110 : 155);
    $className = trim('blog-card ' . $variant);
    ?>
    <article class="<?= h($className) ?>">
      <a class="blog-card-media" href="<?= h($url) ?>" aria-label="<?= h($title) ?>">
        <img src="<?= h($image) ?>" alt="<?= h($title) ?>" loading="lazy" decoding="async">
        <?php if (!empty($post['cover_video'])): ?><span class="blog-play-mark"><i class="fa-solid fa-play" aria-hidden="true"></i></span><?php endif; ?>
      </a>
      <div class="blog-card-body">
        <div class="blog-card-meta">
          <?php if ($date !== ''): ?><span><i class="fa-regular fa-calendar" aria-hidden="true"></i><?= h($date) ?></span><?php endif; ?>
          <?php if ($category !== ''): ?><span><?= h($category) ?></span><?php endif; ?>
        </div>
        <h3><a href="<?= h($url) ?>"><?= h($title) ?></a></h3>
        <?php if ($excerpt !== ''): ?><p><?= h($excerpt) ?></p><?php endif; ?>
        <a class="blog-card-link" href="<?= h($url) ?>"><span>Lire l article</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      </div>
    </article>
    <?php
}

function blog_render_header(string $title, string $description = ''): void {
    $description = $description !== '' ? $description : 'Actualites et articles de Capital Holding KMB.';
    ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= h(blog_truncate($description, 155)) ?>">
  <title><?= h($title) ?> | Capital Holding KMB</title>
  <link rel="icon" type="image/png" href="assets/favicon-kmb.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/site.css?v=20260714-blog">
</head>
<body class="blog-page" data-page="blog">
<div class="contact-strip" aria-label="Coordonnees">
  <div class="contact-strip-info">
    <a class="contact-strip-item" href="mailto:kmb@capitalholding-kmb.ma>
      <i class="fa-solid fa-envelope" aria-hidden="true"></i>
      <span><span class="contact-strip-label">EMAIL:</span>kmb@capitalholding-kmb.ma</span>
    </a>
    <a class="contact-strip-item" href="tel:+212537751412">
      <i class="fa-solid fa-phone" aria-hidden="true"></i>
      <span><span class="contact-strip-label">FIX:</span>+212 537 751412</span>
      <span><span class="contact-strip-label">MOBILE:</span>+212 661 868 619</span>
    </a>
    <a class="contact-strip-item" href="https://www.google.com/maps/search/?api=1&query=243%20Rue%20Zouheir%20Ben%20Qaiss%2C%20Rabat%2C%20Maroc" target="_blank" rel="noopener">
      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
      <span><span class="contact-strip-label">LIEU:</span>243 Rue Zouheir Ben Qaiss, Rabat, Maroc</span>
    </a>
  </div>
</div>
<header class="topbar">
  <a class="topbar-logo" href="index.php" aria-label="Capital Holding KMB - Accueil">
    <img src="assets/logo-kmb.png" alt="Capital Holding KMB">
  </a>
  <div class="language-switcher" aria-label="Language selector">
    <button type="button" data-lang="fr" aria-pressed="true">FR</button>
    <button type="button" data-lang="en" aria-pressed="false">EN</button>
    <button type="button" data-lang="ar" aria-pressed="false">AR</button>
  </div>
</header>
<nav aria-label="Navigation principale">
  <a class="brand" href="index.php" aria-label="Capital Holding KMB - Accueil">
    <img src="assets/logo-kmb.png" alt="Capital Holding KMB">
  </a>
  <ul class="nav-links" id="site-menu">
    <li><a href="index.php#who" data-i18n="nav.about">A Propos</a></li>
    <li class="nav-dropdown">
      <button class="dropdown-trigger" type="button" aria-expanded="false" aria-controls="sector-dropdown">
        <span data-i18n="nav.sectors">Secteurs</span>
        <span aria-hidden="true">v</span>
      </button>
      <div class="dropdown-menu" id="sector-dropdown">
        <a href="secteur.html?id=construction">Construction & Infrastructures</a>
        <a href="secteur.html?id=petroleum">Energies Renouvelables / Energie & Hydrocarbures</a>
        <a href="secteur.html?id=agriculture">Agriculture & Agroalimentaire</a>
        <a href="secteur.html?id=transport">Transport & Logistique</a>
        <a href="secteur.html?id=metals">Metaux Precieux & Or</a>
      </div>
    </li>
    <li><a href="index.php#subsidiaries" data-i18n="nav.subsidiaries">Filiales</a></li>
    <li><a href="blogs.php" class="is-active" data-i18n="nav.blog">Blog</a></li>
    <li><a href="index.php#contact" data-i18n="nav.contact">Contact</a></li>
  </ul>
  <div class="nav-actions">
    <a href="index.php#contact" class="nav-btn" data-i18n="nav.contact">Contact</a>
    <button class="menu-toggle" type="button" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="site-menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</nav>
<main id="content">
    <?php
}

function blog_render_footer(): void {
    ?>
</main>
<footer class="site-footer">
  <div class="footer-main">
    <div class="footer-brand">
      <img class="footer-logo" src="assets/LB.png" alt="Capital Holding KMB">
      <p>Holding multisectorielle basee a Rabat, active dans les secteurs strategiques du Maroc, de l Afrique et des hubs internationaux.</p>
    </div>
    <div class="footer-col">
      <h3>Navigation</h3>
      <a href="index.php#who" data-i18n="nav.about">A Propos</a>
      <a href="index.php#sectors" data-i18n="nav.sectors">Secteurs</a>
      <a href="index.php#subsidiaries" data-i18n="nav.subsidiaries">Filiales</a>
      <a href="blogs.php" data-i18n="nav.blog">Blog</a>
      <a href="index.php#contact" data-i18n="nav.contact">Contact</a>
    </div>
    <div class="footer-col">
      <h3>Reseaux sociaux</h3>
      <div class="social-links">
        <a href="https://wa.me/212661868619?text=Bonjour%20Capital%20Holding%20KMB%2C%20je%20souhaite%20plus%20d%27informations." target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; 2026 - Tous droits reserves</span>
  </div>
</footer>
<a class="whatsapp-toggle" href="https://wa.me/212661868619?text=Bonjour%20Capital%20Holding%20KMB%2C%20je%20souhaite%20plus%20d%27informations." target="_blank" rel="noopener" aria-label="Contacter Capital Holding KMB sur WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
<script src="assets/site.js?v=20260618-subs-bg-size"></script>
</body>
</html>
    <?php
}
