<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Insights | Accounting, Tax, Payroll, and Compliance Articles';
$metaDescription = 'Read practical insights from Japneet S & Associates on accounting, GST, tax, payroll, compliance, and advisory topics.';
$blogs = get_published_blogs();
$categories = array_values(array_unique(array_map(fn($b) => $b['category'], $blogs)));
$linkedinPostsUrl = getenv('JSA_LINKEDIN_POSTS_URL') ?: 'https://www.linkedin.com/search/results/content/?keywords=' . rawurlencode('Japneet S & Associates');
$linkedinPosts = [
    [
        'topic' => 'GST Compliance',
        'date' => 'Latest LinkedIn note',
        'title' => 'What finance teams should review before filing GST returns',
        'body' => 'A practical reminder to reconcile outward supplies, input tax credit, e-invoices, and vendor ledgers before filing so avoidable notices do not reach the desk later.',
        'tags' => ['GST', 'Input Credit', 'Compliance'],
    ],
    [
        'topic' => 'Financial Controls',
        'date' => 'From LinkedIn',
        'title' => 'Why maker-checker review still matters in growing companies',
        'body' => 'Clean close cycles come from simple controls: ownership, documentation, review trails, and exception tracking that management can actually use.',
        'tags' => ['Controls', 'Audit Ready', 'Reporting'],
    ],
    [
        'topic' => 'Payroll & TDS',
        'date' => 'Professional update',
        'title' => 'Payroll compliance is easier when inputs are locked early',
        'body' => 'Salary changes, reimbursements, declarations, and statutory deductions need a clear monthly calendar so payroll and Form 16 work stay predictable.',
        'tags' => ['Payroll', 'TDS', 'Process'],
    ],
];
$linkedinWidgetType = strtolower(trim((string)(getenv('JSA_LINKEDIN_WIDGET_TYPE') ?: 'profile-posts')));
$linkedinWidgetId = trim((string)(getenv('JSA_LINKEDIN_WIDGET_ID') ?: ''));
$linkedinWidgetOptions = [
    'profile-posts' => [
        'class' => 'sk-ww-linkedin-profile-post',
        'script' => 'https://widgets.sociablekit.com/linkedin-profile-posts/new/widget.js',
    ],
    'profile-post' => [
        'class' => 'sk-ww-linkedin-profile-post',
        'script' => 'https://widgets.sociablekit.com/linkedin-profile-posts/new/widget.js',
    ],
    'page-posts' => [
        'class' => 'sk-ww-linkedin-page-post',
        'script' => 'https://widgets.sociablekit.com/linkedin-page-posts/new/widget.js',
    ],
    'page-post' => [
        'class' => 'sk-ww-linkedin-page-post',
        'script' => 'https://widgets.sociablekit.com/linkedin-page-posts/new/widget.js',
    ],
];
$linkedinWidget = $linkedinWidgetOptions[$linkedinWidgetType] ?? $linkedinWidgetOptions['profile-posts'];
$hasLinkedinWidget = $linkedinWidgetId !== '';
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$blogItems = [];
foreach ($blogs as $index => $blog) {
    if (!is_array($blog)) {
        continue;
    }
    $slug = trim((string)($blog['slug'] ?? ''));
    $title = trim((string)($blog['title'] ?? ''));
    if ($slug === '' || $title === '') {
        continue;
    }
    $blogItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'url' => $baseUrl . '/post.php?slug=' . rawurlencode($slug),
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Insights', 'item' => $baseUrl . '/blog.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        '@id' => $baseUrl . '/blog.php#blog',
        'name' => $pageTitle,
        'url' => $baseUrl . '/blog.php',
        'description' => $metaDescription,
        'blogPost' => array_map(fn($item) => ['@id' => $item['url']], $blogItems),
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/blog.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/blog.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $blogItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Insights</div>
        <h1 class="reveal">Latest insights from Japneet S & Associates.</h1>
        <p class="subhead reveal">Short, practical notes across accounting, tax, payroll, compliance, and advisory.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="linkedin-feed-shell reveal" data-reveal="zoom">
            <div class="linkedin-feed-head">
                <div>
                    <div class="eyebrow">LinkedIn Feed</div>
                    <h2>Latest posts from our LinkedIn feed.</h2>
                    <p class="muted">Short-form updates, observations, and practical notes that publish on LinkedIn between full insight articles.</p>
                </div>
                <div class="cta-row">
                    <a class="btn" href="<?php echo h($linkedinPostsUrl); ?>" target="_blank" rel="noopener noreferrer">Open LinkedIn</a>
                </div>
            </div>
            <div class="linkedin-feed-frame" aria-label="LinkedIn feed">
                <?php if ($hasLinkedinWidget): ?>
                    <div class="<?php echo h($linkedinWidget['class']); ?>" data-embed-id="<?php echo h($linkedinWidgetId); ?>" data-ui="new"></div>
                <?php else: ?>
                    <div class="linkedin-post-grid">
                        <?php foreach ($linkedinPosts as $post): ?>
                            <article class="linkedin-post-card">
                                <div class="linkedin-post-top">
                                    <div class="linkedin-avatar" aria-hidden="true">JS</div>
                                    <div>
                                        <div class="linkedin-author">Japneet S &amp; Associates</div>
                                        <div class="linkedin-date"><?php echo h($post['date']); ?> · <?php echo h($post['topic']); ?></div>
                                    </div>
                                    <span class="linkedin-mark" aria-hidden="true">in</span>
                                </div>
                                <h3><?php echo h($post['title']); ?></h3>
                                <p><?php echo h($post['body']); ?></p>
                                <div class="linkedin-tags">
                                    <?php foreach ($post['tags'] as $tag): ?>
                                        <span><?php echo h($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <a class="linkedin-post-link" href="<?php echo h($linkedinPostsUrl); ?>" target="_blank" rel="noopener noreferrer">View LinkedIn posts</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="reveal" data-reveal="zoom" style="margin-bottom:14px;">
            <input type="text" id="blogSearch" name="q" class="search-input" data-search placeholder="Search insights (tax, GST, payroll...)" aria-label="Search insights" autocomplete="search">
        </div>
        <div class="chip-filter list-inline reveal" data-reveal="zoom">
            <button class="filter-btn active" data-filter="all">All</button>
            <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" data-filter="<?php echo h($cat); ?>"><?php echo h($cat); ?></button>
            <?php endforeach; ?>
        </div>
        <div class="grid grid-3" data-stagger="90">
            <?php foreach ($blogs as $blog): ?>
                <?php $blogImage = normalize_image_url((string)($blog['featured_image'] ?? ''), placeholder_image('featured')); ?>
                <div class="card blog-card reveal" data-category="<?php echo h($blog['category']); ?>" data-search-text="<?php echo h(strtolower(($blog['title'] ?? '') . ' ' . ($blog['excerpt'] ?? '') . ' ' . ($blog['category'] ?? ''))); ?>" data-tilt>
                    <img loading="lazy" decoding="async" src="<?php echo h($blogImage); ?>" alt="<?php echo h($blog['title']); ?>">
                    <div class="blog-meta"><?php echo format_date($blog['date']); ?> | <?php echo h($blog['category']); ?></div>
                    <h3><?php echo h($blog['title']); ?></h3>
                    <p class="muted"><?php echo h($blog['excerpt']); ?></p>
                    <a class="link-arrow" href="post.php?slug=<?php echo urlencode($blog['slug']); ?>">Read article -></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Need help with a topic?</h3>
                <p class="muted">Talk to our team for guidance specific to your business.</p>
            </div>
            <div>
                <a class="btn" href="contact.php">Contact us</a>
            </div>
        </div>
    </div>
</section>

<?php if ($hasLinkedinWidget): ?>
    <script src="<?php echo h($linkedinWidget['script']); ?>" defer></script>
<?php endif; ?>

<?php require __DIR__ . '/inc/footer.php'; ?>
