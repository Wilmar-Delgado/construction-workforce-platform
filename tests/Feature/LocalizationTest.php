<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_translation_catalogs_have_matching_key_structures(): void
    {
        $englishKeys = $this->translationKeys(require lang_path('en/app.php'));

        foreach (['fr', 'es'] as $locale) {
            $this->assertSame(
                $englishKeys,
                $this->translationKeys(require lang_path("{$locale}/app.php")),
                "The {$locale} translation catalog must match the English key structure."
            );
        }
    }

    public function test_french_and_spanish_catalogs_are_standalone_files(): void
    {
        foreach (['fr', 'es'] as $locale) {
            $contents = file_get_contents(lang_path("{$locale}/app.php"));

            $this->assertNotFalse($contents);
            $this->assertDoesNotMatchRegularExpression(
                '/(?:require|include)(?:_once)?\\s*\\(?[^;]*["\'](?:\\.\\.\\/)?en\\/app\\.php["\']/',
                $contents,
                "The {$locale} catalog must not inherit English translations at runtime."
            );
            $this->assertDoesNotMatchRegularExpression(
                '/array_(?:replace|merge)(?:_recursive)?\\s*\\(/',
                $contents,
                "The {$locale} catalog must be a standalone translation array."
            );
        }
    }

    public function test_static_application_translation_references_resolve_in_the_catalog(): void
    {
        $translations = require lang_path('en/app.php');

        foreach ($this->staticTranslationReferences() as $reference) {
            $this->assertNotNull(
                $this->translationValue($translations, $reference['key']),
                "Missing translation key [{$reference['key']}] referenced by {$reference['file']}."
            );
        }
    }

    public function test_project_translation_namespaces_do_not_use_legacy_domain_names(): void
    {
        $legacyTerm = strtolower('MI' . 'SSION');
        $legacyPlural = "{$legacyTerm}s";

        $staleNamespaces = [
            "find_{$legacyPlural}_page",
            "find_{$legacyPlural}",
            "{$legacyPlural}_page",
            "{$legacyTerm}_management_page",
            "{$legacyTerm}_management",
            "{$legacyTerm}_card",
            "{$legacyTerm}_details",
            "{$legacyTerm}_request",
        ];

        foreach ([resource_path('js'), resource_path('views'), app_path(), base_path('routes')] as $directory) {
            foreach ($this->sourceFiles($directory) as $file) {
                $contents = file_get_contents($file->getPathname());

                $this->assertNotFalse($contents);

                foreach ($staleNamespaces as $namespace) {
                    $this->assertStringNotContainsString(
                        $namespace,
                        $contents,
                        "Stale translation namespace [{$namespace}] found in {$file->getPathname()}."
                    );
                }
            }
        }
    }

    public function test_spanish_project_display_values_use_project_terminology(): void
    {
        $spanishCatalog = file_get_contents(lang_path('es/app.php'));
        $legacyTerm = strtolower('MI' . 'SI' . 'ÓN');

        $this->assertNotFalse($spanishCatalog);
        $this->assertDoesNotMatchRegularExpression(
            "/\\b{$legacyTerm}(?:es)?\\b/ui",
            $spanishCatalog
        );
    }

    public function test_settings_accepts_each_supported_language_and_uses_it_on_the_next_request(): void
    {
        $user = User::factory()->create(['language' => 'en']);

        foreach (['en', 'fr', 'es'] as $locale) {
            $this->actingAs($user)
                ->postJson(route('settings.notifications.update'), [
                    'email' => true,
                    'sms' => false,
                    'projectAlerts' => true,
                    'language' => $locale,
                    'timezone' => 'UTC',
                ])
                ->assertOk()
                ->assertJsonPath('user.language', $locale);

            $this->actingAs($user->fresh())
                ->get(route('settings'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Settings')
                    ->where('locale', $locale));
        }
    }

    public function test_settings_rejects_an_unsupported_language_without_changing_preferences(): void
    {
        $user = User::factory()->create(['language' => 'en']);

        $this->actingAs($user)
            ->postJson(route('settings.notifications.update'), [
                'email' => true,
                'sms' => false,
                'projectAlerts' => true,
                'language' => 'de',
                'timezone' => 'UTC',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('language');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'language' => 'en',
        ]);
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<int, string>
     */
    private function translationKeys(array $translations, string $prefix = ''): array
    {
        $keys = [];

        foreach ($translations as $key => $value) {
            $path = $prefix === '' ? $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $keys = [...$keys, ...$this->translationKeys($value, $path)];
                continue;
            }

            $keys[] = $path;
        }

        sort($keys);

        return $keys;
    }

    /**
     * @return array<int, array{file: string, key: string}>
     */
    private function staticTranslationReferences(): array
    {
        $references = [];

        foreach ([resource_path('js'), app_path(), resource_path('views'), base_path('routes')] as $directory) {
            foreach ($this->sourceFiles($directory) as $file) {

                $contents = file_get_contents($file->getPathname());

                if ($contents === false) {
                    continue;
                }

                preg_match_all(
                    "/\\bt\\(\\s*['\"]([A-Za-z0-9_.-]+)['\"]\\s*(?=,|\\))/",
                    $contents,
                    $frontendMatches
                );

                foreach ($frontendMatches[1] as $key) {
                    $references[] = [
                        'file' => $file->getPathname(),
                        'key' => $key,
                    ];
                }

                preg_match_all(
                    "/\\b(?:__|trans|@lang)\\(\\s*['\"]app\\.([A-Za-z0-9_.-]+)['\"]\\s*(?=,|\\))/",
                    $contents,
                    $backendMatches
                );

                foreach ($backendMatches[1] as $key) {
                    $references[] = [
                        'file' => $file->getPathname(),
                        'key' => $key,
                    ];
                }
            }
        }

        return $references;
    }

    /**
     * @return array<int, \SplFileInfo>
     */
    private function sourceFiles(string $directory): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];

        foreach ($iterator as $file) {
            if (in_array($file->getExtension(), ['js', 'vue', 'php'], true)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $translations
     */
    private function translationValue(array $translations, string $key): mixed
    {
        $value = $translations;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
