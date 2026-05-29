<?php
require_once __DIR__ . '/inc/data.php';
$slug = $_GET['slug'] ?? '';
$post = $slug ? get_blog_by_slug($slug) : null;

if (!$post) {
    $pageTitle = 'Post not found | Japneet S & Associates';
    $metaDescription = 'Requested article could not be found.';
    $metaRobots = 'noindex,follow';
    http_response_code(404);
    require __DIR__ . '/inc/header.php';
    ?>
    <section class="section">
        <div class="container">
            <h1>Article not found</h1>
            <p class="subhead">This article is unavailable. Explore other insights.</p>
            <a class="btn" href="blog.php">Back to insights</a>
        </div>
    </section>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' | Insights | Japneet S & Associates';
$metaDescription = trim((string)($post['excerpt'] ?? '')) ?: 'Insights from Japneet S & Associates on accounting, tax, payroll, compliance, and advisory.';
$postImage = normalize_image_url((string)($post['featured_image'] ?? ''), placeholder_image('featured'));
$metaImage = str_starts_with($postImage, 'http') ? $postImage : 'https://www.jsaindia.com/' . ltrim($postImage, '/');
$metaImageAlt = (string)($post['title'] ?? 'Insight article image');
$ogType = 'article';
$postDate = (string)($post['date'] ?? '');
$postDateIso = $postDate !== '' ? date('c', strtotime($postDate) ?: time()) : '';
$metaPublishedTime = $postDateIso;
$metaModifiedTime = $postDateIso;
$metaSection = (string)($post['category'] ?? '');
$readingMinutes = estimate_reading_time_minutes($post['content'] ?? '');
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://www.jsaindia.com/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Insights', 'item' => 'https://www.jsaindia.com/blog.php'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => ($post['title'] ?? ''), 'item' => 'https://www.jsaindia.com/post.php?slug=' . urlencode($post['slug'] ?? '')],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => 'https://www.jsaindia.com/post.php?slug=' . urlencode($post['slug'] ?? ''),
        'headline' => ($post['title'] ?? ''),
        'datePublished' => $postDateIso,
        'dateModified' => $postDateIso,
        'articleSection' => ($post['category'] ?? ''),
        'image' => $metaImage,
        'author' => ['@type' => 'Organization', 'name' => 'Japneet S & Associates', 'url' => 'https://www.jsaindia.com/'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Japneet S & Associates',
            'url' => 'https://www.jsaindia.com/',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://www.jsaindia.com/imagesandlogo/jsalogo2.jpeg',
            ],
        ],
        'description' => ($post['excerpt'] ?? ''),
        'mainEntityOfPage' => 'https://www.jsaindia.com/post.php?slug=' . urlencode($post['slug'] ?? ''),
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal"><?php echo h($post['category']); ?></div>
        <nav class="breadcrumbs reveal" data-reveal="zoom" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="crumb-sep">/</span>
            <a href="blog.php">Insights</a>
            <span class="crumb-sep">/</span>
            <span aria-current="page"><?php echo h($post['title']); ?></span>
        </nav>
        <h1 class="reveal"><?php echo h($post['title']); ?></h1>
        <div class="hero-meta reveal" data-reveal="zoom">
            <span><?php echo format_date($post['date']); ?></span>
            <span>Category: <?php echo h($post['category']); ?></span>
            <span><?php echo h($readingMinutes); ?> min read</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container two-col">
        <article class="card blog-content reveal" data-tilt>
            <img loading="lazy" decoding="async" src="<?php echo h($postImage); ?>" alt="<?php echo h($post['title']); ?>" style="border-radius:12px; margin-bottom:16px;">
            <?php
            $raw = (string)($post['content'] ?? '');
            $lines = preg_split("/\r\n|\n|\r/", $raw);
            $toc = [];
            $html = '';
            $inList = false;
            foreach ($lines as $line) {
                $t = trim($line);
                if ($t === '') {
                    if ($inList) {
                        $html .= '</ul>';
                        $inList = false;
                    }
                    continue;
                }
                if (str_starts_with($t, '### ')) {
                    if ($inList) {
                        $html .= '</ul>';
                        $inList = false;
                    }
                    $title = trim(substr($t, 4));
                    $id = slugify($title);
                    $toc[] = ['level' => 3, 'title' => $title, 'id' => $id];
                    $html .= '<h3 id="' . h($id) . '">' . h($title) . '</h3>';
                    continue;
                }
                if (str_starts_with($t, '## ')) {
                    if ($inList) {
                        $html .= '</ul>';
                        $inList = false;
                    }
                    $title = trim(substr($t, 3));
                    $id = slugify($title);
                    $toc[] = ['level' => 2, 'title' => $title, 'id' => $id];
                    $html .= '<h2 id="' . h($id) . '">' . h($title) . '</h2>';
                    continue;
                }
                if (str_starts_with($t, '- ')) {
                    if (!$inList) {
                        $html .= '<ul class="list-bullets">';
                        $inList = true;
                    }
                    $html .= '<li><span class="marker">&bull;</span>' . h(trim(substr($t, 2))) . '</li>';
                    continue;
                }
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $html .= '<p>' . h($t) . '</p>';
            }
            if ($inList) {
                $html .= '</ul>';
            }
            echo $html;
            ?>
        </article>
        <div>
            <?php if (!empty($toc)): ?>
                <div class="card reveal" data-reveal="right" data-tilt>
                    <h3>On this page</h3>
                    <ul class="list-bullets">
                        <?php foreach ($toc as $item): ?>
                            <li><span class="marker">&bull;</span><a href="#<?php echo h($item['id']); ?>"><?php echo h($item['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="card reveal" data-reveal="right" data-tilt>
                <h3>Need help with this topic?</h3>
                <p class="muted">Talk to our team for an approach tailored to your business.</p>
                <a class="btn" href="contact.php">Contact us</a>
            </div>
            <div class="card reveal" data-reveal="right" data-tilt style="margin-top:14px;">
                <h4>Other categories</h4>
                <ul class="list-inline">
                    <?php foreach (array_values(array_unique(array_map(fn($b) => $b['category'], get_published_blogs()))) as $cat): ?>
                        <li><?php echo h($cat); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Need help implementing these ideas?</h3>
                <p class="muted">We can review your current setup and send a clear action plan.</p>
            </div>
            <div>
                <a class="btn" href="contact.php#inquiry-form">Book a consultation</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
