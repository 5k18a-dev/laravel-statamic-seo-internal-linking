# Roadmap

## Variant A — MVP (current)

- [x] Native Statamic `internal_links` collection as storage
- [x] Keyword → target entry mapping
- [x] Antlers modifier `apply_internal_links`
- [x] HTML-aware parser protecting headings, existing links, media, and embed comments
- [x] Per-locale keywords in a single entry (no multisite propagation required)
- [x] Target URL resolves to correct language version automatically
- [x] Deduplication: each target URL linked at most once per page
- [x] `php artisan internal-links:install` command
- [x] Real-time substitution at render time
- [x] Multi-collection source guard via `collections` config with legacy `blog_collection` fallback

## Variant B — Production-ready

- [ ] Global settings (per-site limits, exclusion rules)
- [ ] Per-entry exclusions (exclude specific pages from being linked)
- [ ] Laravel Scheduler / cron for pre-computation and caching
- [ ] Performance cache for large keyword sets and long content
- [ ] Expanded test coverage

## Variant C — Full feature parity

- [ ] Custom CP panel for link overview
- [ ] Database logs for executed auto-links
- [ ] Auto-suggestions based on slugs and content analysis
- [ ] CSV import / export
- [ ] Packagist release
