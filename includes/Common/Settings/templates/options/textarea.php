<?php

namespace BK\WPAI\Common\Settings;

defined('ABSPATH') || exit;
?>
<tr valign="top">
    <th scope="row" class="bk-wp-form-label">
        <label for="<?php echo $option->getIdAttribute(); ?> <?php echo $option->getLabelClassAttribute(); ?>"><?php echo $option->getLabel(); ?></label>
    </th>
    <td class="bk-wp-form bk-wp-form-input">
        <textarea name="<?php echo esc_attr($option->getNameAttribute()); ?>" id="<?php echo $option->getIdAttribute(); ?>" <?php echo $option->getInputClassAttribute(); ?>><?php echo $option->getValueAttribute(); ?></textarea>
        <?php if ($description = $option->getArg('description')) { ?>
            <p class="description"><?php echo $description; ?></p>
        <?php } ?>
        <?php if ($error = $option->hasError()) { ?>
            <div class="wp-ai-settings-error"><?php echo $error; ?></div>
        <?php } ?>
    </td>
</tr>