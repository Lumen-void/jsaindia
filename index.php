<?php
$pageTitle = 'Japneet S & Associates | Chartered Accountants in Gurugram';
$metaDescription = 'Chartered Accountants in Gurugram for accounting, GST, income tax, payroll, ROC compliance, and Virtual CFO support.';
require_once __DIR__ . '/inc/data.php';
$services = get_services();
$serviceCount = count($services);
$team = get_team();
$teamCount = count($team);
$caseStudies = get_case_studies();
$caseStudyCount = count($caseStudies);
$resources = array_values(array_filter(get_site_resources(), fn($row) => ($row['status'] ?? 'published') === 'published'));
$resourceCount = count($resources);
$blogs = array_slice(get_published_blogs(), 0, 3);

$serviceImages = [
    'accounting-services' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
    'tax-consultancy' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1200&q=80',
    'secretarial-compliance' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1200&q=80',
    'financial-consultation' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
    'payroll-services' => 'https://images.unsplash.com/photo-1709715357520-5e1047a2b691?auto=format&fit=crop&w=1200&q=80',
    'virtual-cfo-services' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
    'business-support-services' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80',
];
$serviceIcons = [
    'accounting-services' => 'account_balance',
    'tax-consultancy' => 'calculate',
    'secretarial-compliance' => 'gavel',
    'financial-consultation' => 'query_stats',
    'payroll-services' => 'payments',
    'virtual-cfo-services' => 'analytics',
    'business-support-services' => 'support_agent',
];

$featureSlugs = ['accounting-services', 'tax-consultancy', 'secretarial-compliance', 'payroll-services'];
$featureServices = [];
foreach ($featureSlugs as $slug) {
    $service = get_service_by_slug($slug);
    if ($service) {
        $featureServices[] = $service;
    }
}
if (count($featureServices) < 3) {
    $featureServices = array_slice($services, 0, 4);
}

$schemaBaseUrl = rtrim((string)(getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com'), '/');
$metaImage = $schemaBaseUrl . '/imagesandlogo/jsalogo2.jpeg';
$serviceCatalogEntries = [];
foreach ($services as $service) {
    if (!is_array($service)) {
        continue;
    }
    $title = trim((string)($service['title'] ?? ''));
    $slug = trim((string)($service['slug'] ?? ''));
    if ($title === '' || $slug === '') {
        continue;
    }
    $serviceCatalogEntries[] = [
        '@type' => 'Offer',
        'itemOffered' => [
            '@type' => 'Service',
            'name' => $title,
            'url' => $schemaBaseUrl . '/service.php?slug=' . rawurlencode($slug),
        ],
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => ['AccountingService', 'ProfessionalService'],
        '@id' => $schemaBaseUrl . '#accounting-service',
        'name' => 'Japneet S & Associates',
        'url' => $schemaBaseUrl . '/',
        'image' => $metaImage,
        'logo' => $metaImage,
        'email' => 'jm@jsaindia.com',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'MGF Metropolis Mall, MG Road',
            'addressLocality' => 'Gurugram',
            'addressRegion' => 'Haryana',
            'addressCountry' => 'IN',
        ],
        'areaServed' => [
            ['@type' => 'City', 'name' => 'Gurugram'],
            ['@type' => 'AdministrativeArea', 'name' => 'Haryana'],
            ['@type' => 'Country', 'name' => 'India'],
        ],
        'knowsAbout' => [
            'Accounting services',
            'GST compliance',
            'Income tax consultancy',
            'ROC compliance',
            'Payroll services',
            'Virtual CFO services',
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'Accounting, tax, payroll, compliance, and advisory services',
            'itemListElement' => $serviceCatalogEntries,
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $schemaBaseUrl . '#homepage',
        'name' => $pageTitle,
        'url' => $schemaBaseUrl . '/',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $schemaBaseUrl . '#website'],
        'about' => ['@id' => $schemaBaseUrl . '#accounting-service'],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => $metaImage,
        ],
    ],
];

$impactMetrics = [
    [
        'value' => '0 missed',
        'label' => 'GST filings',
        'desc' => '12 months of coverage',
        'tone' => 'cyan',
    ],
    [
        'value' => 'INR 42L',
        'label' => 'Credits recovered',
        'desc' => 'Retail compliance case',
        'tone' => 'emerald',
    ],
    [
        'value' => '6 days',
        'label' => 'Monthly close cycle',
        'desc' => 'SaaS reporting cadence',
        'tone' => 'purple',
    ],
    [
        'value' => '2 meetings',
        'label' => 'Board deck adoption',
        'desc' => 'Investor readiness',
        'tone' => 'amber',
    ],
    [
        'value' => '0 errors',
        'label' => 'Payroll cycles',
        'desc' => '3 consecutive quarters',
        'tone' => 'rose',
    ],
    [
        'value' => '60%',
        'label' => 'Query time cut',
        'desc' => 'Employee support desk',
        'tone' => 'indigo',
    ],
];
$inquiryErrors = [];
$inquirySuccess = process_inquiry('Home', $inquiryErrors);
require __DIR__ . '/inc/header.php';
?>

<div class="home-modern">

    <section class="hero-section" id="hero">
        <canvas class="globe-canvas" id="globeCanvas" aria-hidden="true"></canvas>
        <div class="hero-grid-bg" aria-hidden="true"></div>
        <div class="hero-overlay" aria-hidden="true"></div>
        <div class="hero-content container">
            <div class="hero-text">
                <div class="badge">Japneet S & Associates</div>
                <h1 class="hero-title">
                    <span class="title-line">Navigate</span>
                    <span class="title-line gradient-text">Compliance</span>
                    <span class="title-line">Confidently</span>
                </h1>
                <p class="hero-description">
                    Accounting, tax, payroll, ROC, and Virtual CFO support delivered by Gurugram-based Chartered Accountants with maker-checker controls and audit-ready documentation.
                </p>
                <div class="hero-buttons">
                    <a class="btn btn-primary btn-large" href="contact.php#inquiry-form">Start a conversation <span class="arrow">→</span></a>
                    <a class="btn btn-ghost-dark btn-large" href="#services-grid">View services</a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-value">10+</div>
                        <div class="stat-label">Years of partner experience</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo h($serviceCount); ?></div>
                        <div class="stat-label">Core services handled end-to-end</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Gurugram</div>
                        <div class="stat-label">On-site + remote support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section compliance-v2">
        <div class="container compliance-grid-v2">
            <div>
                <div class="badge badge-green">AUTOMATION + CARE</div>
                <h2 class="section-title-white">Compliance made effortless</h2>
                <p class="section-description-white">Bookkeeping, GST/TDS/ITR filings, ROC compliance, payroll, and Virtual CFO services delivered with maker-checker controls.</p>
                <div class="feature-list">
                    <div class="feature-item"><span class="check-icon">✓</span><span>Accounting &amp; reconciliations with MIS</span></div>
                    <div class="feature-item"><span class="check-icon">✓</span><span>GST, TDS, Income Tax filings &amp; notices</span></div>
                    <div class="feature-item"><span class="check-icon">✓</span><span>ROC/secretarial compliance for companies &amp; LLPs</span></div>
                    <div class="feature-item"><span class="check-icon">✓</span><span>Payroll, PF/ESI/PT, and Form 16</span></div>
                    <div class="feature-item"><span class="check-icon">✓</span><span>Virtual CFO, budgeting, and reporting</span></div>
                </div>
                <a class="btn btn-primary btn-large" href="services.php">Explore services <span class="arrow">→</span></a>
            </div>
            <div>
                <div class="dashboard-image">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200" alt="Compliance dashboard">
                    <div class="image-overlay"></div>
                    <div class="floating-badge badge-secure"><span class="ms-icon" aria-hidden="true">verified_user</span> Maker-checker reviews</div>
                    <div class="floating-badge badge-live"><span class="pulse-dot"></span> Audit-ready documentation</div>
                </div>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon"><span class="ms-icon" aria-hidden="true">grid_view</span></div>
                        <div class="metric-value"><?php echo h((string)$serviceCount); ?>+</div>
                        <div class="metric-label">Service lines covered</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon"><span class="ms-icon" aria-hidden="true">supervisor_account</span></div>
                        <div class="metric-value"><?php echo h((string)$teamCount); ?>+</div>
                        <div class="metric-label">Partner-led reviews</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon"><span class="ms-icon" aria-hidden="true">insights</span></div>
                        <div class="metric-value"><?php echo h((string)$caseStudyCount); ?></div>
                        <div class="metric-label">Case studies delivered</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon"><span class="ms-icon" aria-hidden="true">folder_shared</span></div>
                        <div class="metric-value"><?php echo h((string)$resourceCount); ?></div>
                        <div class="metric-label">Compliance tools shared</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="services-section" id="services-grid">
        <div class="container">
            <div class="section-header">
                <div class="badge">SERVICES</div>
                <h2 class="section-title">Everything you need</h2>
                <p class="section-subtitle">Accounting, tax, payroll, secretarial, Virtual CFO, and business support in one place.</p>
            </div>
            <div class="services-grid" data-stagger="90">
                <?php
                foreach ($services as $service):
                    $slug = $service['slug'] ?? '';
                    $img = $serviceImages[$slug] ?? reset($serviceImages);
                    $icon = $serviceIcons[$slug] ?? 'auto_awesome';
                    $badge = $service['hero_line'] ?? $service['short_intro'] ?? '';
                    $desc = trim((string)($service['short_intro'] ?? $service['hero_line'] ?? ''));
                    ?>
                    <div class="service-card reveal" data-tilt>
                        <div class="service-image">
                            <img src="<?php echo h($img); ?>" alt="<?php echo h($service['title']); ?>">
                            <div class="service-overlay"></div>
                            <div class="service-icon-float"><span class="ms-icon" aria-hidden="true"><?php echo h($icon); ?></span></div>
                        </div>
                        <div class="service-content">
                            <h3 class="service-title"><?php echo h($service['title']); ?></h3>
                            <p class="service-description"><?php echo h($service['short_intro'] ?? ''); ?></p>
                            <?php if ($badge): ?>
                                <div class="service-badge"><?php echo h($badge); ?></div>
                            <?php endif; ?>
                            <div class="service-actions">
                                <a class="service-link" href="service.php?slug=<?php echo urlencode($slug); ?>">Learn more →</a>
                                <button class="service-book-btn" type="button" data-book-service data-service="<?php echo h($service['title']); ?>" data-service-desc="<?php echo h($desc); ?>" data-service-slug="<?php echo h($slug); ?>">Request to Book</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="interactive-features" class="interactive-features-section">
        <div class="container">
            <div class="section-header">
                <div class="badge">CORE DESKS</div>
                <h2 class="section-title-white">Specialist coverage, coordinated delivery</h2>
                <p class="section-description-white">Choose the services you need today and scale as compliance needs grow.</p>
            </div>

            <div class="feature-tabs">
                <?php foreach ($featureServices as $i => $service): ?>
                    <?php
                    $slug = $service['slug'] ?? '';
                    $icon = $serviceIcons[$slug] ?? 'auto_awesome';
                    ?>
                    <button class="tab-button<?php echo $i === 0 ? ' active' : ''; ?>" type="button" data-feature="<?php echo h($slug); ?>">
                        <span class="ms-icon" aria-hidden="true"><?php echo h($icon); ?></span>
                        <?php echo h($service['title']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($featureServices as $i => $service): ?>
                <?php
                $slug = $service['slug'] ?? '';
                $img = $serviceImages[$slug] ?? reset($serviceImages);
                $icon = $serviceIcons[$slug] ?? 'auto_awesome';
                $cover = array_values(array_filter($service['what_we_cover'] ?? [], fn($item) => trim((string)$item) !== ''));
                $who = array_values(array_filter($service['who_its_for'] ?? [], fn($item) => trim((string)$item) !== ''));
                $why = array_values(array_filter($service['why_choose_us'] ?? [], fn($item) => trim((string)$item) !== ''));
                $highlights = array_slice($cover, 0, 4);
                if (empty($highlights)) {
                    $highlights = array_slice($why, 0, 4);
                }
                ?>
                <div class="feature-content<?php echo $i === 0 ? ' active' : ''; ?>" id="feature-<?php echo h($slug); ?>">
                    <div>
                        <div class="feature-image-wrapper">
                            <img src="<?php echo h($img); ?>" alt="<?php echo h($service['title']); ?>">
                            <div class="image-overlay"></div>
                            <div class="feature-icon-badge"><span class="ms-icon" aria-hidden="true"><?php echo h($icon); ?></span></div>
                        </div>
                        <div class="feature-stats-mini">
                            <div class="mini-stat">
                                <div class="mini-stat-value"><?php echo h((string)count($cover)); ?></div>
                                <div class="mini-stat-label">Coverage items</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value"><?php echo h((string)count($who)); ?></div>
                                <div class="mini-stat-label">Client profiles</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-value"><?php echo h((string)count($why)); ?></div>
                                <div class="mini-stat-label">Reasons to choose</div>
                            </div>
                        </div>
                    </div>
                    <div class="feature-details">
                        <h3><?php echo h($service['title']); ?></h3>
                        <p><?php echo h($service['hero_line'] ?: $service['short_intro']); ?></p>
                        <?php if (!empty($highlights)): ?>
                            <div class="feature-highlights">
                                <?php foreach ($highlights as $item): ?>
                                    <div class="highlight-item"><span class="check-icon">✓</span><span><?php echo h($item); ?></span></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a class="btn btn-primary btn-large" href="service.php?slug=<?php echo urlencode($slug); ?>">Explore <?php echo h($service['title']); ?> <span class="arrow">→</span></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="process" class="process-section">
        <div class="container">
            <div class="section-header">
                <div class="badge badge-green">WORKFLOW</div>
                <h2 class="section-title-white">Your compliance workflow, covered</h2>
                <p class="section-description-white">Core accounting, tax, secretarial, and payroll lanes delivered by one partner team.</p>
            </div>

            <div class="process-timeline">
                <div class="timeline-track">
                    <div class="timeline-line"><div class="timeline-progress" id="timelineProgress"></div></div>
                    <?php foreach ($featureServices as $i => $service): ?>
                        <button class="timeline-step<?php echo $i === 0 ? ' active' : ''; ?>" type="button" data-step="<?php echo $i + 1; ?>">
                            <?php echo str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($featureServices as $i => $service): ?>
                <?php
                $slug = $service['slug'] ?? '';
                $img = $serviceImages[$slug] ?? reset($serviceImages);
                $icon = $serviceIcons[$slug] ?? 'auto_awesome';
                $highlights = array_slice(array_values(array_filter($service['what_we_cover'] ?? [], fn($item) => trim((string)$item) !== '')), 0, 3);
                if (empty($highlights)) {
                    $highlights = array_slice(array_values(array_filter($service['why_choose_us'] ?? [], fn($item) => trim((string)$item) !== '')), 0, 3);
                }
                ?>
                <div class="process-content<?php echo $i === 0 ? ' active' : ''; ?>" id="step-<?php echo $i + 1; ?>">
                    <div class="process-image">
                        <img src="<?php echo h($img); ?>" alt="<?php echo h($service['title']); ?>">
                        <div class="image-overlay"></div>
                        <div class="process-number-badge"><?php echo str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="process-details">
                        <div class="process-icon"><span class="ms-icon" aria-hidden="true"><?php echo h($icon); ?></span></div>
                        <h3><?php echo h($service['title']); ?></h3>
                        <p><?php echo h($service['short_intro'] ?? ''); ?></p>
                        <?php if (!empty($highlights)): ?>
                            <div class="feature-highlights">
                                <?php foreach ($highlights as $item): ?>
                                    <div class="highlight-item"><span class="check-icon">✓</span><span><?php echo h($item); ?></span></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a class="btn btn-primary btn-large" href="service.php?slug=<?php echo urlencode($slug); ?>">Explore <?php echo h($service['title']); ?> <span class="arrow">→</span></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="insights" class="insights-section">
        <div class="container">
            <div class="section-header">
                <div class="badge">INSIGHTS</div>
                <h2 class="section-title-white">Latest insights</h2>
                <p class="section-description-white">Practical notes on accounting, tax, payroll, and compliance.</p>
            </div>

            <div class="insights-grid">
                <?php foreach ($blogs as $blog): ?>
                    <?php $blogImage = normalize_image_url((string)($blog['featured_image'] ?? ''), placeholder_image('featured')); ?>
                    <div class="insight-card">
                        <img src="<?php echo h($blogImage); ?>" alt="<?php echo h($blog['title']); ?>" class="insight-image">
                        <div class="insight-content">
                            <span class="insight-category"><?php echo h($blog['category']); ?></span>
                            <h3 class="insight-title"><?php echo h($blog['title']); ?></h3>
                            <p class="insight-excerpt"><?php echo h($blog['excerpt']); ?></p>
                            <div class="insight-meta">
                                <span><?php echo h((string)estimate_reading_time_minutes($blog['content'] ?? '')); ?> min read</span>
                                <span>•</span>
                                <span><?php echo h(format_date($blog['date'])); ?></span>
                            </div>
                            <a href="post.php?slug=<?php echo urlencode($blog['slug']); ?>" class="read-more">Read article →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cta-section" id="contact">
        <div class="container">
            <div class="cta-grid">
                <div class="cta-content">
                    <h2 class="section-title-white">Ready to get<br><span class="gradient-text">started?</span></h2>
                    <p class="section-description-white">Join businesses that trust Japneet S & Associates with their accounting, tax, payroll, and compliance.</p>
                    <div class="contact-info">
                        <div class="contact-item"><div class="contact-icon"><span class="ms-icon" aria-hidden="true">mail</span></div><span>jm@jsaindia.com</span></div>
                        <div class="contact-item"><div class="contact-icon"><span class="ms-icon" aria-hidden="true">location_on</span></div><span>MGF Metropolis Mall, MG Road, Gurgaon</span></div>
                    </div>
                </div>
                <div class="cta-form-wrapper">
                    <h3 class="form-title">Get in touch</h3>
                    <p class="form-subtitle">We'll respond within one business day</p>
                    <?php if ($inquirySuccess): ?>
                        <div class="alert"><?php echo h($inquirySuccess); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($inquiryErrors)): ?>
                        <div class="alert error"><?php echo h(implode(' ', $inquiryErrors)); ?></div>
                    <?php endif; ?>
                    <form id="contactForm" method="post" data-ajax="inquiry">
                        <input type="hidden" name="form_type" value="inquiry">
                        <input type="hidden" name="page_source" value="Home">
                        <input type="text" name="company" value="" autocomplete="off" tabindex="-1" aria-hidden="true" class="honeypot">
                        <input type="text" name="name" placeholder="Full name" class="form-input" autocomplete="name" required>
                        <input type="email" name="email" placeholder="Work email" class="form-input" autocomplete="email" required>
                        <input type="text" name="selected_service" placeholder="Service of interest" class="form-input" autocomplete="off">
                        <textarea rows="4" name="message" placeholder="How can we help?" class="form-input" autocomplete="off" required></textarea>
                        <button type="submit" class="btn btn-primary btn-large btn-full">
                            Send Message
                            <span class="arrow">→</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
