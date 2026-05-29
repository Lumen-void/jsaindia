<?php
require_once __DIR__ . '/inc/data.php';

$slug = trim((string)($profileSlug ?? ($_GET['slug'] ?? '')));
$scriptName = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptSlug = '';
if ($scriptName !== '' && strtolower($scriptName) !== 'partner.php') {
    $scriptSlug = preg_replace('/\\.php$/i', '', $scriptName);
}

if ($slug === '') {
    if ($scriptSlug !== '') {
        $slug = $scriptSlug;
    } else {
        header('Location: team.php', true, 302);
        exit;
    }
}

$member = get_team_member_by_slug((string)$slug);

if (!$member) {
    if ($scriptSlug !== '' && $scriptSlug !== $slug) {
        $member = get_team_member_by_slug((string)$scriptSlug);
    }
}

if (!$member) {
    $pageTitle = 'Partner not found | Japneet S & Associates';
    $metaDescription = 'Requested partner profile could not be found.';
    $metaRobots = 'noindex,follow';
    http_response_code(404);
    require __DIR__ . '/inc/header.php';
    ?>
    <section class="section">
        <div class="container">
            <h1>Partner not found</h1>
            <p class="subhead">The profile you are looking for is not available. Please return to the partners page.</p>
            <div class="cta-row">
                <a class="btn" href="team.php">View partners</a>
                <a class="btn ghost" href="contact.php">Contact us</a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

$name = (string)($member['name'] ?? 'Partner');
$role = trim((string)($member['role'] ?? ''));
$profileUrl = (string)($member['profile_url'] ?? '');
if ($profileUrl === '' && !empty($member['slug'])) {
    $profileUrl = 'partner.php?slug=' . rawurlencode((string)$member['slug']);
}

$pageTitle = $name . ' | Japneet S & Associates';
$metaDescription = (string)($member['bio'] ?? 'Partner profile at Japneet S & Associates.');
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$memberPhoto = normalize_image_url((string)($member['photo'] ?? ''), placeholder_image('team'));
$metaImage = str_starts_with($memberPhoto, 'http') ? $memberPhoto : rtrim($baseUrl, '/') . '/' . ltrim($memberPhoto, '/');
$metaImageAlt = $name;
$ogType = 'profile';
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Team', 'item' => $baseUrl . '/team.php'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $baseUrl . '/' . ltrim($profileUrl, '/')],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        '@id' => $baseUrl . '/' . ltrim($profileUrl, '/'),
        'name' => $name,
        'jobTitle' => $role,
        'description' => $metaDescription,
        'url' => $baseUrl . '/' . ltrim($profileUrl, '/'),
        'worksFor' => ['@type' => 'Organization', 'name' => 'Japneet S & Associates', 'url' => $baseUrl],
        'image' => $metaImage,
    ],
];
require __DIR__ . '/inc/header.php';

$profileParagraphs = array_values(array_filter($member['profile_paragraphs'] ?? [], function ($item) {
    return trim((string)$item) !== '';
}));
$bio = trim((string)($member['bio'] ?? ''));
$longBio = trim((string)($member['long_bio'] ?? ''));
if (empty($profileParagraphs)) {
    if ($bio !== '') {
        $profileParagraphs[] = $bio;
    }
    if ($longBio !== '') {
        $profileParagraphs[] = $longBio;
    }
}
$backgroundExperience = array_values(array_filter($member['background_experience'] ?? [], function ($item) {
    return trim((string)$item) !== '';
}));
$coreSkills = array_values(array_filter($member['core_skills'] ?? [], function ($item) {
    return trim((string)$item) !== '';
}));
$roleLine = $role !== '' ? $role . ' at Japneet S & Associates.' : 'Partner at Japneet S & Associates.';
?>

<section class="hero partner-hero">
    <div class="container partner-hero-shell">
        <div class="card reveal partner-photo-card" data-reveal="left" data-tilt>
            <img class="partner-photo-image" loading="lazy" decoding="async" src="<?php echo h($memberPhoto); ?>" alt="<?php echo h($name); ?>">
        </div>
        <div class="partner-main-content">
            <div class="reveal partner-hero-head" data-reveal="right">
                <nav class="breadcrumbs reveal" data-reveal="zoom" aria-label="Breadcrumb">
                    <a href="index.php">Home</a>
                    <span class="crumb-sep">/</span>
                    <a href="team.php">Team</a>
                    <span class="crumb-sep">/</span>
                    <span aria-current="page"><?php echo h($name); ?></span>
                </nav>
                <div class="pill reveal">Partner</div>
                <h1 class="reveal"><?php echo h($name); ?></h1>
            </div>
            <div class="partner-profile-block partner-profile-panel reveal" data-reveal="left">
                <p class="subhead partner-role-line"><?php echo h($roleLine); ?></p>
                <?php foreach ($profileParagraphs as $paragraph): ?>
                    <p class="muted"><?php echo h((string)$paragraph); ?></p>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($coreSkills)): ?>
                <div class="partner-inline-section reveal" data-reveal="right">
                    <h3>Core Skills &amp; Expertise</h3>
                    <ul class="list-bullets">
                        <?php foreach ($coreSkills as $item): ?>
                            <li><span class="marker">&bull;</span><?php echo h((string)$item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($backgroundExperience)): ?>
                <div class="partner-inline-section reveal" data-reveal="right">
                    <h3>Background &amp; Experience</h3>
                    <ul class="list-bullets">
                        <?php foreach ($backgroundExperience as $item): ?>
                            <li><span class="marker">&bull;</span><?php echo h((string)$item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="cta-row reveal partner-hero-actions" data-reveal="zoom">
                <a class="btn" href="contact.php#inquiry-form">Work with us</a>
                <a class="btn ghost" href="team.php">Back to partners</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Connect with <?php echo h($name); ?></h3>
                <p class="muted">Tell us what you need across audit, assurance, compliance, or advisory and we'll align the right team.</p>
            </div>
            <div>
                <a class="btn" href="contact.php#inquiry-form">Book a consultation</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
