<?php

namespace BK\WPAI\Common\Settings;

defined('ABSPATH') || exit;

$flash = $settings->flash->has();
$errors = $settings->errors->hasErrors();
?>
<div class="wrap wp-ai-settings-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($settings->title); ?></h1>
    <button type="button" id="wp-ai-start-guided-tour" class="page-title-action">
        <?php esc_html_e('Guided tour', 'wp-ai'); ?>
    </button>
    <button type="button" id="wp-ai-start-setup-tour" class="page-title-action">
        <?php esc_html_e('Setup tour', 'wp-ai'); ?>
    </button>
    <hr class="wp-header-end">
    <div id="wp-ai-guided-tour-root"></div>

    <?php if ($flash) { ?>
        <div class="notice notice-<?php echo $flash['status']; ?> is-dismissible">
            <p><?php echo $flash['message']; ?></p>
        </div>
    <?php } ?>

    <?php if ($errors) { ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Settings issues detected.', 'wp-ai'); ?></p>
        </div>
    <?php } ?>

    <?php $settings->renderTabMenu(); ?>

    <?php $settings->renderActiveSections(); ?>
</div>