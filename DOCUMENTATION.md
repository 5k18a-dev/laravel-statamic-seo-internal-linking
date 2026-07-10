# Blog Internal Linking — Documentation

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Creating Internal Links](#creating-internal-links)
- [Applying the Modifier](#applying-the-modifier)
- [Multilingual Sites](#multilingual-sites)
- [How Matching Works](#how-matching-works)
- [Blueprint Reference](#blueprint-reference)
- [Troubleshooting](#troubleshooting)

---

## Installation

### Requirements

- PHP 8.2+
- Laravel 11+
- Statamic 6+

### Via Composer (recommended)

```bash
composer require 5k18a/blog-internal-links
php artisan internal-links:install
```

The install command:
1. Publishes `config/internal-links.php`
2. Creates `content/collections/internal_links.yaml`
3. Publishes the blueprint to `resources/blueprints/collections/internal_links/`
4. **Auto-detects** your blog collection by scanning collection handles for keywords like `blog`, `post`, `articles`, `news`
5. On a multisite install, asks which site you use to manage content in the CP
6. Writes the detected values into `config/internal-links.php`
7. Runs `php artisan statamic:stache:refresh`

### Manual install

If you prefer to control each step:

```bash
composer require 5k18a/blog-internal-links
php artisan vendor:publish --tag=internal-links-config
php artisan vendor:publish --tag=internal-links-collection
php artisan vendor:publish --tag=internal-links-blueprints
php artisan statamic:stache:refresh
```

---

## Configuration

### Collection site

By default the addon creates the `internal_links` collection scoped to a single site (`pl`). If your admin language is different, edit `content/collections/internal_links.yaml` after install:

```yaml
title: 'Blog Internal Linking'
revisions: false
sites:
  - en   # change to your admin site handle
propagate: false
```

The collection should always be scoped to **one site** — your admin/content language. Keywords for other languages are managed within each entry using the `locale` field.

### Blog collection

The blog collection is configured once in `config/internal-links.php` (created automatically by the install command):

```php
return [
    'blog_collection' => 'blog',  // handle of your blog collection
    'admin_site'      => 'en',    // site used to manage internal_links in CP
];
```

The modifier only processes content on entries that belong to `blog_collection` — it silently skips all other pages. If `blog_collection` is empty or `null`, the guard is disabled and the modifier runs everywhere it is placed.

### Collections for target entries

The blueprint's `target_entry` picker defaults to `pages`, `services`, and `projects`. To add more collections, publish the blueprint and edit `resources/blueprints/collections/internal_links/internal_link.yaml`:

```yaml
handle: target_entry
field:
  type: entries
  collections:
    - pages
    - services
    - projects
    - blog       # add any collection here
```

---

## Creating Internal Links

Go to **CP → Collections → Blog Internal Linking** and create a new entry.

### Fields

| Field | Description |
|---|---|
| **Name** | Admin label only — not displayed on the site. E.g. `Coral Reef → Decorative Aquariums page` |
| **Target page** | The entry to link to. URL resolves to the correct language automatically. |
| **Keywords** | One or more keyword rows (see below). |
| **Priority (weight)** | Higher number = processed first. Useful when two entries share similar keywords. Default: `0`. |
| **nofollow** | Adds `rel="nofollow"` to generated links. |
| **Open in new tab** | Adds `target="_blank" rel="noopener"`. |
| **Active** | Disable to temporarily stop processing this entry without deleting it. |

### Adding keywords

Each row in the **Keywords** replicator has:

- **Word / phrase** — the exact text to match, e.g. `coral reef`, `artificial rocks`. Case-insensitive. You can enter several word or phrase variants in one field by separating them with commas, e.g. `artificial rock, artificial rocks, rockwork`.
- **Language** — optional. Leave empty to match on all language versions of your site. Set to a specific locale (e.g. `en`) to match only on English pages. When one row contains several comma-separated variants, the language setting applies to all variants in that row.

Comma-separated variants are useful for inflected languages where several forms should link to the same target page:

```text
sztuczne skały, sztucznych skał, sztucznymi skałami
```

The variants behave like separate keyword rows that share the same language and target entry. They are tried in the order entered, and once one variant creates a link for that internal-link entry, the remaining variants in that row are skipped for the current page request.

---

## Applying the Modifier

The modifier `apply_internal_links` is applied in your Antlers templates wherever you render content that should contain auto-links.

### Bard field (blog posts)

Inside a Bard loop, apply it to the `text` variable in the `else` branch (plain paragraph/text blocks):

```antlers
{{ content }}
    {{ if type == "quote_section" }}
        {{-- your quote markup --}}
    {{ elseif type == "image_section" }}
        {{-- your image markup --}}
    {{ else }}
        {{ text | apply_internal_links }}
    {{ /if }}
{{ /content }}
```

### Free text / WYSIWYG blocks

```antlers
{{ free_text_content | apply_internal_links }}
{{ wysiwyg_html | apply_internal_links }}
```

### Any HTML field

The modifier accepts any string or HTML value:

```antlers
{{ description | apply_internal_links }}
```

---

## Multilingual Sites

All keyword → page mappings live in a **single collection** (your admin language). You do not need separate entries per language.

For each keyword row, the **Language** field controls which site locale it applies to:

| Keyword | Language | Applied on |
|---|---|---|
| `coral reef` | `en` | English pages only |
| `rafa koralowa` | `pl` | Polish pages only |
| `Korallenriff` | `de` | German pages only |
| `reef` | *(empty)* | All languages |

The **Target page** field is set once. The modifier calls `Entry::in($site)` internally to resolve the URL to the correct language version of that page — no extra configuration needed.

---

## How Matching Works

- **Case-insensitive** — `Coral Reef`, `coral reef`, and `CORAL REEF` all match.
- **Unicode word boundaries** — partial matches inside words are avoided. `reef` will not match inside `reefing`.
- **Protected zones** — the following are never modified:
  - Headings (`<h1>`–`<h6>`)
  - Existing links (`<a>`)
  - Images (`<img>`)
  - Figures (`<figure>`)
  - Iframes (`<iframe>`)
  - WordPress embed comments
- **Deduplication** — each target URL is linked **at most once per page request**. If `coral reef` and `reef` both point to the same page, only the first match wins.
- **Priority** — entries are processed in descending `weight` order. Higher weight = matched first.
- **Comma-separated variants** — a keyword row can contain variants separated by commas. Variants are trimmed, empty variants are ignored, and they are tested in the order entered.
- **Max one link per internal-link entry per page** — after any keyword or comma-separated variant successfully creates a link, that entry is done for the current page.
- **Literal commas are not supported inside keywords** — a comma always separates variants. If your phrase must contain a comma as part of the exact text, it cannot be represented as one literal keyword in this version.

---

## Blueprint Reference

Full list of fields in `resources/blueprints/collections/internal_links/internal_link.yaml`:

```yaml
- handle: title          # text, required — admin label
- handle: target_entry   # entries picker, max 1, required
- handle: keywords         # replicator
    - handle: keyword    # text, required
    - handle: locale     # select (optional) — pl, en, de, fr, es, it, nl, sv, no, da, lv, cs, bg
- handle: weight         # integer, default 0
- handle: nofollow       # toggle, default false
- handle: open_in_new_window  # toggle, default false
- handle: enabled        # toggle, default true
```

---

## Troubleshooting

### Links are not appearing

1. Check that `config/internal-links.php` exists and `blog_collection` matches your blog collection handle exactly (e.g. `blog`, not `Blog`).
2. Check that the entry in the `Blog Internal Linking` collection has **Active** set to `true`.
3. Verify the keyword exists verbatim in the rendered HTML (check page source, not browser inspector which may alter whitespace).
4. Confirm the modifier is applied in the correct template and branch of the Bard loop.
5. Check that the target entry has a published URL for the current site locale.

### Links appear on some languages but not others

The **Language** field on a keyword row must match the site handle (e.g. `en`, `de`), not the locale code (e.g. `en_US`). Leave it empty to apply to all languages.

### The same page is linked multiple times

This should not happen — the addon deduplicates per target URL per request. If it occurs, confirm you are not applying the modifier twice in the same template (e.g. in both the partial and the parent template).

### A keyword inside a heading is not linked

This is intentional — headings are protected to preserve SEO structure and avoid breaking heading semantics.

### After install nothing shows in CP

Run `php artisan statamic:stache:refresh`. If the collection still does not appear, verify that `content/collections/internal_links.yaml` was created and that its `sites` list contains your current site handle.
