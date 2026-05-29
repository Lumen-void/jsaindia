<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/data.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$baseUrl = rtrim($baseUrl, '/');

function sm_url(string $baseUrl, string $path): string
{
    if ($path === '') $path = '/';
    if ($path[0] !== '/') $path = '/' . $path;
    return $baseUrl . $path;
}

function sm_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$urls = [];
$today = date('Y-m-d');

// Static pages
$static = [
    ['loc' => sm_url($baseUrl, '/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
    ['loc' => sm_url($baseUrl, '/about.php'), 'changefreq' => 'monthly', 'priority' => '0.7'],
    ['loc' => sm_url($baseUrl, '/services.php'), 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['loc' => sm_url($baseUrl, '/industries.php'), 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => sm_url($baseUrl, '/case-studies.php'), 'changefreq' => 'monthly', 'priority' => '0.7'],
    ['loc' => sm_url($baseUrl, '/team.php'), 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => sm_url($baseUrl, '/resources.php'), 'changefreq' => 'weekly', 'priority' => '0.7'],
    ['loc' => sm_url($baseUrl, '/updates.php'), 'changefreq' => 'daily', 'priority' => '0.8'],
    ['loc' => sm_url($baseUrl, '/blog.php'), 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['loc' => sm_url($baseUrl, '/careers.php'), 'changefreq' => 'weekly', 'priority' => '0.6'],
    ['loc' => sm_url($baseUrl, '/contact.php'), 'changefreq' => 'yearly', 'priority' => '0.6'],
    ['loc' => sm_url($baseUrl, '/privacy.php'), 'changefreq' => 'yearly', 'priority' => '0.2'],
    ['loc' => sm_url($baseUrl, '/terms.php'), 'changefreq' => 'yearly', 'priority' => '0.2'],
];
foreach ($static as $u) {
    $u['lastmod'] = $today;
    $urls[] = $u;
}

// Service pages
foreach (get_services() as $svc) {
    if (!is_array($svc)) continue;
    $slug = (string)($svc['slug'] ?? '');
    if ($slug === '') continue;
    $urls[] = [
        'loc' => sm_url($baseUrl, '/service.php?slug=' . rawurlencode($slug)),
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.8',
    ];
}

// Blog posts (published only)
foreach (get_published_blogs() as $post) {
    if (!is_array($post)) continue;
    $slug = (string)($post['slug'] ?? '');
    if ($slug === '') continue;
    $date = (string)($post['date'] ?? '');
    $lastmod = $date !== '' ? $date : $today;
    $urls[] = [
        'loc' => sm_url($baseUrl, '/post.php?slug=' . rawurlencode($slug)),
        'lastmod' => $lastmod,
        'changefreq' => 'monthly',
        'priority' => '0.6',
    ];
}

// Careers
foreach (get_careers() as $job) {
    if (!is_array($job)) continue;
    $slug = (string)($job['slug'] ?? '');
    if ($slug === '') continue;
    $urls[] = [
        'loc' => sm_url($baseUrl, '/career.php?slug=' . rawurlencode($slug)),
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.5',
    ];
}

// Partner profile pages
foreach (get_team() as $member) {
    if (!is_array($member)) continue;
    $profileUrl = trim((string)($member['profile_url'] ?? ''));
    if ($profileUrl === '') continue;
    $urls[] = [
        'loc' => sm_url($baseUrl, '/' . ltrim($profileUrl, '/')),
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.6',
    ];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?php echo sm_escape((string)$u['loc']); ?></loc>
    <lastmod><?php echo sm_escape((string)($u['lastmod'] ?? $today)); ?></lastmod>
    <?php if (!empty($u['changefreq'])): ?><changefreq><?php echo sm_escape((string)$u['changefreq']); ?></changefreq><?php endif; ?>
    <?php if (!empty($u['priority'])): ?><priority><?php echo sm_escape((string)$u['priority']); ?></priority><?php endif; ?>
  </url>
<?php endforeach; ?>
</urlset>
