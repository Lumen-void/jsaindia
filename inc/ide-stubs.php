<?php
/**
 * IDE-only stubs for static analyzers that do not index `inc/data.php` reliably.
 *
 * This file is NOT included by the application at runtime.
 * It exists to eliminate "Call to unknown function" diagnostics in editors.
 */

if (false) {
    // Core helpers
    function h(string $value): string {}
    function slugify(string $text): string {}
    function format_date(?string $date): string {}
    function estimate_reading_time_minutes(string $text): int {}

    // Content getters
    function get_services(): array {}
    function get_service_by_slug(string $slug): ?array {}
    function get_blogs(): array {}
    function get_published_blogs(): array {}
    function get_blog_by_slug(string $slug, bool $includeDrafts = false): ?array {}
    function get_team(): array {}
    function get_case_studies(): array {}
    function get_careers(): array {}
    function get_career_by_slug(string $slug): ?array {}
    function get_site_resources(): array {}
    function get_clients(): array {}
    function get_regulatory_updates(): array {}
    function get_regulatory_health(): array {}
    function get_announcement(): array {}

    // Form handlers
    function process_inquiry(string $pageSource, array &$errors = []): ?string {}
    function process_lead(string $pageSource, array &$errors = []): ?string {}

    // Defaults
    function default_process_steps(): array {}
    function default_why_choose_us(): array {}
}

