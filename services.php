<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Services | Accounting, Tax, Payroll, ROC, and Virtual CFO';
$metaDescription = 'Explore accounting, tax consultancy, secretarial compliance, payroll, Virtual CFO, and business support services from Japneet S & Associates in Gurugram.';
$services = get_services();
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$serviceItems = [];
foreach ($services as $index => $service) {
    if (!is_array($service)) {
        continue;
    }
    $slug = trim((string)($service['slug'] ?? ''));
    $title = trim((string)($service['title'] ?? ''));
    if ($slug === '' || $title === '') {
        continue;
    }
    $serviceItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'url' => $baseUrl . '/service.php?slug=' . rawurlencode($slug),
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => $baseUrl . '/services.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/services.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/services.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $serviceItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Services</div>
        <h1 class="reveal">Complexity made simple.</h1>
        <p class="subhead reveal">At Japneet S & Associates, we provide tailored financial services to help you thrive.</p>
        <div class="cta-row reveal" data-reveal="zoom">
            <a class="btn" href="contact.php#inquiry-form">Get started</a>
            <a class="btn ghost" href="#services-grid">View all services</a>
        </div>
    </div>
</section>

<section class="section" id="services-grid">
    <div class="container grid grid-3" data-stagger="90">
        <?php foreach ($services as $service): ?>
            <?php $desc = trim((string)($service['short_intro'] ?? $service['hero_line'] ?? '')); ?>
            <div class="card reveal" data-tilt>
                <div class="service-icon"><?php echo h($service['icon'] ?? strtoupper(substr($service['title'], 0, 2))); ?></div>
                <h3><?php echo h($service['title']); ?></h3>
                <p class="muted"><?php echo h($service['short_intro'] ?? $service['hero_line'] ?? ''); ?></p>
                <div class="service-actions">
                    <a class="link-arrow" href="service.php?slug=<?php echo urlencode($service['slug']); ?>">View details -></a>
                    <button class="service-book-btn" type="button" data-book-service data-service="<?php echo h($service['title']); ?>" data-service-desc="<?php echo h($desc); ?>" data-service-slug="<?php echo h($service['slug'] ?? ''); ?>">Request to Book</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>How we work</h3>
                <p class="muted">Steady cadence with maker-checker controls on filings and MIS.</p>
                <ul class="list-inline">
                    <li>Discover</li>
                    <li>Assess</li>
                    <li>Execute</li>
                    <li>Support</li>
                </ul>
            </div>
            <div>
                <a class="btn" href="contact.php#inquiry-form">Start a project</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
