<?php
$pageTitle = 'Industries | Chartered Accountants for Startups, SMEs, and Corporates';
$metaDescription = 'Industries served by Japneet S & Associates in Gurugram, including startups, SMEs, professionals, corporates, NRIs, and venture-backed teams.';
$industries = [
    ['title' => 'Startups', 'copy' => 'Fast-moving teams needing clear MIS, runway visibility, and compliance without surprises.'],
    ['title' => 'SMEs', 'copy' => 'Disciplined accounting, tax, and payroll support with predictable calendars.'],
    ['title' => 'Professionals', 'copy' => 'Compliance, filings, and cash flow visibility for professional practices.'],
    ['title' => 'Corporates', 'copy' => 'Maker-checker controls, audit readiness, and governance support.'],
    ['title' => 'NRIs', 'copy' => 'Guidance on cross-border tax, filings, and entity needs.'],
    ['title' => 'Venture-backed teams', 'copy' => 'Board-ready reporting, diligence prep, and cap table events.'],
];
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$industryItems = [];
foreach ($industries as $index => $industry) {
    $industryItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => (string)($industry['title'] ?? ''),
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Industries', 'item' => $baseUrl . '/industries.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/industries.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/industries.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $industryItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Industries</div>
        <h1 class="reveal">Process discipline tailored to your industry.</h1>
        <p class="subhead reveal">From fast-scaling startups to established enterprises, we adapt our playbooks to your operating rhythm.</p>
        <div class="cta-row reveal" data-reveal="zoom">
            <a class="btn" href="contact.php#inquiry-form">Book a Consultation</a>
            <a class="btn ghost" href="services.php">View Services</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="industries-grid" data-stagger="80">
            <?php foreach ($industries as $item): ?>
                <div class="industry-card reveal" data-tilt>
                    <h3><?php echo h($item['title']); ?></h3>
                    <p class="muted"><?php echo h($item['copy']); ?></p>
                    <a class="link-arrow" href="contact.php#inquiry-form">Talk to us -></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Need an industry-specific plan?</h3>
                <p class="muted">We will map the compliance calendar, owners, and dashboards you need.</p>
            </div>
            <div>
                <a class="btn" href="contact.php">Contact us</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
