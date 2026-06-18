<?php

namespace BK\WPAI\Common\Settings;

defined('ABSPATH') || exit;
?>
<form method="post" action="<?php echo $settings->getFullUrl(); ?>">
    <?php Template::include('section-menu', compact('settings')); ?>

    <?php foreach ($settings->getActiveTab()->getActiveSections() as $section) { ?>
        <?php Template::include('section', compact('section')); ?>
    <?php } ?>

    <?php wp_nonce_field('bk-wp_ai_settings_save_' . $settings->optionName, 'bk-wp_ai_settings_save'); ?>

    <?php submit_button(null, 'primary', 'submit', true, ['data-bk-tour' => 'save-settings']); ?>
</form>