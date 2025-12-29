<?php

$package = $package ?? [];
$title = $package['title'] ?? 'Package';
$description = $package['description'] ?? '';
$slug = sanitize_title($package['slug'] ?? $title);
$status = $package['status'] ?? 'Unknown';
$statusClass = $status === 'Active' ? 'is-active' : 'is-inactive';

$linkFor = static function (?string $url): ?string {
    if (!$url) {
        return null;
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }

    return admin_url(ltrim($url, '/'));
};

$manageUrl = $linkFor($package['manage_url'] ?? null);
$docsUrl = $linkFor($package['docs_url'] ?? null);
$showPackageCard = $slug !== 'media-alt-suggester';
?>

<div class="wrap">
    <h1><?php echo esc_html($title); ?></h1>
    <p class="description"><?php echo esc_html($description); ?></p>

    <?php if ($showPackageCard) : ?>
        <div class="ai-card" style="margin-top:12px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <h2 style="margin:0;"><?php echo esc_html($title); ?></h2>
                <span class="ai-badge <?php echo esc_attr($statusClass); ?>"><?php echo esc_html($status); ?></span>
            </div>
            <p><?php echo esc_html($description); ?></p>
            <div class="ai-actions">
                <?php if ($manageUrl) : ?>
                    <a class="button button-primary" href="<?php echo esc_url($manageUrl); ?>">Open</a>
                <?php endif; ?>
                <?php if ($docsUrl) : ?>
                    <a class="button" href="<?php echo esc_url($docsUrl); ?>" target="_blank" rel="noreferrer">Docs</a>
                <?php endif; ?>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=40q-autonomy-ai')); ?>">Back to hub</a>
            </div>
        </div>

        <div class="ai-callout">
            <strong>Workflow:</strong> build in <code>packages/40q/<?php echo esc_html($slug); ?></code>, require via Composer (path repository), and tag releases in the standalone repo to deploy independently. Add a dedicated settings page here when the package exposes options.
        </div>
    <?php endif; ?>
</div>
