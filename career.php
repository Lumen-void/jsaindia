<?php
require_once __DIR__ . '/inc/data.php';
$slug = $_GET['slug'] ?? '';
$career = $slug ? get_career_by_slug($slug) : null;

if (!$career) {
    $pageTitle = 'Role not found | Japneet S & Associates';
    $metaDescription = 'Requested role could not be found.';
    $metaRobots = 'noindex,follow';
    http_response_code(404);
    require __DIR__ . '/inc/header.php';
    ?>
    <section class="section">
        <div class="container">
            <h1>Role not found</h1>
            <p class="subhead">This role is unavailable. View open positions below.</p>
            <a class="btn" href="careers.php">Back to careers</a>
        </div>
    </section>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

$pageTitle = $career['title'] . ' | Careers in Gurugram | Japneet S & Associates';
$metaDescription = trim((string)($career['overview'] ?? '')) ?: 'Open role at Japneet S & Associates in Gurugram.';
$inquiryErrors = [];
$inquirySuccess = process_inquiry('Career: ' . $career['title'], $inquiryErrors);
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://www.jsaindia.com/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Careers', 'item' => 'https://www.jsaindia.com/careers.php'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => ($career['title'] ?? ''), 'item' => 'https://www.jsaindia.com/career.php?slug=' . urlencode($career['slug'] ?? '')],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => ($career['title'] ?? ''),
        'description' => implode("\n", array_filter(array_merge(
            [(string)($career['overview'] ?? '')],
            array_map(fn($item) => 'Responsibility: ' . (string)$item, $career['responsibilities'] ?? []),
            array_map(fn($item) => 'Requirement: ' . (string)$item, $career['requirements'] ?? [])
        ))),
        'employmentType' => ($career['type'] ?? ''),
        'jobLocation' => [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Gurugram',
                'addressRegion' => 'Haryana',
                'addressCountry' => 'IN',
            ],
        ],
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name' => 'Japneet S & Associates',
            'sameAs' => 'https://www.jsaindia.com/',
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Careers</div>
        <nav class="breadcrumbs reveal" data-reveal="zoom" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="crumb-sep">/</span>
            <a href="careers.php">Careers</a>
            <span class="crumb-sep">/</span>
            <span aria-current="page"><?php echo h($career['title']); ?></span>
        </nav>
        <h1 class="reveal"><?php echo h($career['title']); ?></h1>
        <div class="hero-meta reveal" data-reveal="zoom">
            <span><?php echo h($career['location']); ?></span>
            <span><?php echo h($career['type']); ?></span>
        </div>
        <p class="subhead reveal"><?php echo h($career['overview']); ?></p>
    </div>
</section>

<section class="section">
    <div class="container two-col">
        <div class="reveal" data-reveal="left">
            <h2>Responsibilities</h2>
            <ul class="list-bullets">
                <?php foreach ($career['responsibilities'] as $item): ?>
                    <li><span class="marker">&bull;</span><?php echo h($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="reveal" data-reveal="right">
            <h2>Requirements</h2>
            <ul class="list-bullets">
                <?php foreach ($career['requirements'] as $item): ?>
                    <li><span class="marker">&bull;</span><?php echo h($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section" id="apply">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Apply for <?php echo h($career['title']); ?></h3>
                <p class="muted">Share your details and we will connect with next steps.</p>
                <p class="chip">You can also email your profile to <a style="color:#fff;" href="mailto:jm@jsaindia.com">jm@jsaindia.com</a></p>
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
                    <input type="hidden" name="page_source" value="<?php echo h('Career: ' . $career['title']); ?>">
                    <input type="text" name="company" value="" autocomplete="off" tabindex="-1" aria-hidden="true" class="honeypot">
                    <div class="form-row">
                        <input type="text" name="name" placeholder="Name" autocomplete="name" required>
                        <input type="email" name="email" placeholder="Email" autocomplete="email" required>
                    </div>
                    <div class="form-row">
                        <input type="text" name="phone" placeholder="Phone" autocomplete="tel" required>
                        <input type="text" name="selected_service" value="<?php echo h($career['title']); ?>" placeholder="Role" autocomplete="off" readonly>
                    </div>
                    <select name="preferred_slot" autocomplete="off">
                        <option value="">Preferred interview slot (optional)</option>
                        <option value="Weekday morning">Weekday morning</option>
                        <option value="Weekday afternoon">Weekday afternoon</option>
                        <option value="Weekday evening">Weekday evening</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                    <textarea name="message" placeholder="Short note about your fit (paste profile link if any)" autocomplete="off" required></textarea>
                    <button type="submit" class="btn">Submit application</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
