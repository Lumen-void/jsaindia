<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'About Japneet S & Associates | Chartered Accountants in Gurugram';
$metaDescription = 'Learn about Japneet S & Associates, a Gurugram Chartered Accountancy firm supporting accounting, tax, payroll, ROC compliance, and advisory needs.';
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$team = get_team();
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'About', 'item' => $baseUrl . '/about.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'AboutPage',
        '@id' => $baseUrl . '/about.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/about.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'about' => ['@id' => $baseUrl . '#organization'],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">About</div>
        <h1 class="reveal">Your partner for growth.</h1>
        <p class="subhead reveal">Our multidisciplinary team of Chartered Accountants, legal experts, finance professionals, and seasoned consultants delivers excellence with integrity.</p>
    </div>
</section>

<section class="section">
    <div class="container two-col">
        <div class="reveal">
            <h2>Our story</h2>
            <p class="subhead">Japneet S & Associates (JSA) is a dynamic business and management consultancy firm based in Gurugram, Haryana.</p>
            <p class="muted">We are a passionate team committed to delivering excellence with integrity. Our ethos revolves around transforming challenges into opportunities by applying forward-thinking, strategic solutions to help clients achieve their business goals.</p>
            <p class="muted">With a diverse pool of highly skilled professionals and extensive service capabilities, we are equipped to address the evolving needs of corporate and non-corporate businesses across industries.</p>
        </div>
        <div class="card reveal" data-reveal="right" data-tilt>
            <h3>What drives us</h3>
            <ul class="list-bullets">
                <li><span class="marker">&bull;</span>Chartered Accountants, legal experts, finance professionals, and industry consultants working together</li>
                <li><span class="marker">&bull;</span>Forward-thinking, strategic solutions that turn challenges into opportunities</li>
                <li><span class="marker">&bull;</span>Proactive client service built on continuous learning and integrity</li>
            </ul>
        </div>
    </div>
</section>

<section class="section" id="team">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow reveal">Meet Our Team</p>
                <h2 class="reveal">People behind the practice.</h2>
            </div>
            <p class="muted reveal">Chartered Accountants and finance professionals with experience across audit, assurance, tax, financial controls, reporting, and compliance.</p>
        </div>
        <div class="grid grid-2" data-stagger="90">
            <?php foreach ($team as $member): ?>
                <?php
                if (!is_array($member)) continue;
                $member = normalize_team_member($member);
                $photo = normalize_image_url((string)($member['photo'] ?? ''), placeholder_image('team'));
                ?>
                <article class="card reveal about-team-card" data-tilt>
                    <img loading="lazy" decoding="async" src="<?php echo h($photo); ?>" alt="<?php echo h($member['name']); ?>">
                    <div class="about-team-copy">
                        <h3><?php echo h($member['name']); ?></h3>
                        <p class="muted"><?php echo h($member['role']); ?></p>
                        <p><?php echo h($member['bio']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="reveal">Our commitment</h2>
        <div class="grid grid-3" data-stagger="90">
            <?php foreach ([
                ['Excellence with integrity', 'We hold ourselves to the highest standards while keeping every engagement transparent.'],
                ['Proactive partnership', 'You get timely updates, structured documentation, and a team that is easy to reach.'],
                ['Prepared for what\'s next', 'Continuous learning keeps us ready for evolving compliance and business needs.']
            ] as $value): ?>
                <div class="card reveal" data-tilt>
                    <h3><?php echo h($value[0]); ?></h3>
                    <p class="muted"><?php echo h($value[1]); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
