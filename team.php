<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Our Team | Chartered Accountants and Advisors';
$metaDescription = 'Meet the team of Chartered Accountants and advisors at Japneet S & Associates in Gurugram.';
$team = get_team();
if (empty($team)) {
    $team = array_values(array_filter(array_map(function ($member) {
        return is_array($member) ? normalize_team_member($member) : null;
    }, read_json('team.json'))));
}
$featuredSlugs = ['japneet-makkar', 'roohi-chawla'];
$featuredMap = [];
$others = [];
foreach ($team as $member) {
    if (!is_array($member)) continue;
    $slug = (string)($member['slug'] ?? '');
    if ($slug === '' && !empty($member['name'])) {
        $slug = team_slug_from_name((string)$member['name']);
    }
    if (in_array($slug, $featuredSlugs, true)) {
        $featuredMap[$slug] = $member;
        continue;
    }
    $others[] = $member;
}

// Ensure featured profiles exist even if DB data is missing.
foreach (read_json('team.json') as $member) {
    if (!is_array($member)) continue;
    $member = normalize_team_member($member);
    $slug = (string)($member['slug'] ?? '');
    if ($slug !== '' && in_array($slug, $featuredSlugs, true)) {
        if (!isset($featuredMap[$slug])) {
            $featuredMap[$slug] = $member;
        }
    }
}

$featured = [];
foreach ($featuredSlugs as $slug) {
    if (isset($featuredMap[$slug])) {
        $featured[] = $featuredMap[$slug];
    }
}
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$teamItems = [];
foreach ($team as $index => $member) {
    if (!is_array($member)) {
        continue;
    }
    $member = normalize_team_member($member);
    $name = trim((string)($member['name'] ?? ''));
    $profileUrl = trim((string)($member['profile_url'] ?? ''));
    if ($name === '' || $profileUrl === '') {
        continue;
    }
    $teamItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $name,
        'url' => $baseUrl . '/' . ltrim($profileUrl, '/'),
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Team', 'item' => $baseUrl . '/team.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/team.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/team.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $teamItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Team</div>
        <h1 class="reveal">Meet our team.</h1>
        <p class="subhead reveal">Our Chartered Accountants and finance professionals bring practical experience across audit, assurance, tax, financial controls, reporting, and compliance.</p>
        <div class="cta-row reveal" data-reveal="zoom">
            <a class="btn" href="contact.php#inquiry-form">Work with us</a>
            <a class="btn ghost" href="careers.php">Join the team</a>
        </div>
    </div>
</section>

<?php if (!empty($featured)): ?>
<section class="section">
    <div class="container grid grid-2" data-stagger="90">
        <?php foreach ($featured as $member): ?>
            <div class="card reveal featured-team-card" data-tilt>
                <?php
                $profileUrl = (string)($member['profile_url'] ?? '');
                if ($profileUrl === '') {
                    $slug = (string)($member['slug'] ?? '');
                    if ($slug === '' && !empty($member['name'])) {
                        $slug = team_slug_from_name((string)$member['name']);
                    }
                    if ($slug !== '') {
                        $candidate = $slug . '.php';
                        if (file_exists(__DIR__ . '/' . $candidate)) {
                            $profileUrl = $candidate;
                        } else {
                            $profileUrl = 'partner.php?slug=' . rawurlencode($slug);
                        }
                    }
                }
                ?>
                <?php $photo = normalize_image_url((string)($member['photo'] ?? ''), placeholder_image('team')); ?>
                <img class="featured-team-photo" loading="lazy" decoding="async" src="<?php echo h($photo); ?>" alt="<?php echo h($member['name']); ?>">
                <h3><?php echo h($member['name']); ?></h3>
                <p class="muted"><?php echo h($member['role']); ?></p>
                <p><?php echo h($member['bio']); ?></p>
                <?php if ($profileUrl !== ''): ?>
                    <div class="cta-row" style="margin-top:12px;">
                        <a class="btn ghost" href="<?php echo h($profileUrl); ?>">View more</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container grid grid-3" data-stagger="90">
        <?php foreach ($others as $member): ?>
            <div class="card reveal" data-tilt>
                <?php
                $profileUrl = (string)($member['profile_url'] ?? '');
                if ($profileUrl === '') {
                    $slug = (string)($member['slug'] ?? '');
                    if ($slug === '' && !empty($member['name'])) {
                        $slug = team_slug_from_name((string)$member['name']);
                    }
                    if ($slug !== '') {
                        $candidate = $slug . '.php';
                        if (file_exists(__DIR__ . '/' . $candidate)) {
                            $profileUrl = $candidate;
                        } else {
                            $profileUrl = 'partner.php?slug=' . rawurlencode($slug);
                        }
                    }
                }
                ?>
                <?php $photo = normalize_image_url((string)($member['photo'] ?? ''), placeholder_image('team')); ?>
                <img loading="lazy" decoding="async" src="<?php echo h($photo); ?>" alt="<?php echo h($member['name']); ?>" style="border-radius:12px; margin-bottom:12px;">
                <h3><?php echo h($member['name']); ?></h3>
                <p class="muted"><?php echo h($member['role']); ?></p>
                <p><?php echo h($member['bio']); ?></p>
                <?php if ($profileUrl !== ''): ?>
                    <div class="cta-row" style="margin-top:12px;">
                        <a class="btn ghost" href="<?php echo h($profileUrl); ?>">View more</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Need leadership across finance functions?</h3>
                <p class="muted">Our senior team stays involved throughout the engagement.</p>
            </div>
            <div>
                <a class="btn" href="contact.php">Book a consultation</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
