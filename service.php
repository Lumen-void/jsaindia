<?php
require_once __DIR__ . '/inc/data.php';
$slug = $_GET['slug'] ?? '';
$service = $slug ? get_service_by_slug($slug) : null;

if (!$service) {
    $pageTitle = 'Service not found | Japneet S & Associates';
    $metaDescription = 'Requested service could not be found.';
    $metaRobots = 'noindex,follow';
    http_response_code(404);
    require __DIR__ . '/inc/header.php';
    ?>
    <section class="section">
        <div class="container">
            <h1>Service not found</h1>
            <p class="subhead">The service you are looking for is not available. Please explore our services or contact us.</p>
            <div class="cta-row">
                <a class="btn" href="services.php">View Services</a>
                <a class="btn ghost" href="contact.php">Contact us</a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

$pageTitle = $service['meta_title'] ?? ($service['title'] . ' | Services in Gurugram | Japneet S & Associates');
$metaDescription = $service['meta_description'] ?? ($service['short_intro'] ?? '');
$serviceDesc = trim((string)($service['short_intro'] ?? $service['hero_line'] ?? ''));
$faqs = array_values(array_filter($service['faqs'] ?? [], function ($faq) {
    return is_array($faq) && !empty($faq['question']) && !empty($faq['answer']);
}));
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://www.jsaindia.com/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => 'https://www.jsaindia.com/services.php'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => ($service['title'] ?? ''), 'item' => 'https://www.jsaindia.com/service.php?slug=' . urlencode($service['slug'] ?? '')],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => ($service['title'] ?? ''),
        'url' => 'https://www.jsaindia.com/service.php?slug=' . urlencode($service['slug'] ?? ''),
        'provider' => ['@type' => 'Organization', 'name' => 'Japneet S & Associates'],
        'description' => ($service['short_intro'] ?? ''),
        'areaServed' => [
            ['@type' => 'City', 'name' => 'Gurugram'],
            ['@type' => 'Country', 'name' => 'India'],
        ],
    ],
];
if (!empty($faqs)) {
    $schemaExtra[] = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(function (array $faq): array {
            return [
                '@type' => 'Question',
                'name' => (string)($faq['question'] ?? ''),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => (string)($faq['answer'] ?? ''),
                ],
            ];
        }, $faqs),
    ];
}
require __DIR__ . '/inc/header.php';

$processSteps = array_values(array_filter($service['process_steps'] ?? [], fn($step) => trim((string)$step) !== ''));
$whyChoose = !empty($service['why_choose_us']) ? $service['why_choose_us'] : default_why_choose_us();
$showProcess = !empty($processSteps);
$showFaqs = !empty($faqs);
$inquiryErrors = [];
$inquirySuccess = process_inquiry('Service: ' . $service['title'], $inquiryErrors);
?>

<section class="hero">
    <div class="container two-col">
        <div class="reveal">
            <nav class="breadcrumbs reveal" data-reveal="zoom" aria-label="Breadcrumb">
                <a href="index.php">Home</a>
                <span class="crumb-sep">/</span>
                <a href="services.php">Services</a>
                <span class="crumb-sep">/</span>
                <span aria-current="page"><?php echo h($service['title']); ?></span>
            </nav>
            <div class="pill reveal"><?php echo h($service['title']); ?></div>
            <h1 class="reveal"><?php echo h($service['hero_line'] ?? $service['title']); ?></h1>
            <p class="subhead reveal"><?php echo h($service['short_intro'] ?? ''); ?></p>
            <div class="cta-row reveal" data-reveal="zoom">
                <button class="service-book-btn large" type="button" data-book-service data-service="<?php echo h($service['title']); ?>" data-service-desc="<?php echo h($serviceDesc); ?>" data-service-slug="<?php echo h($service['slug'] ?? ''); ?>">Request to Book</button>
                <a class="btn" href="#service-inquiry">Talk to us</a>
                <a class="btn ghost" href="services.php">Back to services</a>
            </div>
        </div>
        <div class="card reveal" data-reveal="right" data-tilt>
            <h3>Fast facts</h3>
            <ul class="list-bullets">
                <li><span class="marker">&bull;</span>Structured checklists and templates</li>
                <li><span class="marker">&bull;</span>Maker-checker reviews before filings</li>
                <li><span class="marker">&bull;</span>Clear timelines and ownership</li>
                <li><span class="marker">&bull;</span>Secure data handling</li>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="container two-col">
        <div class="reveal" data-reveal="left">
            <h2>What we cover</h2>
            <ul class="list-bullets">
                <?php foreach ($service['what_we_cover'] ?? [] as $item): ?>
                    <li><span class="marker">&bull;</span><?php echo h($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="reveal" data-reveal="right">
            <h2>Who it is for</h2>
            <ul class="list-bullets">
                <?php foreach ($service['who_its_for'] ?? [] as $item): ?>
                    <li><span class="marker">&bull;</span><?php echo h($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<?php if ($showProcess): ?>
    <section class="section">
        <div class="container">
            <h2 class="reveal">How we work</h2>
            <div class="process-steps" data-stagger="80">
                <?php foreach ($processSteps as $i => $step): ?>
                    <div class="process-step reveal">
                        <div><span class="badge"><?php echo $i + 1; ?></span><strong><?php echo h($step); ?></strong></div>
                        <p class="muted">Structured execution with updates at each milestone.</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section">
    <div class="container two-col">
        <div class="reveal" data-reveal="left">
            <h2>Why choose us for this</h2>
            <ul class="list-bullets">
                <?php foreach ($whyChoose as $item): ?>
                    <li><span class="marker">&bull;</span><?php echo h($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($showFaqs): ?>
            <div class="reveal" data-reveal="right">
                <h2>FAQs</h2>
                <div class="faq" data-stagger="80">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="faq-item reveal">
                            <div class="faq-question">
                                <span><?php echo h($faq['question']); ?></span>
                                <span class="plus"></span>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-inner"><?php echo h($faq['answer']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section" id="service-inquiry">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Discuss <?php echo h($service['title']); ?></h3>
                <p class="muted">Share your requirements and we will respond with the approach, timelines, and owners.</p>
                <div class="chip">Email: <a href="mailto:jm@jsaindia.com" style="color:#fff;">jm@jsaindia.com</a></div>
                <div class="chip" style="margin-top:8px;">Office: MGF Metropolis Mall, MG Road, Gurgaon</div>
            </div>
            <div>
                <?php if ($inquirySuccess): ?>
                    <div class="alert"><?php echo h($inquirySuccess); ?></div>
                <?php endif; ?>
                <?php if (!empty($inquiryErrors)): ?>
                    <div class="alert error"><?php echo h(implode(' ', $inquiryErrors)); ?></div>
                <?php endif; ?>
                <form method="post" data-ajax="inquiry">
                    <input type="hidden" name="form_type" value="inquiry">
                    <input type="hidden" name="page_source" value="<?php echo h('Service: ' . $service['title']); ?>">
                    <input type="text" name="company" value="" autocomplete="off" tabindex="-1" aria-hidden="true" class="honeypot">
                    <div class="form-row">
                        <input type="text" name="name" placeholder="Name" autocomplete="name" required>
                        <input type="email" name="email" placeholder="Work email" autocomplete="email" required>
                    </div>
                    <div class="form-row">
                        <input type="text" name="phone" placeholder="Phone" autocomplete="tel" required>
                        <input type="text" name="selected_service" value="<?php echo h($service['title']); ?>" placeholder="Service" autocomplete="off" readonly>
                    </div>
                    <select name="preferred_slot" autocomplete="off">
                        <option value="">Preferred slot (optional)</option>
                        <option value="Weekday morning">Weekday morning</option>
                        <option value="Weekday afternoon">Weekday afternoon</option>
                        <option value="Weekday evening">Weekday evening</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                    <textarea name="message" placeholder="What do you need help with?" autocomplete="off" required></textarea>
                    <button type="submit" class="btn">Submit inquiry</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
