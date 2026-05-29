<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require __DIR__ . '/../inc/data.php';

if (!db_enabled()) {
    fwrite(STDERR, "DB is not enabled. Create inc/db-config.local.php and set $DB_ENABLED=true (or set env JSA_DB_ENABLED=1).\n");
    exit(1);
}

echo "Migrating JSON -> MySQL...\n";

// Read from JSON files directly (not DB routed helpers)
$services = read_json('services.json', []);
$blogs = read_json('blogs.json', []);
$team = read_json('team.json', []);
$caseStudies = read_json('case-studies.json', []);
$careers = read_json('careers.json', []);
$resources = read_json('resources.json', []);
$clients = read_json('clients.json', []);
$announcement = read_json('announcement.json', []);
$subscribers = read_json('subscribers.json', []);
$regUpdates = read_json('regulatory.json', []);
$regHealth = read_json('regulatory-health.json', []);
$inquiries = read_json('inquiries.json', []);
$leads = read_json('leads.json', []);

echo "- services: " . (is_array($services) ? count($services) : 0) . "\n";
echo "- blogs: " . (is_array($blogs) ? count($blogs) : 0) . "\n";
echo "- team: " . (is_array($team) ? count($team) : 0) . "\n";
echo "- case-studies: " . (is_array($caseStudies) ? count($caseStudies) : 0) . "\n";
echo "- careers: " . (is_array($careers) ? count($careers) : 0) . "\n";
echo "- resources: " . (is_array($resources) ? count($resources) : 0) . "\n";
echo "- clients: " . (is_array($clients) ? count($clients) : 0) . "\n";
echo "- subscribers: " . (is_array($subscribers) ? count($subscribers) : 0) . "\n";
echo "- regulatory: " . (is_array($regUpdates) ? count($regUpdates) : 0) . "\n";
echo "- inquiries: " . (is_array($inquiries) ? count($inquiries) : 0) . "\n";
echo "- leads: " . (is_array($leads) ? count($leads) : 0) . "\n";

// Replace-all style migrations for content tables
save_services(is_array($services) ? $services : []);
save_blogs(is_array($blogs) ? $blogs : []);
save_team(is_array($team) ? $team : []);
save_case_studies(is_array($caseStudies) ? $caseStudies : []);
save_careers(is_array($careers) ? $careers : []);
save_site_resources(is_array($resources) ? $resources : []);
save_clients(is_array($clients) ? $clients : []);
if (is_array($announcement) && !empty($announcement)) {
    save_announcement($announcement);
}

// Subscribers (overwrite)
save_subscribers(is_array($subscribers) ? $subscribers : []);

// Regulatory
save_regulatory_updates(is_array($regUpdates) ? $regUpdates : []);
if (is_array($regHealth) && !empty($regHealth)) {
    save_regulatory_health($regHealth);
}

// Inquiries/leads (append)
// We insert rows one by one; there is no "save_all" because site uses append behavior.
if (is_array($inquiries)) {
    foreach ($inquiries as $inq) {
        if (!is_array($inq)) continue;
        add_inquiry($inq);
    }
}
if (is_array($leads)) {
    foreach ($leads as $lead) {
        if (!is_array($lead)) continue;
        add_lead($lead);
    }
}

echo "Done.\n";

