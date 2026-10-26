<?php
declare(strict_types=1);

require_once __DIR__ . '/blog-lib.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = blog_find_post($slug);

if (!$post) {
    http_response_code(404);
    blog_render_header('Article introuvable', 'Article blog introuvable.');
    ?>
    <section class="blog-empty blog-empty-page">
      <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
      <h1>Article introuvable</h1>
      <p>Cet article est indisponible ou n est pas encore publie.</p>
      <a class="btn-gold" href="blogs.php">Retour au blog</a>
    </section>
    <?php
    blog_render_footer();
    exit;
}

$blocks = blog_post_blocks((int) $post['id']);
$latestPosts = blog_latest_posts(4, (int) $post['id']);
$relatedPosts = blog_related_posts($post, 4);
$dateParts = blog_date_parts((string) (($post['published_at'] ?? '') ?: ($post['created_at'] ?? '')));
$description = blog_excerpt($post, 155);
$title = (string) ($post['title'] ?? 'Article blog');
$subtitle = trim((string) ($post['subtitle'] ?? ''));
$bodyText = trim((string) ($post['body_text'] ?? ''));
$author = trim((string) ($post['author_name'] ?? ''));
$category = trim((string) ($post['category'] ?? ''));

function blog_detail_cover(array $post, string $className = ''): void {
    $hasVideo = trim((string) ($post['cover_video'] ?? '')) !== '';
    $mediaType = $hasVideo ? 'video' : 'image';
    $source = $hasVideo ? (string) $post['cover_video'] : (string) ($post['cover_image'] ?? '');
    $poster = (string) (($post['cover_video_poster'] ?? '') ?: ($post['cover_image'] ?? ''));
    blog_render_media($mediaType, $source, $poster, '', trim('blog-main-media ' . $className));
}

function blog_detail_block(array $block): void {
    $type = (string) ($block['block_type'] ?? 'text');
    $title = trim((string) ($block['block_title'] ?? ''));
    $text = trim((string) ($block['body_text'] ?? ''));
    $media = trim((string) ($block['media_path'] ?? ''));
    $poster = trim((string) ($block['poster_path'] ?? ''));
    $caption = trim((string) ($block['caption'] ?? ''));
    $align = (string) ($block['align_style'] ?? 'full');
    if (!in_array($align, ['full', 'left', 'right'], true)) $align = 'full';

    if (($type === 'image' || $type === 'video') && $media === '') return;
    if (($type === 'text' || $type === 'quote') && $title === '' && $text === '') return;

    if ($type === 'quote') {
        echo '<blockquote class="blog-quote blog-block-' . h($align) . '">';
        if ($text !== '') echo '<p>' . h($text) . '</p>';
        if ($title !== '') echo '<cite>' . h($title) . '</cite>';
        echo '</blockquote>';
        return;
    }

    if ($type === 'image' || $type === 'video') {
        echo '<section class="blog-content-block has-media blog-block-' . h($align) . '">';
        if ($title !== '') echo '<h2>' . h($title) . '</h2>';
        blog_render_media($type, $media, $poster, $caption, 'blog-block-media');
        if ($text !== '') blog_render_paragraphs($text);
        echo '</section>';
        return;
    }

    echo '<section class="blog-content-block blog-block-' . h($align) . '">';
    if ($title !== '') echo '<h2>' . h($title) . '</h2>';
    if ($text !== '') blog_render_paragraphs($text);
    echo '</section>';
}

blog_render_header($title, $description);
?>

<section class="blog-detail-hero fade">
  <div class="blog-detail-title">
    <?php if ($category !== ''): ?><span class="section-label"><?= h($category) ?></span><?php else: ?><span class="section-label">Blog</span><?php endif; ?>
    <h1><?= h($title) ?></h1>
    <?php if ($subtitle !== ''): ?><p><?= h($subtitle) ?></p><?php endif; ?>
    <div class="blog-detail-meta">
      <?php if ($dateParts['full'] !== ''): ?><span><i class="fa-regular fa-calendar" aria-hidden="true"></i><?= h($dateParts['full']) ?></span><?php endif; ?>
      <?php if ($author !== ''): ?><span><i class="fa-regular fa-user" aria-hidden="true"></i><?= h($author) ?></span><?php endif; ?>
    </div>
  </div>
</section>

<section class="blog-detail-layout fade">
  <article class="blog-article">
    <?php if (($post['media_position'] ?? 'top') === 'top'): ?>
      <div class="blog-cover-wrap">
        <?php if ($dateParts['day'] !== ''): ?>
          <span class="blog-date-badge"><strong><?= h($dateParts['day']) ?></strong><small><?= h($dateParts['month']) ?></small></span>
        <?php endif; ?>
        <?php blog_detail_cover($post); ?>
      </div>
    <?php endif; ?>

    <?php if ($description !== ''): ?>
      <div class="blog-intro">
        <p><?= h($description) ?></p>
      </div>
    <?php endif; ?>

    <?php if (($post['media_position'] ?? '') === 'after_intro'): ?>
      <div class="blog-cover-wrap">
        <?php blog_detail_cover($post); ?>
      </div>
    <?php endif; ?>

    <?php if ($bodyText !== ''): ?>
      <section class="blog-content-block blog-main-text">
        <?php blog_render_paragraphs($bodyText); ?>
      </section>
    <?php endif; ?>

    <?php foreach ($blocks as $block): ?>
      <?php blog_detail_block($block); ?>
    <?php endforeach; ?>

    <?php if (($post['media_position'] ?? '') === 'bottom'): ?>
      <div class="blog-cover-wrap">
        <?php blog_detail_cover($post); ?>
      </div>
    <?php endif; ?>
  </article>

  <aside class="blog-sidebar" aria-label="Articles recents">
    <section class="blog-side-panel">
      <h2>Articles recents</h2>
      <?php if ($latestPosts): ?>
        <div class="blog-side-list">
          <?php foreach ($latestPosts as $item): ?>
            <a class="blog-side-item" href="<?= h(blog_url($item)) ?>">
              <img src="<?= h(blog_asset((string) (($item['cover_image'] ?? '') ?: ($item['cover_video_poster'] ?? '')))) ?>" alt="<?= h($item['title'] ?? 'Article blog') ?>" loading="lazy" decoding="async">
              <span>
                <strong><?= h(blog_truncate((string) ($item['title'] ?? ''), 72)) ?></strong>
                <small><?= h(blog_display_date($item)) ?></small>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Aucun autre article publie.</p>
      <?php endif; ?>
    </section>
  </aside>
</section>

<section class="blog-related fade" aria-labelledby="blog-related-title">
  <div class="blog-section-head">
    <div>
      <span class="section-label">A lire aussi</span>
      <h2 id="blog-related-title">Articles recents et precedents</h2>
    </div>
    <a class="blog-card-link" href="blogs.php"><span>Voir tout</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
  </div>
  <?php if ($relatedPosts): ?>
    <div class="blog-grid related">
      <?php foreach ($relatedPosts as $item): ?>
        <?php blog_render_card($item, 'compact'); ?>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="blog-empty small">
      <p>Les prochains articles apparaitront ici automatiquement.</p>
    </div>
  <?php endif; ?>
</section>

<?php blog_render_footer(); ?>
