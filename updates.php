<?php
$pageTitle = 'Regulatory Updates | Japneet S & Associates';
$metaDescription = 'Latest regulatory circulars, notifications, and compliance updates from official sources like RBI, Income Tax India, CBIC, EPFO, and ESIC.';
require_once __DIR__ . '/inc/data.php';

$updates = get_regulatory_updates();
$knownSources = ['Income Tax India', 'RBI', 'CBIC', 'ESIC', 'EPFO'];

$q = trim((string)($_GET['q'] ?? ''));
$source = trim((string)($_GET['source'] ?? ''));
$tag = trim((string)($_GET['tag'] ?? ''));

$sources = [];
$tags = [];
foreach ($updates as $u) {
    if (!is_array($u)) {
        continue;
    }
    if ((string)($u['status'] ?? 'published') !== 'published') {
        continue;
    }
    $s = (string)($u['source'] ?? '');
    $t = (string)($u['tag'] ?? '');
    if ($s !== '') {
        $sources[$s] = true;
    }
    if ($t !== '') {
        $tags[$t] = true;
    }
}
$sources = array_keys($sources);
sort($sources);
$sources = array_values(array_unique(array_merge($knownSources, $sources)));

$tags = array_keys($tags);
sort($tags);

$filtered = array_values(array_filter($updates, function ($u) use ($q, $source, $tag) {
    if (!is_array($u)) {
        return false;
    }
    if ((string)($u['status'] ?? 'published') !== 'published') {
        return false;
    }
    if ($source !== '' && (string)($u['source'] ?? '') !== $source) {
        return false;
    }
    if ($tag !== '' && (string)($u['tag'] ?? '') !== $tag) {
        return false;
    }
    if ($q !== '') {
        $hay = strtolower((string)($u['title'] ?? '') . ' ' . (string)($u['source'] ?? '') . ' ' . (string)($u['tag'] ?? ''));
        if (strpos($hay, strtolower($q)) === false) {
            return false;
        }
    }
    return true;
}));

$nowTs = time();
usort($filtered, function (array $a, array $b) use ($nowTs) {
    $ap = (string)($a['pinned_until'] ?? '');
    $bp = (string)($b['pinned_until'] ?? '');
    $apTs = $ap !== '' ? (strtotime($ap) ?: 0) : 0;
    $bpTs = $bp !== '' ? (strtotime($bp) ?: 0) : 0;
    $aPinned = $apTs >= $nowTs;
    $bPinned = $bpTs >= $nowTs;
    if ($aPinned !== $bPinned) {
        return $aPinned ? -1 : 1;
    }
    $at = strtotime((string)($a['published_at'] ?? '')) ?: 0;
    $bt = strtotime((string)($b['published_at'] ?? '')) ?: 0;
    if ($at === $bt) {
        return strcmp((string)($b['key'] ?? ''), (string)($a['key'] ?? ''));
    }
    return $bt <=> $at;
});

$filtered = array_slice($filtered, 0, 120);

$lastFetchedTs = 0;
$recent30 = 0;
$now = time();
foreach ($updates as $u) {
    if (!is_array($u)) {
        continue;
    }
    if ((string)($u['status'] ?? 'published') !== 'published') {
        continue;
    }
    $ft = strtotime((string)($u['fetched_at'] ?? '')) ?: 0;
    if ($ft > $lastFetchedTs) {
        $lastFetchedTs = $ft;
    }
    $pt = strtotime((string)($u['published_at'] ?? '')) ?: 0;
    if ($pt && $pt >= ($now - 30 * 86400)) {
        $recent30++;
    }
}
$lastFetchedNice = $lastFetchedTs ? date('d M Y, H:i', $lastFetchedTs) . ' IST' : 'Not synced yet';

function build_updates_url(array $params): string
{
    $clean = [];
    foreach ($params as $k => $v) {
        $v = trim((string)$v);
        if ($v === '') {
            continue;
        }
        $clean[$k] = $v;
    }
    if (empty($clean)) {
        return 'updates.php';
    }
    return 'updates.php?' . http_build_query($clean);
}

$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$updateItems = [];
foreach (array_slice($filtered, 0, 10) as $index => $update) {
    if (!is_array($update)) {
        continue;
    }
    $title = trim((string)($update['title'] ?? ''));
    if ($title === '') {
        continue;
    }
    $itemUrl = trim((string)($update['url'] ?? ''));
    if ($itemUrl === '') {
        $itemUrl = $baseUrl . '/updates.php';
    }
    $updateItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'url' => $itemUrl,
    ];
}
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Regulatory Updates', 'item' => $baseUrl . '/updates.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $baseUrl . '/updates.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/updates.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $updateItems,
        ],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero hero-small updates-hero">
    <div class="container">
        <p class="eyebrow reveal">Compliance Desk</p>
        <h1 class="reveal">Regulatory Updates</h1>
        <p class="subhead reveal">Auto-fetched from official sources (RBI, Income Tax India, CBIC, ESIC, EPFO). Always verify the original notification before acting.</p>
        <div class="cta-row reveal">
            <a class="btn" href="contact.php#inquiry-form">Need help interpreting an update?</a>
            <a class="btn ghost" href="services.php">Explore services</a>
        </div>
        <div class="grid grid-3 updates-stats" data-stagger="90">
            <div class="mini-card reveal">
                <div class="mini-card-title">Last synced</div>
                <div class="mini-card-sub"><?php echo h($lastFetchedNice); ?></div>
            </div>
            <div class="mini-card reveal">
                <div class="mini-card-title">New (30 days)</div>
                <div class="mini-card-sub"><span data-counter data-target="<?php echo (int)$recent30; ?>" data-duration="900">0</span> updates</div>
            </div>
            <div class="mini-card reveal">
                <div class="mini-card-title">Sources</div>
                <div class="mini-card-sub"><span data-counter data-target="<?php echo (int)count($sources); ?>" data-duration="900">0</span> monitored</div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2 class="reveal">Latest circulars and notifications</h2>
            <p class="muted reveal">Showing <?php echo count($filtered); ?> items<?php echo $source !== '' ? ' from ' . h($source) : ''; ?>.</p>
        </div>

        <div class="updates-toolbar reveal">
            <div class="updates-tabs" role="tablist" aria-label="Sources">
                <?php
                $allUrl = build_updates_url(['q' => $q, 'tag' => $tag]);
                ?>
                <a class="tab <?php echo $source === '' ? 'active' : ''; ?>" href="<?php echo h($allUrl); ?>" role="tab" aria-selected="<?php echo $source === '' ? 'true' : 'false'; ?>">All</a>
                <?php foreach ($sources as $s): ?>
                    <?php
                    $tabUrl = build_updates_url(['q' => $q, 'tag' => $tag, 'source' => $s]);
                    $active = $s === $source;
                    ?>
                    <a class="tab <?php echo $active ? 'active' : ''; ?>" href="<?php echo h($tabUrl); ?>" role="tab" aria-selected="<?php echo $active ? 'true' : 'false'; ?>"><?php echo h($s); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="updates-chips" aria-label="Quick filters">
                <button class="chip" type="button" data-filter="pinned">Pinned</button>
                <button class="chip" type="button" data-filter="new7">New 7d</button>
                <button class="chip" type="button" data-filter="pdf">PDF</button>
                <button class="chip" type="button" data-filter="high">High importance</button>
                <button class="chip ghost" type="button" data-filter="clear">Clear</button>
            </div>

            <form class="card filters-card" method="get" action="updates.php">
                <div class="form-row">
                    <div>
                        <div class="field-label">Search</div>
                        <input type="search" name="q" value="<?php echo h($q); ?>" placeholder="Search title, source, tag" autocomplete="search">
                    </div>
                    <div>
                        <div class="field-label">Source</div>
                        <select name="source">
                            <option value="">All</option>
                            <?php foreach ($sources as $s): ?>
                                <option value="<?php echo h($s); ?>" <?php echo $s === $source ? 'selected' : ''; ?>><?php echo h($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <div class="field-label">Tag</div>
                        <select name="tag">
                            <option value="">All</option>
                            <?php foreach ($tags as $t): ?>
                                <option value="<?php echo h($t); ?>" <?php echo $t === $tag ? 'selected' : ''; ?>><?php echo h($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="cta-row filters-actions">
                    <button class="btn" type="submit">Apply</button>
                    <a class="btn ghost" href="updates.php">Reset</a>
                </div>
            </form>
        </div>

        <div class="grid grid-2 updates-grid" data-stagger="80" aria-live="polite">
            <?php if (empty($filtered)): ?>
                <div class="card reveal">
                    <h3>No updates found</h3>
                    <p class="muted">There are no regulatory updates available right now. Please check back shortly.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($filtered as $u): ?>
                <?php
                $title = (string)($u['title'] ?? '(Untitled)');
                $url = (string)($u['url'] ?? '');
                $src = (string)($u['source'] ?? '');
                $t = (string)($u['tag'] ?? '');
                $published = (string)($u['published_at'] ?? '');
                $publishedTs = strtotime($published) ?: 0;
                $publishedNice = $publishedTs ? date('d M Y', $publishedTs) : '';
                $isNew = $publishedTs ? ($publishedTs >= (time() - 7 * 86400)) : false;
                $status = (string)($u['status'] ?? 'published');
                $summary = trim((string)($u['summary'] ?? ''));
                $importance = trim((string)($u['importance'] ?? ''));
                $effectiveDate = trim((string)($u['effective_date'] ?? ''));
                $pinnedUntil = trim((string)($u['pinned_until'] ?? ''));
                $isPinned = $pinnedUntil !== '' && (strtotime($pinnedUntil) ?: 0) >= time();
                $actionItems = $u['action_items'] ?? [];
                if (!is_array($actionItems)) $actionItems = [];
                $actionItems = array_values(array_filter(array_map('trim', $actionItems), fn($v) => $v !== ''));
                $host = '';
                if ($url !== '') {
                    $host = (string)(parse_url($url, PHP_URL_HOST) ?? '');
                    $host = preg_replace('/^www\\./i', '', $host);
                }
                $ext = '';
                if ($url !== '') {
                    $path = (string)(parse_url($url, PHP_URL_PATH) ?? '');
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                }
                $docTag = $ext !== '' ? strtoupper($ext) : '';
                $avatar = '';
                $words = preg_split('/\\s+/', trim($src)) ?: [];
                foreach ($words as $w) {
                    if ($w === '') continue;
                    $avatar .= strtoupper(substr($w, 0, 1));
                    if (strlen($avatar) >= 3) break;
                }
                if ($avatar === '') {
                    $avatar = 'UPD';
                }
                $sourceKey = slugify($src);
                ?>
                <article
                    class="card update-card reveal reveal-right"
                    data-tilt="1"
                    data-key="<?php echo h((string)($u['key'] ?? '')); ?>"
                    data-source="<?php echo h($sourceKey); ?>"
                    data-source-label="<?php echo h($src); ?>"
                    data-tag="<?php echo h($t); ?>"
                    data-url="<?php echo h($url); ?>"
                    data-title="<?php echo h($title); ?>"
                    data-published-at="<?php echo h($published); ?>"
                    data-doc="<?php echo h($docTag); ?>"
                    data-pinned="<?php echo $isPinned ? '1' : '0'; ?>"
                    data-importance="<?php echo h($importance); ?>"
                    data-summary="<?php echo h($summary); ?>"
                    data-effective-date="<?php echo h($effectiveDate); ?>"
                    data-status="<?php echo h($status); ?>"
                    data-actions="<?php echo h(implode("\n", $actionItems)); ?>"
                >
                    <div class="update-inner">
                        <div class="update-avatar" aria-hidden="true"><?php echo h($avatar); ?></div>
                        <div class="update-body">
                            <div class="meta">
                                <span class="pill"><?php echo h($src); ?></span>
                                <?php if ($isPinned): ?><span class="pill soft">Pinned</span><?php endif; ?>
                                <?php if ($isNew): ?><span class="pill new">New</span><?php endif; ?>
                                <?php if ($t !== ''): ?><span class="pill soft"><?php echo h($t); ?></span><?php endif; ?>
                                <?php if ($docTag !== ''): ?><span class="pill soft"><?php echo h($docTag); ?></span><?php endif; ?>
                                <?php if ($publishedNice !== ''): ?><span class="pill soft"><?php echo h($publishedNice); ?></span><?php endif; ?>
                            </div>
                            <h3 class="update-title"><?php echo h($title); ?></h3>
                            <?php if ($summary !== ''): ?>
                                <div class="update-summary"><?php echo h($summary); ?></div>
                            <?php endif; ?>
                            <?php if ($host !== ''): ?>
                                <div class="update-sub muted"><?php echo h($host); ?></div>
                            <?php endif; ?>
                            <div class="update-actions">
                                <?php if ($url !== ''): ?>
                                    <a class="btn" href="<?php echo h($url); ?>" target="_blank" rel="noopener noreferrer">Open source</a>
                                <?php endif; ?>
                                <button class="btn ghost update-open" type="button">Details</button>
                                <button class="btn ghost update-bookmark" type="button" aria-pressed="false">Save</button>
                                <a class="btn ghost" href="contact.php#inquiry-form">Ask our team</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="alert reveal">
            <strong>Note:</strong> This page stores titles + links from public endpoints and scrapes where permitted. Always rely on the original circular/notification text for compliance decisions.
        </div>

        <div class="section-head" style="margin-top: 22px;">
            <h2 class="reveal">Get compliance alerts</h2>
            <p class="muted reveal">Subscribe to weekly or daily updates. No login needed.</p>
        </div>

        <div class="grid grid-2">
            <div class="card reveal" data-reveal="zoom">
                <h3>Subscribe</h3>
                <form id="subscribeForm" class="subscribe-form" method="post" action="api/subscribe.php" data-ajax="subscribe">
                    <div class="form-row">
                        <div>
                            <div class="field-label">Email</div>
                            <input type="email" name="email" placeholder="you@company.com" autocomplete="email" required>
                        </div>
                        <div>
                            <div class="field-label">Frequency</div>
                            <select name="frequency">
                                <option value="weekly" selected>Weekly digest</option>
                                <option value="daily">Daily digest</option>
                                <option value="instant">Instant (high volume)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="field-label">Keywords (optional)</div>
                        <input type="text" name="keywords" placeholder="e.g. GST, TDS, payroll, notice, threshold" autocomplete="off">
                    </div>
                    <div>
                        <div class="field-label">Sources</div>
                        <div class="check-grid">
                            <?php foreach ($sources as $s): ?>
                                <label class="check">
                                    <input type="checkbox" name="sources[]" value="<?php echo h($s); ?>">
                                    <span><?php echo h($s); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="muted" style="font-size: 13px; margin-top: 8px;">Leave all unchecked to receive a combined digest.</div>
                    </div>

                    <div class="hp" aria-hidden="true">
                        <input type="text" name="company" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="cta-row filters-actions">
                        <button class="btn" type="submit">Subscribe</button>
                        <a class="btn ghost" href="contact.php#inquiry-form">Need a compliance calendar?</a>
                    </div>
                    <div class="form-status" role="status" aria-live="polite"></div>
                </form>
            </div>
            <div class="card reveal" data-reveal="zoom">
                <h3>Enhancements available</h3>
                <p class="muted">Includes review workflow, summaries, pinning, exports, and subscriber management via Admin.</p>
                <ul class="list-bullets">
                    <li><span class="marker">&bull;</span>Publish/draft + pinned important items</li>
                    <li><span class="marker">&bull;</span>Short summary + action items per update</li>
                    <li><span class="marker">&bull;</span>Weekly PDF/CSV report export</li>
                    <li><span class="marker">&bull;</span>Client-specific digests by industry</li>
                </ul>
                <div class="cta-row">
                    <a class="btn" href="contact.php#inquiry-form">Set up alerts for my firm</a>
                    <a class="btn ghost" href="resources.php">Resources</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
