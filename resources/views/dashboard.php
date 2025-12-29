<?php

$packages = $packages ?? [];
$ai = $ai ?? [];

$linkFor = static function (?string $url): ?string {
    if (!$url) {
        return null;
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }

    return admin_url(ltrim($url, '/'));
};

$defaultModel = $ai['default_model'] ?? 'openai';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">Central hub for 40Q Autonomy AI tools. Quickly jump to each package and keep an eye on install status.</p>

    <div class="ai-card" style="margin-top:12px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
            <div>
                <h2 style="margin:0;">AI Provider</h2>
                <p style="margin:4px 0 0; color:#3c434a;">Default model: <strong><?php echo esc_html(ucfirst($defaultModel)); ?></strong></p>
            </div>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=40q-autonomy-ai-settings')); ?>">Edit provider</a>
        </div>
    </div>

    <div class="ai-grid" style="margin-top:12px;">
        <?php foreach ($packages as $package) : ?>
            <?php
            $status = $package['status'] ?? 'Unknown';
            $statusClass = $status === 'Active' ? 'is-active' : 'is-inactive';
            $manageUrl = $linkFor($package['manage_url'] ?? null);
            $docsUrl = $linkFor($package['docs_url'] ?? null);
            ?>
            <div class="ai-card">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <h2><?php echo esc_html($package['title'] ?? ''); ?></h2>
                    <span class="ai-badge <?php echo esc_attr($statusClass); ?>"><?php echo esc_html($status); ?></span>
                </div>
                <p><?php echo esc_html($package['description'] ?? ''); ?></p>
                <div class="ai-actions">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=40q-autonomy-ai-' . sanitize_title($package['slug'] ?? ''))); ?>">Details</a>
                    <?php if ($manageUrl) : ?>
                        <a class="button" href="<?php echo esc_url($manageUrl); ?>">Open</a>
                    <?php endif; ?>
                    <?php if ($docsUrl) : ?>
                        <a class="button button-link" href="<?php echo esc_url($docsUrl); ?>" target="_blank" rel="noreferrer">Docs</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ai-callout">
        <strong>Tip:</strong> Add or reorder packages via <code>config/autonomy-ai.php</code>. Ship each package from the <code>packages/40q/*</code> folder and split to standalone repos when tagging releases.
    </div>
</div>
