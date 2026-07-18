<?php

namespace Skalisty\InternalLinks\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;

class InstallCommand extends Command
{
    protected $signature = 'internal-links:install';

    protected $description = 'Install the Blog Internal Linking addon (collection + blueprint + config)';

    public function handle(): int
    {
        $this->publishConfig();
        $this->publishCollection();
        $this->publishBlueprint();

        $blogCollection = $this->detectBlogCollection();
        $adminSite = $this->detectAdminSite();

        $this->writeConfig($blogCollection, $adminSite);

        $this->call('statamic:stache:refresh');

        $this->components->info('Blog Internal Linking installed successfully.');
        $this->line('');
        $this->line('  Configuration written to <fg=cyan>config/internal-links.php</>');
        $this->line('  Primary source collection: <fg=yellow>'.$blogCollection.'</>');
        $this->line('  Admin site:                <fg=yellow>'.$adminSite.'</>');
        $this->line('');
        $this->line('  Next step — add the modifier to the content fields you want to auto-link:');
        $this->line('  <fg=green>{{ text | apply_internal_links }}</>');
        $this->line('  To enable more source collections, edit <fg=cyan>internal-links.collections</>.');
        $this->line('');
        $this->line('  See <fg=cyan>DOCUMENTATION.md</> for full setup instructions.');

        return self::SUCCESS;
    }

    private function detectBlogCollection(): string
    {
        $blogKeywords = ['blog', 'post', 'article', 'news', 'journal', 'entry', 'entries'];

        $candidates = Collection::all()
            ->map(fn ($col) => $col->handle())
            ->filter(function (string $handle) use ($blogKeywords) {
                foreach ($blogKeywords as $keyword) {
                    if (str_contains(strtolower($handle), $keyword)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        if ($candidates->count() === 1) {
            $detected = $candidates->first();
            $this->components->info("Detected blog collection: <fg=yellow>{$detected}</>");

            return $detected;
        }

        if ($candidates->count() > 1) {
            $this->components->warn('Multiple possible blog collections found: '.$candidates->implode(', '));
        }

        $all = Collection::all()->map(fn ($col) => $col->handle())->sort()->values()->all();
        $default = $candidates->first() ?? 'blog';

        return $this->choice(
            'Which collection contains your blog posts?',
            $all ?: ['blog'],
            $default
        );
    }

    private function detectAdminSite(): string
    {
        $sites = Site::all();

        if ($sites->count() === 1) {
            return $sites->first()->handle();
        }

        $handles = $sites->map(fn ($s) => $s->handle())->values()->all();
        $default = Site::default()->handle();

        return $this->choice(
            'Which site do you use to manage content in the CP (admin site)?',
            $handles,
            $default
        );
    }

    private function publishConfig(): void
    {
        $target = config_path('internal-links.php');

        if (File::exists($target)) {
            $this->components->warn('Config already exists, skipping: config/internal-links.php');

            return;
        }

        File::ensureDirectoryExists(dirname($target));
        File::copy(__DIR__.'/../../config/internal-links.php', $target);

        $this->components->info('Created: config/internal-links.php');
    }

    private function publishCollection(): void
    {
        $target = base_path('content/collections/internal_links.yaml');

        if (File::exists($target)) {
            $this->components->warn('Collection already exists, skipping: content/collections/internal_links.yaml');

            return;
        }

        File::ensureDirectoryExists(dirname($target));
        File::copy(__DIR__.'/../../resources/collections/internal_links.yaml', $target);

        $this->components->info('Created: content/collections/internal_links.yaml');
    }

    private function publishBlueprint(): void
    {
        $target = resource_path('blueprints/collections/internal_links/internal_link.yaml');

        if (File::exists($target)) {
            $this->components->warn('Blueprint already exists, skipping: resources/blueprints/collections/internal_links/internal_link.yaml');

            return;
        }

        File::ensureDirectoryExists(dirname($target));
        File::copy(
            __DIR__.'/../../resources/blueprints/collections/internal_links/internal_link.yaml',
            $target
        );

        $this->components->info('Created: resources/blueprints/collections/internal_links/internal_link.yaml');
    }

    private function writeConfig(string $blogCollection, string $adminSite): void
    {
        $target = config_path('internal-links.php');

        $contents = File::get($target);
        $contents = $this->replaceOrInsertCollections($contents, $blogCollection);
        $contents = preg_replace(
            "/'blog_collection'\s*=>\s*'[^']*'/",
            "'blog_collection' => '{$blogCollection}'",
            $contents
        );
        $contents = preg_replace(
            "/'admin_site'\s*=>\s*'[^']*'/",
            "'admin_site' => '{$adminSite}'",
            $contents
        );

        File::put($target, $contents);
    }

    private function replaceOrInsertCollections(string $contents, string $blogCollection): string
    {
        $collectionsLine = "'collections' => ['".$this->escapeConfigValue($blogCollection)."']";

        if (preg_match("/'collections'\s*=>/", $contents)) {
            return preg_replace(
                "/'collections'\s*=>\s*(?:null|\[[^\]]*\])/",
                $collectionsLine,
                $contents
            ) ?? $contents;
        }

        return preg_replace(
            "/('blog_collection'\s*=>)/",
            $collectionsLine.",\n\n    $1",
            $contents
        ) ?? $contents;
    }

    private function escapeConfigValue(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }
}
