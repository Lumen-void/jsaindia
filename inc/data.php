<?php
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/db.php';

function to_json_or_null($value): ?string
{
    if ($value === null) return null;
    if (!is_array($value)) return null;
    return json_encode($value, JSON_UNESCAPED_SLASHES);
}

function from_json_array($value): array
{
    if ($value === null) return [];
    if (is_array($value)) return $value;
    $decoded = json_decode((string)$value, true);
    return is_array($decoded) ? $decoded : [];
}

function dt_to_mysql(?string $iso): ?string
{
    $iso = trim((string)$iso);
    if ($iso === '') return null;
    $ts = strtotime($iso);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}

function mysql_to_iso(?string $dt): string
{
    $dt = trim((string)$dt);
    if ($dt === '') return '';
    $ts = strtotime($dt);
    return $ts ? date('c', $ts) : $dt;
}

function data_path(string $filename): string
{
    return __DIR__ . '/../data/' . $filename;
}

function read_json(string $filename, array $default = []): array
{
    $path = data_path($filename);
    if (!file_exists($path)) {
        return $default;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        return $default;
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : $default;
}

function save_json(string $filename, array $data): bool
{
    $path = data_path($filename);
    return (bool)file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function team_slug_from_name(string $name): string
{
    $name = preg_replace('/^ca\\s+/i', '', trim($name));
    $name = preg_replace('/\\s+/', ' ', $name);
    return slugify($name);
}

function normalize_team_member(array $member): array
{
    $name = trim((string)($member['name'] ?? ''));
    $slug = trim((string)($member['slug'] ?? ''));
    if ($slug === '' && $name !== '') {
        $slug = team_slug_from_name($name);
    }
    $profileUrl = trim((string)($member['profile_url'] ?? ''));
    if ($profileUrl === '' && $slug !== '') {
        $candidate = $slug . '.php';
        if (file_exists(__DIR__ . '/../' . $candidate)) {
            $profileUrl = $candidate;
        } else {
            $profileUrl = 'partner.php?slug=' . rawurlencode($slug);
        }
    }
    $member['name'] = $name;
    $member['slug'] = $slug;
    $member['profile_url'] = $profileUrl;
    return $member;
}

function placeholder_image(string $type): string
{
    if ($type === 'team') {
        return 'assets/placeholders/team-320x320.svg';
    }
    return 'assets/placeholders/featured-960x480.svg';
}

function normalize_image_url(string $url, string $fallback): string
{
    $url = trim($url);
    if ($url === '') return $fallback;
    $lower = strtolower($url);
    if (strpos($lower, 'via.placeholder.com') !== false || strpos($lower, 'placehold.co') !== false) {
        return $fallback;
    }
    return $url;
}

function default_process_steps(): array
{
    return [
        'Understand requirements',
        'Review documents & compliance needs',
        'Execute and file/prepare deliverables',
        'Share updates + ongoing support',
    ];
}

function default_why_choose_us(): array
{
    return [
        'Compliance-first approach',
        'Clear communication',
        'Timely delivery',
        'Confidential handling',
        'Dedicated support',
    ];
}

function get_services(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM services ORDER BY sort_order ASC, title ASC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'title' => (string)($r['title'] ?? ''),
                'slug' => (string)($r['slug'] ?? ''),
                'icon' => (string)($r['icon'] ?? ''),
                'hero_line' => (string)($r['hero_line'] ?? ''),
                'short_intro' => (string)($r['short_intro'] ?? ''),
                'what_we_cover' => from_json_array($r['what_we_cover_json'] ?? null),
                'who_its_for' => from_json_array($r['who_its_for_json'] ?? null),
                'process_steps' => from_json_array($r['process_steps_json'] ?? null),
                'why_choose_us' => from_json_array($r['why_choose_us_json'] ?? null),
                'faqs' => from_json_array($r['faqs_json'] ?? null),
                'meta_title' => (string)($r['meta_title'] ?? ''),
                'meta_description' => (string)($r['meta_description'] ?? ''),
            ];
        }, $rows);
    }
    return read_json('services.json');
}

function save_services(array $services): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT slug FROM services')->fetchAll();
            $existingSlugs = array_map(fn($r) => (string)($r['slug'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO services (slug, title, icon, hero_line, short_intro, what_we_cover_json, who_its_for_json, process_steps_json, why_choose_us_json, faqs_json, meta_title, meta_description, sort_order)
                VALUES (:slug, :title, :icon, :hero_line, :short_intro, :what_we_cover_json, :who_its_for_json, :process_steps_json, :why_choose_us_json, :faqs_json, :meta_title, :meta_description, :sort_order)
                ON DUPLICATE KEY UPDATE
                    title=VALUES(title),
                    icon=VALUES(icon),
                    hero_line=VALUES(hero_line),
                    short_intro=VALUES(short_intro),
                    what_we_cover_json=VALUES(what_we_cover_json),
                    who_its_for_json=VALUES(who_its_for_json),
                    process_steps_json=VALUES(process_steps_json),
                    why_choose_us_json=VALUES(why_choose_us_json),
                    faqs_json=VALUES(faqs_json),
                    meta_title=VALUES(meta_title),
                    meta_description=VALUES(meta_description),
                    sort_order=VALUES(sort_order),
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($services as $i => $svc) {
                if (!is_array($svc)) continue;
                $slug = (string)($svc['slug'] ?? '');
                if ($slug === '') continue;
                $keep[] = $slug;
                $stmt->execute([
                    ':slug' => $slug,
                    ':title' => (string)($svc['title'] ?? ''),
                    ':icon' => (string)($svc['icon'] ?? ''),
                    ':hero_line' => (string)($svc['hero_line'] ?? ''),
                    ':short_intro' => (string)($svc['short_intro'] ?? ''),
                    ':what_we_cover_json' => to_json_or_null($svc['what_we_cover'] ?? []),
                    ':who_its_for_json' => to_json_or_null($svc['who_its_for'] ?? []),
                    ':process_steps_json' => to_json_or_null($svc['process_steps'] ?? []),
                    ':why_choose_us_json' => to_json_or_null($svc['why_choose_us'] ?? []),
                    ':faqs_json' => to_json_or_null($svc['faqs'] ?? []),
                    ':meta_title' => (string)($svc['meta_title'] ?? ''),
                    ':meta_description' => (string)($svc['meta_description'] ?? ''),
                    ':sort_order' => (int)($svc['sort_order'] ?? (($i + 1) * 10)),
                ]);
            }

            $toDelete = array_diff($existingSlugs, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM services WHERE slug IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('services.json', $services);
}

function get_service_by_slug(string $slug): ?array
{
    foreach (get_services() as $service) {
        if (($service['slug'] ?? '') === $slug) {
            return $service;
        }
    }
    return null;
}

function get_blogs(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM blogs ORDER BY date DESC, created_at DESC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'title' => (string)($r['title'] ?? ''),
                'slug' => (string)($r['slug'] ?? ''),
                'excerpt' => (string)($r['excerpt'] ?? ''),
                'content' => (string)($r['content'] ?? ''),
                'featured_image' => (string)($r['featured_image'] ?? ''),
                'category' => (string)($r['category'] ?? ''),
                'date' => (string)($r['date'] ?? ''),
                'status' => (string)($r['status'] ?? 'published'),
            ];
        }, $rows);
    }
    return read_json('blogs.json');
}

function save_blogs(array $blogs): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT slug FROM blogs')->fetchAll();
            $existingSlugs = array_map(fn($r) => (string)($r['slug'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO blogs (slug, title, excerpt, content, featured_image, category, date, status)
                VALUES (:slug, :title, :excerpt, :content, :featured_image, :category, :date, :status)
                ON DUPLICATE KEY UPDATE
                    title=VALUES(title),
                    excerpt=VALUES(excerpt),
                    content=VALUES(content),
                    featured_image=VALUES(featured_image),
                    category=VALUES(category),
                    date=VALUES(date),
                    status=VALUES(status),
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($blogs as $b) {
                if (!is_array($b)) continue;
                $slug = (string)($b['slug'] ?? '');
                if ($slug === '') continue;
                $keep[] = $slug;
                $date = trim((string)($b['date'] ?? ''));
                $stmt->execute([
                    ':slug' => $slug,
                    ':title' => (string)($b['title'] ?? ''),
                    ':excerpt' => (string)($b['excerpt'] ?? ''),
                    ':content' => (string)($b['content'] ?? ''),
                    ':featured_image' => (string)($b['featured_image'] ?? ''),
                    ':category' => (string)($b['category'] ?? ''),
                    ':date' => $date !== '' ? $date : null,
                    ':status' => (string)($b['status'] ?? 'published'),
                ]);
            }

            $toDelete = array_diff($existingSlugs, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM blogs WHERE slug IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('blogs.json', $blogs);
}

function get_blog_by_slug(string $slug, bool $includeDrafts = false): ?array
{
    foreach (get_blogs() as $blog) {
        if (($blog['slug'] ?? '') === $slug) {
            if (!$includeDrafts && ($blog['status'] ?? 'published') !== 'published') {
                return null;
            }
            return $blog;
        }
    }
    return null;
}

function get_team(): array
{
    $normalized = [];
    $keys = [];

    if (db_enabled()) {
        try {
            $rows = db()->query('SELECT * FROM team WHERE active=1 ORDER BY sort_order ASC, name ASC')->fetchAll();
            foreach ($rows as $r) {
                if (!is_array($r)) continue;
                $member = normalize_team_member([
                    'name' => (string)($r['name'] ?? ''),
                    'role' => (string)($r['role'] ?? ''),
                    'bio' => (string)($r['bio'] ?? ''),
                    'photo' => (string)($r['photo'] ?? ''),
                ]);
                if ($member['name'] === '') continue;
                $key = $member['slug'] !== '' ? $member['slug'] : team_slug_from_name($member['name']);
                if ($key === '') continue;
                $keys[$key] = true;
                $normalized[] = $member;
            }
        } catch (Throwable $e) {
            // Fall back to JSON if the table is missing or DB isn't ready.
        }
    }

    foreach (read_json('team.json') as $member) {
        if (!is_array($member)) continue;
        $member = normalize_team_member($member);
        if ($member['name'] === '') continue;
        $key = $member['slug'] !== '' ? $member['slug'] : team_slug_from_name($member['name']);
        if ($key === '') continue;
        if (!isset($keys[$key])) {
            $keys[$key] = true;
            $normalized[] = $member;
        }
    }

    return $normalized;
}

function get_team_member_by_slug(string $slug): ?array
{
    $slug = trim($slug);
    if ($slug === '') return null;
    $slug = slugify($slug);
    foreach (get_team() as $member) {
        if (!is_array($member)) continue;
        $memberSlug = trim((string)($member['slug'] ?? ''));
        $fallbackSlug = team_slug_from_name((string)($member['name'] ?? ''));
        $profileUrl = (string)($member['profile_url'] ?? '');
        $profileSlug = '';
        $profileQuerySlug = '';
        if ($profileUrl !== '') {
            $path = parse_url($profileUrl, PHP_URL_PATH);
            $base = $path ? basename($path) : $profileUrl;
            $profileSlug = slugify(preg_replace('/\\.php$/i', '', $base));
            $query = parse_url($profileUrl, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $params);
                if (!empty($params['slug'])) {
                    $profileQuerySlug = slugify((string)$params['slug']);
                }
            }
        }
        if ($memberSlug === $slug || $fallbackSlug === $slug || $profileSlug === $slug || $profileQuerySlug === $slug) {
            return $member;
        }
    }
    // If DB is enabled but slugs/names don't match, try the JSON source directly.
    foreach (read_json('team.json') as $member) {
        if (!is_array($member)) continue;
        $member = normalize_team_member($member);
        $memberSlug = trim((string)($member['slug'] ?? ''));
        $fallbackSlug = team_slug_from_name((string)($member['name'] ?? ''));
        $profileUrl = (string)($member['profile_url'] ?? '');
        $profileSlug = '';
        $profileQuerySlug = '';
        if ($profileUrl !== '') {
            $path = parse_url($profileUrl, PHP_URL_PATH);
            $base = $path ? basename($path) : $profileUrl;
            $profileSlug = slugify(preg_replace('/\\.php$/i', '', $base));
            $query = parse_url($profileUrl, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $params);
                if (!empty($params['slug'])) {
                    $profileQuerySlug = slugify((string)$params['slug']);
                }
            }
        }
        if ($memberSlug === $slug || $fallbackSlug === $slug || $profileSlug === $slug || $profileQuerySlug === $slug) {
            return $member;
        }
    }
    return null;
}

function save_team(array $team): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT name FROM team')->fetchAll();
            $existingNames = array_map(fn($r) => (string)($r['name'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO team (name, role, bio, photo, sort_order, active)
                VALUES (:name, :role, :bio, :photo, :sort_order, 1)
                ON DUPLICATE KEY UPDATE
                    role=VALUES(role),
                    bio=VALUES(bio),
                    photo=VALUES(photo),
                    sort_order=VALUES(sort_order),
                    active=1,
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($team as $i => $m) {
                if (!is_array($m)) continue;
                $name = (string)($m['name'] ?? '');
                if ($name === '') continue;
                $keep[] = $name;
                $stmt->execute([
                    ':name' => $name,
                    ':role' => (string)($m['role'] ?? ''),
                    ':bio' => (string)($m['bio'] ?? ''),
                    ':photo' => (string)($m['photo'] ?? ''),
                    ':sort_order' => (int)($m['sort_order'] ?? (($i + 1) * 10)),
                ]);
            }

            $toDeactivate = array_diff($existingNames, $keep);
            if (!empty($toDeactivate)) {
                $in = implode(',', array_fill(0, count($toDeactivate), '?'));
                $upd = $pdo->prepare("UPDATE team SET active=0 WHERE name IN ($in)");
                $upd->execute(array_values($toDeactivate));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('team.json', $team);
}

function get_case_studies(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM case_studies ORDER BY created_at DESC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'title' => (string)($r['title'] ?? ''),
                'industry' => (string)($r['industry'] ?? ''),
                'problem' => (string)($r['problem'] ?? ''),
                'solution' => (string)($r['solution'] ?? ''),
                'outcome' => (string)($r['outcome'] ?? ''),
                'metrics' => from_json_array($r['metrics_json'] ?? null),
            ];
        }, $rows);
    }
    return read_json('case-studies.json');
}

function save_case_studies(array $caseStudies): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT title FROM case_studies')->fetchAll();
            $existingTitles = array_map(fn($r) => (string)($r['title'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO case_studies (title, industry, problem, solution, outcome, metrics_json)
                VALUES (:title, :industry, :problem, :solution, :outcome, :metrics_json)
                ON DUPLICATE KEY UPDATE
                    industry=VALUES(industry),
                    problem=VALUES(problem),
                    solution=VALUES(solution),
                    outcome=VALUES(outcome),
                    metrics_json=VALUES(metrics_json),
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($caseStudies as $c) {
                if (!is_array($c)) continue;
                $title = (string)($c['title'] ?? '');
                if ($title === '') continue;
                $keep[] = $title;
                $stmt->execute([
                    ':title' => $title,
                    ':industry' => (string)($c['industry'] ?? ''),
                    ':problem' => (string)($c['problem'] ?? ''),
                    ':solution' => (string)($c['solution'] ?? ''),
                    ':outcome' => (string)($c['outcome'] ?? ''),
                    ':metrics_json' => to_json_or_null($c['metrics'] ?? []),
                ]);
            }

            $toDelete = array_diff($existingTitles, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM case_studies WHERE title IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('case-studies.json', $caseStudies);
}

function get_careers(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM careers ORDER BY created_at DESC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'title' => (string)($r['title'] ?? ''),
                'slug' => (string)($r['slug'] ?? ''),
                'location' => (string)($r['location'] ?? ''),
                'type' => (string)($r['type'] ?? ''),
                'overview' => (string)($r['overview'] ?? ''),
                'responsibilities' => from_json_array($r['responsibilities_json'] ?? null),
                'requirements' => from_json_array($r['requirements_json'] ?? null),
            ];
        }, $rows);
    }
    return read_json('careers.json');
}

function save_careers(array $careers): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT slug FROM careers')->fetchAll();
            $existingSlugs = array_map(fn($r) => (string)($r['slug'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO careers (slug, title, location, type, overview, responsibilities_json, requirements_json)
                VALUES (:slug, :title, :location, :type, :overview, :responsibilities_json, :requirements_json)
                ON DUPLICATE KEY UPDATE
                    title=VALUES(title),
                    location=VALUES(location),
                    type=VALUES(type),
                    overview=VALUES(overview),
                    responsibilities_json=VALUES(responsibilities_json),
                    requirements_json=VALUES(requirements_json),
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($careers as $c) {
                if (!is_array($c)) continue;
                $slug = (string)($c['slug'] ?? '');
                if ($slug === '') continue;
                $keep[] = $slug;
                $stmt->execute([
                    ':slug' => $slug,
                    ':title' => (string)($c['title'] ?? ''),
                    ':location' => (string)($c['location'] ?? ''),
                    ':type' => (string)($c['type'] ?? ''),
                    ':overview' => (string)($c['overview'] ?? ''),
                    ':responsibilities_json' => to_json_or_null($c['responsibilities'] ?? []),
                    ':requirements_json' => to_json_or_null($c['requirements'] ?? []),
                ]);
            }

            $toDelete = array_diff($existingSlugs, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM careers WHERE slug IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('careers.json', $careers);
}

function get_career_by_slug(string $slug): ?array
{
    foreach (get_careers() as $career) {
        if (($career['slug'] ?? '') === $slug) {
            return $career;
        }
    }
    return null;
}

function get_inquiries(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM inquiries ORDER BY timestamp ASC, id ASC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'timestamp' => mysql_to_iso((string)($r['timestamp'] ?? '')),
                'name' => (string)($r['name'] ?? ''),
                'email' => (string)($r['email'] ?? ''),
                'phone' => (string)($r['phone'] ?? ''),
                'selected_service' => (string)($r['selected_service'] ?? ''),
                'preferred_slot' => (string)($r['preferred_slot'] ?? ''),
                'page_source' => (string)($r['page_source'] ?? ''),
                'message' => (string)($r['message'] ?? ''),
            ];
        }, $rows);
    }
    return read_json('inquiries.json');
}

function normalize_slot_date(string $date): ?string
{
    $date = trim($date);
    if ($date === '') return null;
    $ts = strtotime($date);
    return $ts ? date('Y-m-d', $ts) : null;
}

function normalize_slot_time(string $time): ?string
{
    $time = trim($time);
    if ($time === '') return null;
    $ts = strtotime('1970-01-01 ' . $time);
    return $ts ? date('H:i', $ts) : null;
}

function get_bookings(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM bookings ORDER BY created_at DESC, id DESC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'id' => (string)($r['id'] ?? ''),
                'created_at' => mysql_to_iso((string)($r['created_at'] ?? '')),
                'service_title' => (string)($r['service_title'] ?? ''),
                'service_slug' => (string)($r['service_slug'] ?? ''),
                'name' => (string)($r['name'] ?? ''),
                'email' => (string)($r['email'] ?? ''),
                'phone' => (string)($r['phone'] ?? ''),
                'slot_date' => (string)($r['slot_date'] ?? ''),
                'slot_time' => normalize_slot_time((string)($r['slot_time'] ?? '')) ?? '',
                'status' => (string)($r['status'] ?? 'booked'),
            ];
        }, $rows);
    }
    return read_json('bookings.json');
}

function save_bookings(array $rows): bool
{
    return save_json('bookings.json', $rows);
}

function booking_slot_taken(string $date, string $time): bool
{
    $date = normalize_slot_date($date);
    $time = normalize_slot_time($time);
    if (!$date || !$time) return false;
    $activeStatuses = ['approved', 'booked'];

    if (db_enabled()) {
        $in = implode(',', array_fill(0, count($activeStatuses), '?'));
        $stmt = db()->prepare("SELECT 1 FROM bookings WHERE slot_date = ? AND slot_time = ? AND status IN ($in) LIMIT 1");
        $stmt->execute([
            $date,
            $time,
            ...$activeStatuses,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    foreach (get_bookings() as $row) {
        if (!is_array($row)) continue;
        $status = (string)($row['status'] ?? 'booked');
        if (!in_array($status, $activeStatuses, true)) continue;
        if ((string)($row['slot_date'] ?? '') === $date && (string)($row['slot_time'] ?? '') === $time) {
            return true;
        }
    }
    return false;
}

function get_booked_slots(string $date): array
{
    $date = normalize_slot_date($date);
    if (!$date) return [];
    $activeStatuses = ['approved', 'booked'];

    if (db_enabled()) {
        $in = implode(',', array_fill(0, count($activeStatuses), '?'));
        $stmt = db()->prepare("SELECT slot_time FROM bookings WHERE slot_date = ? AND status IN ($in)");
        $stmt->execute([$date, ...$activeStatuses]);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $time = normalize_slot_time((string)($r['slot_time'] ?? ''));
            if ($time) $out[] = $time;
        }
        return $out;
    }

    $out = [];
    foreach (get_bookings() as $row) {
        if (!is_array($row)) continue;
        $status = (string)($row['status'] ?? 'booked');
        if (!in_array($status, $activeStatuses, true)) continue;
        if ((string)($row['slot_date'] ?? '') === $date && !empty($row['slot_time'])) {
            $out[] = (string)$row['slot_time'];
        }
    }
    return $out;
}

function add_booking(array $payload): bool
{
    $slotDate = normalize_slot_date((string)($payload['slot_date'] ?? ''));
    $slotTime = normalize_slot_time((string)($payload['slot_time'] ?? ''));
    if (!$slotDate || !$slotTime) return false;

    if (db_enabled()) {
        $stmt = db()->prepare('
            INSERT INTO bookings (service_title, service_slug, name, email, phone, slot_date, slot_time, status)
            VALUES (:service_title, :service_slug, :name, :email, :phone, :slot_date, :slot_time, :status)
        ');
        return $stmt->execute([
            ':service_title' => (string)($payload['service_title'] ?? ''),
            ':service_slug' => (string)($payload['service_slug'] ?? ''),
            ':name' => (string)($payload['name'] ?? ''),
            ':email' => (string)($payload['email'] ?? ''),
            ':phone' => (string)($payload['phone'] ?? ''),
            ':slot_date' => $slotDate,
            ':slot_time' => $slotTime,
            ':status' => (string)($payload['status'] ?? 'pending'),
        ]);
    }

    $rows = get_bookings();
    $payload['id'] = 'bkg_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(3)), 0, 6);
    $payload['created_at'] = date('c');
    $payload['slot_date'] = $slotDate;
    $payload['slot_time'] = $slotTime;
    $payload['status'] = (string)($payload['status'] ?? 'pending');
    $rows[] = $payload;
    return save_bookings($rows);
}

function update_booking_status(string $id, string $status): bool
{
    $id = trim($id);
    $status = trim($status);
    $allowed = ['pending', 'approved', 'rejected', 'cancelled', 'booked'];
    if ($id === '' || !in_array($status, $allowed, true)) return false;

    if (db_enabled()) {
        $stmt = db()->prepare('UPDATE bookings SET status = :status WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    $rows = get_bookings();
    $updated = false;
    foreach ($rows as &$row) {
        if (!is_array($row)) continue;
        if ((string)($row['id'] ?? '') !== $id) continue;
        $row['status'] = $status;
        $updated = true;
        break;
    }
    unset($row);
    if (!$updated) return false;
    return save_bookings($rows);
}

function get_leads(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM leads ORDER BY timestamp ASC, id ASC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'timestamp' => mysql_to_iso((string)($r['timestamp'] ?? '')),
                'email' => (string)($r['email'] ?? ''),
                'page_source' => (string)($r['page_source'] ?? ''),
            ];
        }, $rows);
    }
    return read_json('leads.json');
}

function get_regulatory_updates(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM regulatory_updates ORDER BY published_at DESC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'key' => (string)($r['key'] ?? ''),
                'source' => (string)($r['source'] ?? ''),
                'source_site' => (string)($r['source_site'] ?? ''),
                'source_feed' => (string)($r['source_feed'] ?? ''),
                'tag' => (string)($r['tag'] ?? ''),
                'title' => (string)($r['title'] ?? ''),
                'url' => (string)($r['url'] ?? ''),
                'published_at' => mysql_to_iso((string)($r['published_at'] ?? '')),
                'fetched_at' => mysql_to_iso((string)($r['fetched_at'] ?? '')),
                'status' => (string)($r['status'] ?? 'published'),
                'pinned_until' => mysql_to_iso((string)($r['pinned_until'] ?? '')),
                'importance' => (string)($r['importance'] ?? ''),
                'audience' => (string)($r['audience'] ?? ''),
                'department' => (string)($r['department'] ?? ''),
                'effective_date' => (string)($r['effective_date'] ?? ''),
                'summary' => (string)($r['summary'] ?? ''),
                'action_items' => from_json_array($r['action_items_json'] ?? null),
                'related_service_slug' => (string)($r['related_service_slug'] ?? ''),
                'notes' => (string)($r['notes'] ?? ''),
                'reviewed_at' => mysql_to_iso((string)($r['reviewed_at'] ?? '')),
                'reviewed_by' => (string)($r['reviewed_by'] ?? ''),
            ];
        }, $rows);
    }
    $rows = read_json('regulatory.json', []);
    return is_array($rows) ? $rows : [];
}

function save_regulatory_updates(array $rows): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT `key` FROM regulatory_updates')->fetchAll();
            $existingKeys = array_map(fn($r) => (string)($r['key'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO regulatory_updates
                (`key`, source, source_site, source_feed, tag, title, url, published_at, fetched_at, status, pinned_until, importance, audience, department, effective_date, summary, action_items_json, related_service_slug, notes, reviewed_at, reviewed_by)
                VALUES
                (:key, :source, :source_site, :source_feed, :tag, :title, :url, :published_at, :fetched_at, :status, :pinned_until, :importance, :audience, :department, :effective_date, :summary, :action_items_json, :related_service_slug, :notes, :reviewed_at, :reviewed_by)
                ON DUPLICATE KEY UPDATE
                    source=VALUES(source),
                    source_site=VALUES(source_site),
                    source_feed=VALUES(source_feed),
                    tag=VALUES(tag),
                    title=VALUES(title),
                    url=VALUES(url),
                    published_at=VALUES(published_at),
                    fetched_at=VALUES(fetched_at),
                    status=VALUES(status),
                    pinned_until=VALUES(pinned_until),
                    importance=VALUES(importance),
                    audience=VALUES(audience),
                    department=VALUES(department),
                    effective_date=VALUES(effective_date),
                    summary=VALUES(summary),
                    action_items_json=VALUES(action_items_json),
                    related_service_slug=VALUES(related_service_slug),
                    notes=VALUES(notes),
                    reviewed_at=VALUES(reviewed_at),
                    reviewed_by=VALUES(reviewed_by)
            ');

            foreach ($rows as $u) {
                if (!is_array($u)) continue;
                $key = (string)($u['key'] ?? '');
                if ($key === '') continue;
                $keep[] = $key;
                $stmt->execute([
                    ':key' => $key,
                    ':source' => (string)($u['source'] ?? ''),
                    ':source_site' => (string)($u['source_site'] ?? ''),
                    ':source_feed' => (string)($u['source_feed'] ?? ''),
                    ':tag' => (string)($u['tag'] ?? ''),
                    ':title' => (string)($u['title'] ?? ''),
                    ':url' => (string)($u['url'] ?? ''),
                    ':published_at' => dt_to_mysql((string)($u['published_at'] ?? '')),
                    ':fetched_at' => dt_to_mysql((string)($u['fetched_at'] ?? '')),
                    ':status' => (string)($u['status'] ?? 'published'),
                    ':pinned_until' => dt_to_mysql((string)($u['pinned_until'] ?? '')),
                    ':importance' => ($u['importance'] ?? '') !== '' ? (string)$u['importance'] : null,
                    ':audience' => (string)($u['audience'] ?? ''),
                    ':department' => (string)($u['department'] ?? ''),
                    ':effective_date' => (($u['effective_date'] ?? '') !== '') ? (string)$u['effective_date'] : null,
                    ':summary' => (string)($u['summary'] ?? ''),
                    ':action_items_json' => to_json_or_null($u['action_items'] ?? []),
                    ':related_service_slug' => (string)($u['related_service_slug'] ?? ''),
                    ':notes' => (string)($u['notes'] ?? ''),
                    ':reviewed_at' => dt_to_mysql((string)($u['reviewed_at'] ?? '')),
                    ':reviewed_by' => (string)($u['reviewed_by'] ?? ''),
                ]);
            }

            $toDelete = array_diff($existingKeys, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM regulatory_updates WHERE `key` IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('regulatory.json', $rows);
}

function get_regulatory_health(): array
{
    if (db_enabled()) {
        $row = db()->query('SELECT payload_json FROM regulatory_health WHERE id=1')->fetch();
        if (is_array($row) && isset($row['payload_json'])) {
            $decoded = json_decode((string)$row['payload_json'], true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
    $rows = read_json('regulatory-health.json', []);
    return is_array($rows) ? $rows : [];
}

function save_regulatory_health(array $payload): bool
{
    if (db_enabled()) {
        $stmt = db()->prepare('INSERT INTO regulatory_health (id, payload_json) VALUES (1, :p) ON DUPLICATE KEY UPDATE payload_json=VALUES(payload_json), updated_at=CURRENT_TIMESTAMP');
        return $stmt->execute([':p' => json_encode($payload, JSON_UNESCAPED_SLASHES)]);
    }
    return save_json('regulatory-health.json', $payload);
}

function get_announcement(): array
{
    if (db_enabled()) {
        $row = db()->query('SELECT * FROM announcement ORDER BY updated_at DESC LIMIT 1')->fetch();
        if (is_array($row)) {
            return [
                'active' => !empty($row['active']),
                'id' => (string)($row['id'] ?? 'default'),
                'text' => (string)($row['text'] ?? ''),
                'link' => (string)($row['link'] ?? ''),
                'kind' => (string)($row['kind'] ?? 'info'),
            ];
        }
        return ['active' => false, 'id' => 'default', 'text' => '', 'link' => '', 'kind' => 'info'];
    }

    $row = read_json('announcement.json', [
        'active' => false,
        'id' => 'default',
        'text' => '',
        'link' => '',
        'kind' => 'info',
    ]);
    return is_array($row) ? $row : ['active' => false, 'id' => 'default', 'text' => '', 'link' => '', 'kind' => 'info'];
}

function save_announcement(array $row): bool
{
    if (db_enabled()) {
        $id = (string)($row['id'] ?? 'default');
        if ($id === '') $id = 'default';
        $stmt = db()->prepare('
            INSERT INTO announcement (id, active, text, link, kind)
            VALUES (:id, :active, :text, :link, :kind)
            ON DUPLICATE KEY UPDATE active=VALUES(active), text=VALUES(text), link=VALUES(link), kind=VALUES(kind), updated_at=CURRENT_TIMESTAMP
        ');
        return $stmt->execute([
            ':id' => $id,
            ':active' => !empty($row['active']) ? 1 : 0,
            ':text' => (string)($row['text'] ?? ''),
            ':link' => (string)($row['link'] ?? ''),
            ':kind' => (string)($row['kind'] ?? 'info'),
        ]);
    }
    return save_json('announcement.json', $row);
}

function get_site_resources(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM resources ORDER BY sort_order ASC, title ASC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'title' => (string)($r['title'] ?? ''),
                'slug' => (string)($r['slug'] ?? ''),
                'category' => (string)($r['category'] ?? ''),
                'description' => (string)($r['description'] ?? ''),
                'file_url' => (string)($r['file_url'] ?? ''),
                'file_type' => (string)($r['file_type'] ?? ''),
                'featured' => !empty($r['featured']),
                'status' => (string)($r['status'] ?? 'published'),
                'sort_order' => (int)($r['sort_order'] ?? 999),
            ];
        }, $rows);
    }
    $rows = read_json('resources.json', []);
    return is_array($rows) ? $rows : [];
}

function save_site_resources(array $rows): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT slug FROM resources')->fetchAll();
            $existingSlugs = array_map(fn($r) => (string)($r['slug'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO resources (slug, title, category, description, file_url, file_type, featured, status, sort_order)
                VALUES (:slug, :title, :category, :description, :file_url, :file_type, :featured, :status, :sort_order)
                ON DUPLICATE KEY UPDATE
                    title=VALUES(title),
                    category=VALUES(category),
                    description=VALUES(description),
                    file_url=VALUES(file_url),
                    file_type=VALUES(file_type),
                    featured=VALUES(featured),
                    status=VALUES(status),
                    sort_order=VALUES(sort_order),
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($rows as $r) {
                if (!is_array($r)) continue;
                $slug = (string)($r['slug'] ?? '');
                if ($slug === '') continue;
                $keep[] = $slug;
                $stmt->execute([
                    ':slug' => $slug,
                    ':title' => (string)($r['title'] ?? ''),
                    ':category' => (string)($r['category'] ?? ''),
                    ':description' => (string)($r['description'] ?? ''),
                    ':file_url' => (string)($r['file_url'] ?? ''),
                    ':file_type' => (string)($r['file_type'] ?? ''),
                    ':featured' => !empty($r['featured']) ? 1 : 0,
                    ':status' => (string)($r['status'] ?? 'published'),
                    ':sort_order' => (int)($r['sort_order'] ?? 999),
                ]);
            }

            $toDelete = array_diff($existingSlugs, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM resources WHERE slug IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('resources.json', $rows);
}

function get_clients(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM clients ORDER BY sort_order ASC, name ASC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'name' => (string)($r['name'] ?? ''),
                'logo' => (string)($r['logo'] ?? ''),
                'url' => (string)($r['url'] ?? ''),
                'active' => !empty($r['active']),
                'sort_order' => (int)($r['sort_order'] ?? 999),
            ];
        }, $rows);
    }
    $rows = read_json('clients.json', []);
    return is_array($rows) ? $rows : [];
}

function save_clients(array $rows): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = $pdo->query('SELECT name FROM clients')->fetchAll();
            $existingNames = array_map(fn($r) => (string)($r['name'] ?? ''), $existing);
            $keep = [];

            $stmt = $pdo->prepare('
                INSERT INTO clients (name, logo, url, active, sort_order)
                VALUES (:name, :logo, :url, :active, :sort_order)
                ON DUPLICATE KEY UPDATE
                    logo=VALUES(logo),
                    url=VALUES(url),
                    active=VALUES(active),
                    sort_order=VALUES(sort_order),
                    updated_at=CURRENT_TIMESTAMP
            ');

            foreach ($rows as $r) {
                if (!is_array($r)) continue;
                $name = (string)($r['name'] ?? '');
                if ($name === '') continue;
                $keep[] = $name;
                $stmt->execute([
                    ':name' => $name,
                    ':logo' => (string)($r['logo'] ?? ''),
                    ':url' => (string)($r['url'] ?? ''),
                    ':active' => !empty($r['active']) ? 1 : 0,
                    ':sort_order' => (int)($r['sort_order'] ?? 999),
                ]);
            }

            $toDelete = array_diff($existingNames, $keep);
            if (!empty($toDelete)) {
                $in = implode(',', array_fill(0, count($toDelete), '?'));
                $del = $pdo->prepare("DELETE FROM clients WHERE name IN ($in)");
                $del->execute(array_values($toDelete));
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('clients.json', $rows);
}

function get_subscribers(): array
{
    if (db_enabled()) {
        $rows = db()->query('SELECT * FROM subscribers ORDER BY timestamp DESC')->fetchAll();
        return array_map(function (array $r) {
            return [
                'id' => (string)($r['id'] ?? ''),
                'email' => (string)($r['email'] ?? ''),
                'frequency' => (string)($r['frequency'] ?? 'weekly'),
                'keywords' => (string)($r['keywords'] ?? ''),
                'sources' => from_json_array($r['sources_json'] ?? null),
                'active' => !empty($r['active']),
                'confirmed' => !empty($r['confirmed']),
                'timestamp' => mysql_to_iso((string)($r['timestamp'] ?? '')),
            ];
        }, $rows);
    }
    $rows = read_json('subscribers.json', []);
    return is_array($rows) ? $rows : [];
}

function save_subscribers(array $rows): bool
{
    if (db_enabled()) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM subscribers');
            $stmt = $pdo->prepare('
                INSERT INTO subscribers (id, email, frequency, keywords, sources_json, active, confirmed, timestamp)
                VALUES (:id, :email, :frequency, :keywords, :sources_json, :active, :confirmed, :timestamp)
            ');
            foreach ($rows as $r) {
                if (!is_array($r)) continue;
                $id = trim((string)($r['id'] ?? ''));
                if ($id === '') {
                    $id = sha1(strtolower((string)($r['email'] ?? '')) . '|' . (string)($r['timestamp'] ?? date('c')));
                }
                $stmt->execute([
                    ':id' => $id,
                    ':email' => (string)($r['email'] ?? ''),
                    ':frequency' => (string)($r['frequency'] ?? 'weekly'),
                    ':keywords' => (string)($r['keywords'] ?? ''),
                    ':sources_json' => to_json_or_null($r['sources'] ?? []),
                    ':active' => !empty($r['active']) ? 1 : 0,
                    ':confirmed' => !empty($r['confirmed']) ? 1 : 0,
                    ':timestamp' => dt_to_mysql((string)($r['timestamp'] ?? date('c'))) ?? date('Y-m-d H:i:s'),
                ]);
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
    return save_json('subscribers.json', $rows);
}

function add_subscriber(array $payload): bool
{
    if (db_enabled()) {
        $payload['timestamp'] = date('c');
        if (!isset($payload['id']) || trim((string)$payload['id']) === '') {
            $payload['id'] = sha1(strtolower((string)($payload['email'] ?? '')) . '|' . (string)$payload['timestamp']);
        }
        $stmt = db()->prepare('
            INSERT INTO subscribers (id, email, frequency, keywords, sources_json, active, confirmed, timestamp)
            VALUES (:id, :email, :frequency, :keywords, :sources_json, :active, :confirmed, :timestamp)
        ');
        return $stmt->execute([
            ':id' => (string)$payload['id'],
            ':email' => (string)($payload['email'] ?? ''),
            ':frequency' => (string)($payload['frequency'] ?? 'weekly'),
            ':keywords' => (string)($payload['keywords'] ?? ''),
            ':sources_json' => to_json_or_null($payload['sources'] ?? []),
            ':active' => !empty($payload['active']) ? 1 : 0,
            ':confirmed' => !empty($payload['confirmed']) ? 1 : 0,
            ':timestamp' => dt_to_mysql((string)($payload['timestamp'] ?? date('c'))) ?? date('Y-m-d H:i:s'),
        ]);
    }
    $rows = get_subscribers();
    $payload['timestamp'] = date('c');
    if (!isset($payload['id']) || trim((string)$payload['id']) === '') {
        $payload['id'] = sha1(strtolower((string)($payload['email'] ?? '')) . '|' . (string)$payload['timestamp']);
    }
    $payload['active'] = (bool)($payload['active'] ?? true);
    $rows[] = $payload;
    return save_subscribers($rows);
}

function validate_subscriber_payload(array $post): array
{
    $errors = [];

    $honeypot = trim((string)($post['company'] ?? ''));
    if ($honeypot !== '') {
        $errors[] = 'Invalid submission.';
    }

    $email = trim((string)($post['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    $frequency = trim((string)($post['frequency'] ?? 'weekly'));
    if (!in_array($frequency, ['instant', 'daily', 'weekly'], true)) {
        $frequency = 'weekly';
    }

    $keywords = trim((string)($post['keywords'] ?? ''));
    $sources = $post['sources'] ?? [];
    if (!is_array($sources)) {
        $sources = [];
    }
    $sources = array_values(array_filter(array_map('trim', $sources), fn($v) => $v !== ''));
    $sources = array_values(array_unique($sources));

    $payload = [
        'email' => $email,
        'frequency' => $frequency,
        'keywords' => $keywords,
        'sources' => $sources,
        'active' => true,
        'confirmed' => false,
    ];

    return [
        'ok' => empty($errors),
        'errors' => $errors,
        'payload' => $payload,
    ];
}

function add_lead(array $payload): bool
{
    if (db_enabled()) {
        $stmt = db()->prepare('INSERT INTO leads (email, page_source, timestamp) VALUES (:email, :page_source, CURRENT_TIMESTAMP)');
        return $stmt->execute([
            ':email' => (string)($payload['email'] ?? ''),
            ':page_source' => (string)($payload['page_source'] ?? ''),
        ]);
    }
    $leads = get_leads();
    $payload['timestamp'] = date('c');
    $leads[] = $payload;
    return save_json('leads.json', $leads);
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($ip) ? $ip : '';
}

function rate_limit_hit(string $key, int $maxPerHour = 8): bool
{
    $key = trim($key);
    if ($key === '') {
        return false;
    }

    $data = read_json('rate-limit.json', []);
    if (!is_array($data)) {
        $data = [];
    }

    $now = time();
    $window = 3600;
    $bucket = $data[$key] ?? ['count' => 0, 'start' => $now];
    $start = (int)($bucket['start'] ?? $now);
    $count = (int)($bucket['count'] ?? 0);

    if (($now - $start) > $window) {
        $start = $now;
        $count = 0;
    }

    $count++;
    $data[$key] = ['count' => $count, 'start' => $start];
    save_json('rate-limit.json', $data);

    return $count > $maxPerHour;
}

function add_inquiry(array $payload): bool
{
    if (db_enabled()) {
        $stmt = db()->prepare('
            INSERT INTO inquiries (name, email, phone, message, selected_service, preferred_slot, page_source, timestamp)
            VALUES (:name, :email, :phone, :message, :selected_service, :preferred_slot, :page_source, CURRENT_TIMESTAMP)
        ');
        return $stmt->execute([
            ':name' => (string)($payload['name'] ?? ''),
            ':email' => (string)($payload['email'] ?? ''),
            ':phone' => (string)($payload['phone'] ?? ''),
            ':message' => (string)($payload['message'] ?? ''),
            ':selected_service' => (string)($payload['selected_service'] ?? ''),
            ':preferred_slot' => (string)($payload['preferred_slot'] ?? ''),
            ':page_source' => (string)($payload['page_source'] ?? ''),
        ]);
    }
    $inquiries = get_inquiries();
    $payload['timestamp'] = date('c');
    $inquiries[] = $payload;
    return save_json('inquiries.json', $inquiries);
}

function validate_inquiry_payload(array $post, string $pageSource): array
{
    $errors = [];

    // Honeypot (bots fill hidden fields)
    $honeypot = trim((string)($post['company'] ?? ''));
    if ($honeypot !== '') {
        $errors[] = 'Invalid submission.';
    }

    $name = trim((string)($post['name'] ?? ''));
    $email = trim((string)($post['email'] ?? ''));
    $phone = trim((string)($post['phone'] ?? ''));
    $message = trim((string)($post['message'] ?? ''));
    $selectedService = trim((string)($post['selected_service'] ?? ''));
    $preferredSlot = trim((string)($post['preferred_slot'] ?? ''));

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if ($message === '') {
        $errors[] = 'Message is required.';
    }

    return [
        'ok' => empty($errors),
        'errors' => $errors,
        'payload' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'selected_service' => $selectedService,
            'preferred_slot' => $preferredSlot,
            'page_source' => $pageSource,
        ],
    ];
}

function validate_lead_payload(array $post, string $pageSource): array
{
    $errors = [];
    $honeypot = trim((string)($post['company'] ?? ''));
    if ($honeypot !== '') {
        $errors[] = 'Invalid submission.';
    }

    $email = trim((string)($post['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    return [
        'ok' => empty($errors),
        'errors' => $errors,
        'payload' => [
            'email' => $email,
            'page_source' => $pageSource,
        ],
    ];
}

function process_inquiry(string $pageSource, array &$errors = []): ?string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['form_type'] ?? '') !== 'inquiry') {
        return null;
    }

    if (rate_limit_hit('inquiry:' . client_ip(), 10)) {
        $errors[] = 'Too many submissions. Please try again later.';
        return null;
    }

    $result = validate_inquiry_payload($_POST, $pageSource);
    $errors = $result['errors'];
    if (!$result['ok']) {
        return null;
    }

    add_inquiry($result['payload']);

    return 'Thanks for reaching out. We will get back to you shortly.';
}

function process_lead(string $pageSource, array &$errors = []): ?string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['form_type'] ?? '') !== 'lead') {
        return null;
    }

    if (rate_limit_hit('lead:' . client_ip(), 12)) {
        $errors[] = 'Too many submissions. Please try again later.';
        return null;
    }

    $result = validate_lead_payload($_POST, $pageSource);
    $errors = $result['errors'];
    if (!$result['ok']) {
        return null;
    }

    add_lead($result['payload']);
    return 'Thanks. Your download is ready.';
}

function get_published_blogs(): array
{
    $all = get_blogs();
    return array_values(array_filter($all, fn($b) => ($b['status'] ?? 'published') === 'published'));
}

function estimate_reading_time_minutes(string $text): int
{
    $words = str_word_count(strip_tags($text));
    return max(1, (int)ceil($words / 220));
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date('M j, Y', $timestamp) : $date;
}
