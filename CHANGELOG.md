# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.5.0] - 2026-07-18

### Added
- Multi-collection source guard via `config/internal-links.php` key `collections`.
- Support for injecting internal links into project overview content when the host application applies the modifier there.
- Backward-compatible fallback to the legacy `blog_collection` key when `collections` is null, empty, or not a non-empty array.
- Installer and documentation updates for source collections beyond blog entries.
- Regression coverage for `collections` priority and legacy `blog_collection` fallback.

### Changed
- Blueprint instructions no longer describe auto-linking as blog-only.
- Package metadata now describes configured source collections instead of only blog content.

## [0.4.0] - 2026-07-10

### Added
- Keyword rows now support several comma-separated word or phrase variants in a single **Word / phrase** field.
- `ApplyInternalLinks::splitKeywordVariants()` helper for testing and documenting comma-splitting behavior.
- Bulgarian (`bg`) option in the keyword locale selector to match the current multisite setup.

### Changed
- A keyword row with multiple variants keeps the existing deduplication behavior: the target URL is linked at most once per page request, and variants are tried in the order entered.
- Blueprint instructions now explain comma-separated variants and the literal-comma limitation.
- `VERSION.md` synchronized with the package version.

## [0.3.0] - 2026-06-22

### Added
- `config/internal-links.php` — global configuration file with `blog_collection` and `admin_site` keys
- `InstallCommand` auto-detects blog collection by scanning collection handles for keywords (`blog`, `post`, `article`, `news`, `journal`, `entry`)
- On multisite installs, `InstallCommand` asks which site is used to manage content in the CP
- Detected values are written automatically into the published config file
- `vendor:publish --tag=internal-links-config` for config-only publish

### Changed
- `blog_collection` moved from a per-entry CP field to a global config key — set once, applies everywhere
- `admin_site` replaces the hardcoded `'pl'` in the modifier query — now read from config
- Blueprint simplified: `blog_collection` field removed from CP
- Composer package renamed to `5k18a/blog-internal-links`
- CP blueprint and collection labels translated to English for Statamic Marketplace compatibility

### Removed
- `blog_collection` entries picker field from blueprint (replaced by config)

## [0.1.0] - 2026-06-22

### Added
- Antlers modifier `apply_internal_links` for automatic keyword substitution in HTML/Bard content
- `LinkableContentParser` — HTML-aware keyword replacer that protects headings, existing links, images, figures, iframes, and WordPress embed comments from substitution
- `internal_links` Statamic collection with blueprint for managing keyword → entry mappings
- Per-locale keyword support: each keyword in the replicator has an optional `locale` field; empty = all languages, set = specific language only
- Target entry URL resolves automatically to the correct language version via Statamic's native multisite URL resolution (`Entry::in($site)`)
- Deduplication: each target URL is linked at most once per page request
- `php artisan internal-links:install` command — creates collection YAML, publishes blueprint, refreshes stache
- `vendor:publish --tag=internal-links-blueprints` for blueprint-only publish
- `vendor:publish --tag=internal-links-collection` for collection-only publish
- Blueprint fields: `target_entry`, `keywords` (replicator with `keyword` + `locale`), `weight`, `nofollow`, `open_in_new_window`, `enabled`
