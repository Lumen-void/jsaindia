<?php
require_once __DIR__ . '/data.php';
$pageTitle = $pageTitle ?? 'Japneet S & Associates | Chartered Accountants in Gurugram';
$metaDescription = $metaDescription ?? 'Japneet S & Associates is a Chartered Accountancy firm in Gurugram offering accounting, GST, income tax, payroll, ROC compliance, and Virtual CFO services.';
$metaRobots = $metaRobots ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
$ogType = $ogType ?? 'website';
$metaImageAlt = $metaImageAlt ?? 'Japneet S & Associates logo';
$metaPublishedTime = $metaPublishedTime ?? '';
$metaModifiedTime = $metaModifiedTime ?? '';
$metaSection = $metaSection ?? '';
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$aboutActive = in_array($currentScript, ['about.php', 'team.php'], true);
$servicesActive = in_array($currentScript, ['services.php', 'service.php'], true);
$careersActive = in_array($currentScript, ['careers.php', 'career.php'], true);
$latestUpdatesActive = in_array($currentScript, ['case-studies.php', 'updates.php', 'resources.php'], true);
$insightsActive = in_array($currentScript, ['blog.php', 'post.php'], true);
$schemaExtra = $schemaExtra ?? [];
$announcement = get_announcement();
$navServices = array_slice(get_services(), 0, 7);
if (!function_exists('jsa_asset_url')) {
    function jsa_asset_url(string $path): string
    {
        $cleanPath = ltrim($path, '/');
        $fullPath = dirname(__DIR__) . '/' . $cleanPath;
        if (is_file($fullPath)) {
            return $cleanPath . '?v=' . rawurlencode((string)filemtime($fullPath));
        }
        return $cleanPath;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($metaDescription); ?>">
    <meta name="application-name" content="Japneet S & Associates">
    <meta name="apple-mobile-web-app-title" content="Japneet S & Associates">
    <meta name="robots" content="<?php echo h($metaRobots); ?>">
    <title><?php echo h($pageTitle); ?></title>
    <script>
        document.documentElement.classList.add('js');
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <?php
    $baseUrl = rtrim((string)(getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com'), '/');
    $brandName = 'Japneet S & Associates';
    $brandShortName = 'JSA';
    $officeEmail = 'jm@jsaindia.com';
    $orgDescription = 'Japneet S & Associates is a Chartered Accountancy firm in Gurugram offering accounting, GST, income tax, payroll, ROC compliance, and Virtual CFO services.';
    $officeAddress = [
        '@type' => 'PostalAddress',
        'streetAddress' => 'MGF Metropolis Mall, MG Road',
        'addressLocality' => 'Gurugram',
        'addressRegion' => 'Haryana',
        'addressCountry' => 'IN',
    ];
    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $parsedRequestUri = parse_url($requestUri);
    $canonicalPath = (string)($parsedRequestUri['path'] ?? '/');
    if ($canonicalPath === '' || $canonicalPath === '/index.php') {
        $canonicalPath = '/';
    }
    $allowedQueryParamsByScript = [
        'service.php' => ['slug'],
        'post.php' => ['slug'],
        'career.php' => ['slug'],
        'partner.php' => ['slug'],
    ];
    $allowedQueryParams = $allowedQueryParamsByScript[$currentScript] ?? [];
    $canonicalQuery = [];
    if (!empty($parsedRequestUri['query']) && !empty($allowedQueryParams)) {
        parse_str((string)$parsedRequestUri['query'], $queryParams);
        foreach ($allowedQueryParams as $param) {
            $value = $queryParams[$param] ?? null;
            if (is_string($value) && $value !== '') {
                $canonicalQuery[$param] = $value;
            }
        }
    }
    $canonicalUrl = $baseUrl . $canonicalPath;
    if (!empty($canonicalQuery)) {
        $canonicalUrl .= '?' . http_build_query($canonicalQuery);
    }
    $orgLogoUrl = $baseUrl . '/' . ltrim(jsa_asset_url('imagesandlogo/jsalogo2.jpeg'), '/');
    $metaImage = $metaImage ?? $orgLogoUrl;
    $organizationId = $baseUrl . '#organization';
    $websiteId = $baseUrl . '#website';
    $orgTelephone = getenv('JSA_TELEPHONE') ?: '';
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => $organizationId,
        'name' => $brandName,
        'alternateName' => $brandShortName,
        'url' => $baseUrl,
        'logo' => $orgLogoUrl,
        'image' => $orgLogoUrl,
        'email' => $officeEmail,
        'description' => $orgDescription,
        'address' => $officeAddress,
        'areaServed' => [
            ['@type' => 'City', 'name' => 'Gurugram'],
            ['@type' => 'AdministrativeArea', 'name' => 'Haryana'],
            ['@type' => 'Country', 'name' => 'India'],
        ],
    ];
    if ($orgTelephone !== '') {
        $organization['telephone'] = $orgTelephone;
        $organization['contactPoint'] = [
            [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'telephone' => $orgTelephone,
                'email' => $officeEmail,
                'areaServed' => 'IN',
                'availableLanguage' => ['en', 'hi'],
            ],
        ];
    }
    $schemas = [
        $organization,
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'name' => $brandName,
            'alternateName' => $brandShortName,
            'url' => $baseUrl,
            'description' => $orgDescription,
            'publisher' => ['@id' => $organizationId],
            'inLanguage' => 'en-IN',
        ],
    ];
    if (is_array($schemaExtra)) {
        foreach ($schemaExtra as $item) {
            if (is_array($item)) {
                $schemas[] = $item;
            }
        }
    }
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
    ?>
    <link rel="canonical" href="<?php echo h($canonicalUrl); ?>">
    <meta property="og:type" content="<?php echo h($ogType); ?>">
    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="Japneet S & Associates">
    <meta property="og:title" content="<?php echo h($pageTitle); ?>">
    <meta property="og:description" content="<?php echo h($metaDescription); ?>">
    <meta property="og:url" content="<?php echo h($canonicalUrl); ?>">
    <meta property="og:image" content="<?php echo h($metaImage); ?>">
    <meta property="og:image:alt" content="<?php echo h($metaImageAlt); ?>">
    <?php if ($metaPublishedTime !== ''): ?>
        <meta property="article:published_time" content="<?php echo h($metaPublishedTime); ?>">
    <?php endif; ?>
    <?php if ($metaModifiedTime !== ''): ?>
        <meta property="article:modified_time" content="<?php echo h($metaModifiedTime); ?>">
    <?php endif; ?>
    <?php if ($metaSection !== ''): ?>
        <meta property="article:section" content="<?php echo h($metaSection); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo h($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo h($metaImage); ?>">
    <meta name="twitter:image:alt" content="<?php echo h($metaImageAlt); ?>">
    <link rel="icon" type="image/png" href="<?php echo h(jsa_asset_url('favicon.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo h(jsa_asset_url('favicon.png')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo h(jsa_asset_url('imagesandlogo/favicon-32x32.png')); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo h(jsa_asset_url('imagesandlogo/favicon-16x16.png')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo h(jsa_asset_url('apple-touch-icon.png')); ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo h(jsa_asset_url('imagesandlogo/android-chrome-192x192.png')); ?>">
    <link rel="stylesheet" href="<?php echo h(jsa_asset_url('assets/animations.css')); ?>">
    <link rel="stylesheet" href="<?php echo h(jsa_asset_url('assets/styles.css')); ?>">
</head>
<body id="top" data-page="<?php echo h($currentScript ?: 'index.php'); ?>">
<?php if (!empty($announcement['active']) && !empty($announcement['text'])): ?>
    <div class="announce-bar" data-announce-id="<?php echo h((string)($announcement['id'] ?? 'default')); ?>" data-kind="<?php echo h((string)($announcement['kind'] ?? 'info')); ?>">
        <div class="container announce-inner">
            <div class="announce-text">
                <?php if (!empty($announcement['link'])): ?>
                    <a href="<?php echo h((string)$announcement['link']); ?>"><?php echo h((string)$announcement['text']); ?></a>
                <?php else: ?>
                    <?php echo h((string)$announcement['text']); ?>
                <?php endif; ?>
            </div>
            <button class="announce-close" type="button" aria-label="Close announcement">Close</button>
        </div>
    </div>
<?php endif; ?>
<header class="site-header">
    <div class="container header-inner">
        <div class="logo">
            <a href="index.php" aria-label="Japneet S & Associates">
                <img class="logo-img" src="<?php echo h(jsa_asset_url('imagesandlogo/jsalogo2.jpeg')); ?>" alt="Japneet S & Associates logo">
            </a>
        </div>
        <nav class="nav" aria-label="Main navigation">
            <div class="nav-item has-mega">
                <a href="about.php" <?php echo $aboutActive ? 'aria-current="page"' : ''; ?> aria-haspopup="true" aria-expanded="false">About</a>
                <div class="mega" role="menu" aria-label="About menu">
                    <div class="mega-grid">
                        <div>
                            <div class="mega-title">About JSA</div>
                            <ul class="mega-list">
                                <li><a href="about.php">About Us</a></li>
                                <li><a href="about.php#team">Our Team</a></li>
                            </ul>
                            <div class="cta-row" style="margin-top:12px;">
                                <a class="btn ghost" href="about.php#team">Meet the team</a>
                            </div>
                        </div>
                        <div>
                            <div class="mega-title">Why clients choose us</div>
                            <div class="mega-note">Partner-led execution, clear communication, and audit-ready delivery across accounting, tax, payroll, and compliance.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-item has-mega">
                <a href="services.php" <?php echo $servicesActive ? 'aria-current="page"' : ''; ?> aria-haspopup="true" aria-expanded="false">Services</a>
                <div class="mega" role="menu" aria-label="Services menu">
                    <div class="mega-grid">
                        <div>
                            <div class="mega-title">Popular services</div>
                            <ul class="mega-list">
                                <?php foreach ($navServices as $svc): ?>
                                    <?php
                                    if (!is_array($svc)) continue;
                                    $slug = (string)($svc['slug'] ?? '');
                                    $title = (string)($svc['title'] ?? '');
                                    if ($slug === '' || $title === '') continue;
                                    ?>
                                    <li><a href="service.php?slug=<?php echo urlencode($slug); ?>"><?php echo h($title); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="cta-row" style="margin-top:12px;">
                                <a class="btn ghost" href="services.php">All services</a>
                            </div>
                        </div>
                        <div>
                            <div class="mega-title">Quick links</div>
                            <ul class="mega-list">
                                <li><a href="industries.php">Industries</a></li>
                                <li><a href="case-studies.php">Case Studies</a></li>
                                <li><a href="updates.php">Regulatory Updates</a></li>
                                <li><a href="resources.php">Resources</a></li>
                                <li><a href="blog.php">Insights</a></li>
                            </ul>
                            <div class="mega-note">Tip: Use Search to find services, updates, and insights quickly.</div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="careers.php" <?php echo $careersActive ? 'aria-current="page"' : ''; ?>>Careers</a>
            <div class="nav-item has-mega">
                <a href="updates.php" <?php echo $latestUpdatesActive ? 'aria-current="page"' : ''; ?> aria-haspopup="true" aria-expanded="false">Latest Updates</a>
                <div class="mega" role="menu" aria-label="Latest updates menu">
                    <div class="mega-grid">
                        <div>
                            <div class="mega-title">Stay updated</div>
                            <ul class="mega-list">
                                <li><a href="case-studies.php">Case Studies</a></li>
                                <li><a href="updates.php">Regulatory Updates</a></li>
                                <li><a href="resources.php">Resources</a></li>
                            </ul>
                            <div class="cta-row" style="margin-top:12px;">
                                <a class="btn ghost" href="updates.php">Open updates hub</a>
                            </div>
                        </div>
                        <div>
                            <div class="mega-title">What you will find</div>
                            <div class="mega-note">Recent casework, practical regulatory guidance, and downloadable compliance resources in one place.</div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="blog.php" <?php echo $insightsActive ? 'aria-current="page"' : ''; ?>>Insights</a>
            <a href="contact.php" <?php echo $currentScript === 'contact.php' ? 'aria-current="page"' : ''; ?>>Contact</a>
        </nav>
        <div class="header-actions">
            <button class="icon-btn search-toggle" type="button" aria-label="Search">
                <span class="icon-btn-text">Search</span>
                <span class="ms-icon" aria-hidden="true">search</span>
            </button>
            <a class="btn" href="contact.php#inquiry-form">Book a Consultation</a>
        </div>
        <button class="menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
            <span class="menu-toggle-text">Menu</span>
            <span class="menu-toggle-bars" aria-hidden="true"></span>
        </button>
    </div>
    <div class="drawer-overlay" hidden></div>
    <aside class="nav-drawer" id="mobile-drawer" aria-hidden="true">
        <div class="drawer-head">
            <div class="logo">
                <img class="logo-img" src="<?php echo h(jsa_asset_url('imagesandlogo/jsalogo2.jpeg')); ?>" alt="Japneet S & Associates logo">
            </div>
            <button class="drawer-close" type="button" aria-label="Close menu">Close</button>
        </div>
        <div class="drawer-intro">
            <div class="drawer-kicker">Explore JSA</div>
            <p>Find services, read insights, and contact the team without losing your place.</p>
        </div>
        <div class="drawer-body">
            <button class="drawer-search-card search-toggle" type="button" aria-label="Search the site">
                <span class="drawer-search-icon"><span class="ms-icon" aria-hidden="true">search</span></span>
                <span class="drawer-search-copy">
                    <span class="drawer-search-label">Search the site</span>
                    <span class="drawer-search-text">Services, updates, and insights</span>
                </span>
                <span class="drawer-search-arrow ms-icon" aria-hidden="true">arrow_forward</span>
            </button>
            <nav class="drawer-nav" aria-label="Mobile navigation"></nav>
        </div>
        <div class="drawer-cta">
            <a class="btn" href="contact.php#inquiry-form">Book a Consultation</a>
            <a class="drawer-contact-link" href="mailto:jm@jsaindia.com">jm@jsaindia.com</a>
        </div>
    </aside>
</header>
<main>
