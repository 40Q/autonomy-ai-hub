<?php

namespace FortyQ\AutonomyAiHub;

use Illuminate\Support\ServiceProvider;

class AutonomyAiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/autonomy-ai.php', 'autonomy-ai');

        $this->publishes([
            __DIR__ . '/../config/autonomy-ai.php' => $this->app->configPath('autonomy-ai.php'),
        ], 'autonomy-ai-config');
    }

    public function boot(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_menu', [$this, 'repositionSeoAssistantMenu'], 99);
        add_filter('option_40q_seo_assistant_settings', [$this, 'filterSeoAssistantSettings']);

        $this->applyPackageOverrides();
    }

    public function registerMenu(): void
    {
        $config = $this->app->make('config')->get('autonomy-ai', []);
        $menuTitle = $config['menu_title'] ?? '40Q Autonomy AI';
        $capability = $config['capability'] ?? 'manage_options';
        $position = $config['menu_position'] ?? 56;

        add_menu_page(
            $menuTitle,
            $menuTitle,
            $capability,
            '40q-autonomy-ai',
            [$this, 'renderDashboard'],
            'dashicons-lightbulb',
            $position
        );

        add_submenu_page(
            '40q-autonomy-ai',
            __('Overview', '40q-autonomy-ai'),
            __('Overview', '40q-autonomy-ai'),
            $capability,
            '40q-autonomy-ai',
            [$this, 'renderDashboard']
        );

        add_submenu_page(
            '40q-autonomy-ai',
            __('General Settings', '40q-autonomy-ai'),
            __('General Settings', '40q-autonomy-ai'),
            $capability,
            '40q-autonomy-ai-settings',
            [$this, 'renderSettings']
        );

        // Add other packages except media-alt and SEO (handled above).
        foreach ($config['packages'] ?? [] as $package) {
            $slug = $package['slug'] ?? null;
            $title = $package['title'] ?? null;

            if (!$slug || !$title) {
                continue;
            }

            if (in_array($slug, ['seo-assistant', 'media-alt-suggester'], true)) {
                continue;
            }

            add_submenu_page(
                '40q-autonomy-ai',
                $title,
                $title,
                $capability,
                '40q-autonomy-ai-' . sanitize_title($slug),
                fn () => $this->renderPackage($package)
            );
        }

        // Media Alt Suggester at the bottom for clarity.
        foreach ($config['packages'] ?? [] as $package) {
            if (($package['slug'] ?? null) !== 'media-alt-suggester') {
                continue;
            }

            $title = $package['title'] ?? null;
            if (!$title) {
                continue;
            }

            add_submenu_page(
                '40q-autonomy-ai',
                $title,
                $title,
                $capability,
                '40q-autonomy-ai-' . sanitize_title($package['slug']),
                fn () => $this->renderPackage($package)
            );
        }
    }

    public function enqueueAssets(): void
    {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->base, '40q-autonomy-ai') === false) {
            return;
        }

        wp_register_style('40q-autonomy-ai-admin', false, [], '1.0.0');
        wp_add_inline_style('40q-autonomy-ai-admin', $this->styles());
        wp_enqueue_style('40q-autonomy-ai-admin');
    }

    public function renderSeoAssistantSettings(): void
    {
        if (class_exists('FortyQ\\SeoAssistant\\Admin\\SettingsPage')) {
            $this->app->make(\FortyQ\SeoAssistant\Admin\SettingsPage::class)->renderPage();
        } else {
            echo '<div class="wrap"><p>' . esc_html__('SEO Assistant is not active.', '40q-autonomy-ai') . '</p></div>';
        }
    }

    public function repositionSeoAssistantMenu(): void
    {
        // Hide the old Settings > SEO Assistant entry once the hub subpage exists.
        if (class_exists('FortyQ\\SeoAssistant\\Admin\\SettingsPage')) {
            remove_submenu_page('options-general.php', '40q-seo-assistant');
        }
    }

    public function renderDashboard(): void
    {
        $packages = $this->packagesWithStatus();
        include __DIR__ . '/../resources/views/dashboard.php';
    }

    public function renderPackage(array $package): void
    {
        $package['status'] = $this->packageStatus($package);
        include __DIR__ . '/../resources/views/package.php';
    }

    public function renderSettings(): void
    {
        $config = $this->effectiveConfig();
        $settingsConfig = $this->app->make('config')->get('autonomy-ai.settings', []);
        $envKeys = $settingsConfig['env_keys'] ?? [];
        $notes = $settingsConfig['notes'] ?? [];
        $overrides = $this->overrides();
        include __DIR__ . '/../resources/views/settings.php';
    }

    protected function packageStatus(array $package): string
    {
        $statusClass = $package['status_class'] ?? null;

        if ($statusClass && class_exists($statusClass)) {
            return 'Active';
        }

        return 'Not detected';
    }

    protected function packagesWithStatus(): array
    {
        $packages = $this->app->make('config')->get('autonomy-ai.packages', []);

        foreach ($packages as &$package) {
            $package['status'] = $this->packageStatus($package);
        }

        return $packages;
    }

    protected function styles(): string
    {
        return '
        .wrap .ai-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; margin-top:16px; }
        .wrap .ai-card { background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04); }
        .wrap .ai-card h2 { margin:0 0 8px; font-size:16px; }
        .wrap .ai-card p { margin:0 0 12px; color:#3c434a; }
        .wrap .ai-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:999px; background:#f6f7f7; color:#1d2327; font-weight:600; font-size:12px; }
        .wrap .ai-badge.is-active { background:#e9f7ef; color:#0f5132; }
        .wrap .ai-badge.is-inactive { background:#fff4e5; color:#8a6d3b; }
        .wrap .ai-actions { display:flex; flex-wrap:wrap; gap:8px; }
        .wrap .ai-callout { margin-top:12px; padding:12px; background:#f6f7f7; border:1px solid #dcdcde; border-radius:6px; }
        .wrap .ai-list { list-style:disc; margin-left:20px; }
        ';
    }

    public function registerSettings(): void
    {
        register_setting('autonomy-ai', 'autonomy_ai_overrides', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeOverrides'],
            'default' => [],
        ]);
    }

    public function sanitizeOverrides($value): array
    {
        $value = is_array($value) ? $value : [];

        $ai = $value['ai'] ?? [];
        $validModels = ['openai', 'anthropic', 'custom', 'heuristic'];

        return [
            'ai' => [
                'default_model' => in_array($ai['default_model'] ?? '', $validModels, true) ? $ai['default_model'] : null,
                'openai' => [
                    'api_key' => isset($ai['openai']['api_key']) ? trim((string) $ai['openai']['api_key']) : null,
                    'model' => isset($ai['openai']['model']) ? trim((string) $ai['openai']['model']) : null,
                ],
                'anthropic' => [
                    'api_key' => isset($ai['anthropic']['api_key']) ? trim((string) $ai['anthropic']['api_key']) : null,
                    'model' => isset($ai['anthropic']['model']) ? trim((string) $ai['anthropic']['model']) : null,
                ],
            ],
        ];
    }

    protected function overrides(): array
    {
        $overrides = get_option('autonomy_ai_overrides', []);
        return is_array($overrides) ? $overrides : [];
    }

    protected function effectiveConfig(): array
    {
        $config = $this->app->make('config')->get('autonomy-ai', []);
        $overrides = $this->overrides();

        if (isset($overrides['ai'])) {
            $config['ai'] = array_merge(
                $config['ai'] ?? [],
                array_filter($overrides['ai'], static fn ($v) => $v !== null)
            );

            foreach (['openai', 'anthropic'] as $provider) {
                if (isset($overrides['ai'][$provider])) {
                    $config['ai'][$provider] = array_merge(
                        $config['ai'][$provider] ?? [],
                        array_filter($overrides['ai'][$provider], static fn ($v) => $v !== null)
                    );
                }
            }
        }

        return $config;
    }

    protected function applyPackageOverrides(): void
    {
        $config = $this->effectiveConfig();
        $ai = $config['ai'] ?? [];

        $defaultModel = $ai['default_model'] ?? 'openai';
        $openAi = $ai['openai'] ?? [];
        $anthropic = $ai['anthropic'] ?? [];

        // Apply to media-alt config (provider defaults).
        $this->app->make('config')->set('media-alt.default_provider', $defaultModel === 'anthropic' ? 'anthropic' : 'openai');
        $this->app->make('config')->set('media-alt.providers.openai.api_key', $openAi['api_key'] ?? $this->app->make('config')->get('media-alt.providers.openai.api_key'));
        $this->app->make('config')->set('media-alt.providers.openai.model', $openAi['model'] ?? $this->app->make('config')->get('media-alt.providers.openai.model'));
        $this->app->make('config')->set('media-alt.providers.anthropic.api_key', $anthropic['api_key'] ?? $this->app->make('config')->get('media-alt.providers.anthropic.api_key'));
        $this->app->make('config')->set('media-alt.providers.anthropic.model', $anthropic['model'] ?? $this->app->make('config')->get('media-alt.providers.anthropic.model'));

        // Pass SEO Assistant config through filter (see filterSeoAssistantSettings).
        $seo = [
            'ai_model' => $defaultModel === 'anthropic' ? 'anthropic' : 'openai',
            'openai_api_key' => $openAi['api_key'] ?? null,
            'openai_model' => $openAi['model'] ?? null,
        ];

        $this->app->instance('autonomy-ai.seo_assistant_config', array_filter($seo, static fn ($v) => $v !== null));
    }

    public function filterSeoAssistantSettings($value)
    {
        $seoConfig = $this->app->make('autonomy-ai.seo_assistant_config', []);
        if (!$seoConfig) {
            return $value;
        }

        $value = is_array($value) ? $value : [];
        return array_merge($value, $seoConfig);
    }
}
