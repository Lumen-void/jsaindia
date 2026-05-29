<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Careers | Finance and Compliance Jobs in Gurugram';
$metaDescription = 'Explore current finance, accounting, tax, and compliance openings at Japneet S & Associates in Gurugram.';
$careers = get_careers();
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$careerItems = [];
foreach ($careers as $index => $job) {
    if (!is_array($job)) {
        continue;
    }
    $slug = trim((string)($job['slug'] ?? ''));
    $title = trim((string)($job['title'] ?? ''));
    if ($slug === '' || $title === '') {
        continue;
    }
    $careerItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'url' => $baseUrl . '/career.php?slug=' . rawurlencode($slug),
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Careers', 'item' => $baseUrl . '/careers.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/careers.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/careers.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $careerItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Careers</div>
        <h1 class="reveal">Job listings at Japneet S & Associates.</h1>
        <p class="subhead reveal">Explore current openings in Gurugram.</p>
    </div>
</section>

<section class="section">
    <div class="container grid grid-3" data-stagger="90">
        <?php foreach ($careers as $job): ?>
            <div class="card reveal" data-tilt>
                <h3><?php echo h($job['title']); ?></h3>
                <p class="muted"><?php echo h($job['location']); ?> | <?php echo h($job['type']); ?></p>
                <p><?php echo h($job['overview']); ?></p>
                <a class="link-arrow" href="career.php?slug=<?php echo urlencode($job['slug']); ?>">View role -></a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Don't see a role yet?</h3>
                <p class="muted">Write to us with your profile and interest area.</p>
            </div>
            <div>
                <a class="btn" href="mailto:jm@jsaindia.com">jm@jsaindia.com</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
