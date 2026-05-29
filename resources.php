<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Resources | Compliance Checklists and Trackers';
$metaDescription = 'Download compliance checklists, trackers, and practical resources from Japneet S & Associates.';

$resources = get_site_resources();
$resources = array_values(array_filter($resources, fn($r) => is_array($r) && (string)($r['status'] ?? 'published') === 'published'));
usort($resources, function (array $a, array $b) {
    $ao = (int)($a['sort_order'] ?? 999);
    $bo = (int)($b['sort_order'] ?? 999);
    if ($ao === $bo) {
        return strcmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
    }
    return $ao <=> $bo;
});

$cats = [];
foreach ($resources as $r) {
    $c = trim((string)($r['category'] ?? ''));
    if ($c !== '') $cats[$c] = true;
}
$cats = array_keys($cats);
sort($cats);
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$resourceItems = [];
foreach ($resources as $index => $resource) {
    $title = trim((string)($resource['title'] ?? ''));
    $fileUrl = trim((string)($resource['file_url'] ?? ''));
    if ($title === '' || $fileUrl === '') {
        continue;
    }
    $absoluteUrl = str_starts_with($fileUrl, 'http') ? $fileUrl : $baseUrl . '/' . ltrim($fileUrl, '/');
    $resourceItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'url' => $absoluteUrl,
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Resources', 'item' => $baseUrl . '/resources.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/resources.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/resources.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $resourceItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero hero-small">
    <div class="container">
        <p class="eyebrow reveal">Resources</p>
        <h1 class="reveal">Checklists and trackers</h1>
        <p class="subhead reveal">Downloads to help you run compliance with calmer execution. Always verify with original notifications and portals.</p>
        <div class="cta-row reveal">
            <a class="btn" href="contact.php#inquiry-form">Book a Consultation</a>
            <a class="btn ghost" href="updates.php">Compliance Desk</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2 class="reveal">Download library</h2>
                <p class="muted reveal">Filter by category and download practical JSA templates and checklists.</p>
            </div>
        </div>

        <div class="updates-tabs reveal" data-reveal="zoom" role="tablist" aria-label="Resource categories">
            <button class="tab active" type="button" data-res-filter="all" role="tab" aria-selected="true">All</button>
            <?php foreach ($cats as $c): ?>
                <button class="tab" type="button" data-res-filter="<?php echo h(slugify($c)); ?>" role="tab" aria-selected="false"><?php echo h($c); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-3" data-stagger="90" id="resourcesGrid">
            <?php foreach ($resources as $r): ?>
                <?php
                $title = (string)($r['title'] ?? '');
                $desc = (string)($r['description'] ?? '');
                $cat = (string)($r['category'] ?? '');
                $url = (string)($r['file_url'] ?? '');
                $type = strtoupper((string)($r['file_type'] ?? ''));
                $featured = !empty($r['featured']);
                ?>
                <div class="card reveal resource-card" data-tilt data-category="<?php echo h(slugify($cat)); ?>">
                    <div class="meta">
                        <?php if ($cat !== ''): ?><span class="pill"><?php echo h($cat); ?></span><?php endif; ?>
                        <?php if ($type !== ''): ?><span class="pill soft"><?php echo h($type); ?></span><?php endif; ?>
                        <?php if ($featured): ?><span class="pill new">Featured</span><?php endif; ?>
                    </div>
                    <h3><?php echo h($title); ?></h3>
                    <p class="muted"><?php echo h($desc); ?></p>
                    <div class="cta-row" style="margin-top:12px;">
                        <?php if ($url !== ''): ?>
                            <a class="btn" href="<?php echo h($url); ?>" download>Download</a>
                        <?php endif; ?>
                        <a class="btn ghost" href="contact.php?source=Resources&service=<?php echo urlencode($cat ?: 'Resources'); ?>#inquiry-form">Customize for my firm</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cta-panel reveal" data-reveal="zoom" style="margin-top: 22px;">
            <div>
                <h3>Want a client-ready compliance calendar?</h3>
                <p class="muted">We can set up a calendar with ownership, buffers, and a filing tracker rhythm.</p>
            </div>
            <div class="cta-row" style="margin:0;">
                <a class="btn" href="contact.php#inquiry-form">Talk to our team</a>
                <a class="btn ghost" href="services.php">Explore services</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
