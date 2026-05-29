<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Case Studies | Accounting and Compliance Outcomes';
$metaDescription = 'Read case studies from Japneet S & Associates covering accounting, compliance, payroll, and finance outcomes.';
$caseStudies = get_case_studies();
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$caseItems = [];
foreach ($caseStudies as $index => $case) {
    if (!is_array($case)) {
        continue;
    }
    $title = trim((string)($case['title'] ?? ''));
    if ($title === '') {
        continue;
    }
    $caseItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'item' => [
            '@type' => 'CreativeWork',
            'name' => $title,
            'description' => trim((string)($case['outcome'] ?? '')),
        ],
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Case Studies', 'item' => $baseUrl . '/case-studies.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/case-studies.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/case-studies.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $caseItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Case Studies</div>
        <h1 class="reveal">Proof of disciplined execution.</h1>
        <p class="subhead reveal">How we keep teams compliant, confident, and ready for audits and investor conversations.</p>
    </div>
</section>

<section class="section">
    <div class="container grid grid-3" data-stagger="90">
        <?php foreach ($caseStudies as $case): ?>
            <div class="card case-card reveal" data-tilt>
                <h3><?php echo h($case['title']); ?></h3>
                <div class="case-meta"><?php echo h($case['industry']); ?></div>
                <p><strong>Problem:</strong> <?php echo h($case['problem']); ?></p>
                <p><strong>Approach:</strong> <?php echo h($case['solution']); ?></p>
                <p><strong>Outcome:</strong> <?php echo h($case['outcome']); ?></p>
                <div>
                    <?php foreach ($case['metrics'] as $metric): ?>
                        <span class="metric-tag"><?php echo h($metric); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Want similar outcomes?</h3>
                <p class="muted">Share your goals and we will outline the milestones and timelines.</p>
            </div>
            <div>
                <a class="btn" href="contact.php#inquiry-form">Talk to us</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
