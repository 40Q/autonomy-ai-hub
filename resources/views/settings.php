<?php

$envKeys = $envKeys ?? [];
$notes = $notes ?? [];
$config = $config ?? [];
$overrides = $overrides ?? [];
$ai = $config['ai'] ?? [];
$openai = $ai['openai'] ?? [];
$anthropic = $ai['anthropic'] ?? [];
$defaultModel = $ai['default_model'] ?? 'openai';

$maskValue = static function (string $value): string {
    if ($value === '') {
        return '';
    }

    $suffix = substr($value, -4);
    return '••••' . $suffix;
};
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">Shared configuration for 40Q Autonomy AI packages. Prefer <code>.env</code> first; overrides here are stored in <code>wp_options</code>.</p>

    <form method="post" action="options.php" style="margin-top:12px;">
        <?php settings_fields('autonomy-ai'); ?>

        <div class="ai-card" style="margin-top:0;">
            <h2 style="margin-top:0;">AI Model & Providers</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Default model</th>
                    <td>
                        <?php
                        $models = [
                            'openai' => 'OpenAI',
                            'anthropic' => 'Anthropic',
                            'heuristic' => 'Heuristic (built-in)',
                            'custom' => 'Custom (hooked)',
                        ];
                        foreach ($models as $key => $label) :
                            ?>
                            <label style="display:block; margin-bottom:6px;">
                                <input type="radio" name="autonomy_ai_overrides[ai][default_model]" value="<?php echo esc_attr($key); ?>" <?php checked($defaultModel, $key); ?> />
                                <?php echo esc_html($label); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">Controls which provider the subpackages default to. Package-specific options live in each submenu.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="autonomy-ai-openai-key">OpenAI API key</label></th>
                    <td>
                        <input type="password" id="autonomy-ai-openai-key" name="autonomy_ai_overrides[ai][openai][api_key]" value="<?php echo esc_attr($openai['api_key'] ?? ''); ?>" style="width:320px;" autocomplete="off" />
                        <p class="description">Overrides <code>OPENAI_API_KEY</code>. Leave blank to use <code>.env</code>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="autonomy-ai-openai-model">OpenAI model</label></th>
                    <td>
                        <input type="text" id="autonomy-ai-openai-model" name="autonomy_ai_overrides[ai][openai][model]" value="<?php echo esc_attr($openai['model'] ?? 'gpt-4o-mini'); ?>" style="width:240px;" />
                        <p class="description">Overrides <code>OPENAI_MODEL</code>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="autonomy-ai-anthropic-key">Anthropic API key</label></th>
                    <td>
                        <input type="password" id="autonomy-ai-anthropic-key" name="autonomy_ai_overrides[ai][anthropic][api_key]" value="<?php echo esc_attr($anthropic['api_key'] ?? ''); ?>" style="width:320px;" autocomplete="off" />
                        <p class="description">Overrides <code>ANTHROPIC_API_KEY</code>. Leave blank to use <code>.env</code>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="autonomy-ai-anthropic-model">Anthropic model</label></th>
                    <td>
                        <input type="text" id="autonomy-ai-anthropic-model" name="autonomy_ai_overrides[ai][anthropic][model]" value="<?php echo esc_attr($anthropic['model'] ?? 'claude-3-5-sonnet-latest'); ?>" style="width:240px;" />
                        <p class="description">Overrides <code>ANTHROPIC_MODEL</code>.</p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button('Save overrides'); ?>
    </form>

    <?php if ($envKeys) : ?>
        <div class="ai-card" style="margin-top:12px;">
            <h2 style="margin-top:0;">Environment checks</h2>
            <p>These keys are read by one or more packages. Values are masked for safety.</p>
            <table class="widefat striped" style="margin-top:8px;">
                <thead>
                    <tr>
                        <th scope="col">Key</th>
                        <th scope="col">Status</th>
                        <th scope="col">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($envKeys as $key) : ?>
                        <?php
                        $value = getenv($key) ?: ($_ENV[$key] ?? '');
                        $isSet = $value !== '';
                        ?>
                        <tr>
                            <td><code><?php echo esc_html($key); ?></code></td>
                            <td>
                                <span class="ai-badge <?php echo $isSet ? 'is-active' : 'is-inactive'; ?>">
                                    <?php echo $isSet ? 'Set' : 'Missing'; ?>
                                </span>
                            </td>
                            <td><?php echo $isSet ? esc_html($maskValue((string) $value)) : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($notes) : ?>
        <div class="ai-callout">
            <strong>Notes</strong>
            <ul class="ai-list">
                <?php foreach ($notes as $note) : ?>
                    <li><?php echo esc_html($note); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
