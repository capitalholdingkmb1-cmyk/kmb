<?php
declare(strict_types=1);

require_once __DIR__ . '/blog-lib.php';

$posts = blog_latest_posts(24);
$featured = $posts[0] ?? null;
$pageDescription = 'Actualites, projets et analyses de Capital Holding KMB.';

blog_render_header('Blog', $pageDescription);
?>

<section class="blog-index-hero fade">
  <div class="blog-index-copy">
    <span class="section-label">Blog</span>
    <h1>Actualites Capital Holding KMB</h1>
    <p>Articles, annonces et contenus medias publies depuis le panneau admin.</p>
  </div>
  <?php if ($featured): ?>
    <div class="blog-index-feature">
      <?php blog_render_card($featured, 'featured'); ?>
    </div>
  <?php endif; ?>
</section>

<section class="blog-list-section fade" aria-labelledby="blog-list-title">
  <div class="blog-section-head">
    <div>
      <span class="section-label">Articles</span>
      <h2 id="blog-list-title">Dernieres publications</h2>
    </div>
  </div>

  <?php if ($posts): ?>
    <div class="blog-grid">
      <?php foreach ($posts as $post): ?>
        <?php blog_render_card($post); ?>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="blog-empty">
      <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
      <h2>Aucun article publie pour le moment</h2>
      <p>Les articles ajoutes depuis l espace admin apparaitront ici automatiquement.</p>
    </div>
  <?php endif; ?>
</section>

<?php blog_render_footer(); ?>
