<?php

namespace BK\WPAI\Common\Settings;

defined('ABSPATH') || exit;

$tour_attr = $option->getName() === 'new_url' ? ' data-bk-tour="new-domain"' : '';
?>
<tr valign="top">
    <th scope="row" class="bk-wp-form-label">
        <label for="<?php echo $option->getIdAttribute(); ?>" <?php echo $option->getLabelClassAttribute(); ?>><?php echo $option->getLabel(); ?></label>
    </th>
    <td class="bk-wp-form bk-wp-form-input"<?php echo $tour_attr; ?>>
        <input name="<?php echo esc_attr($option->getNameAttribute()); ?>" id="<?php echo $option->getIdAttribute(); ?>" type="text" value="<?php echo $option->getValueAttribute(); ?>" synonym="<?php echo $option->getsynonymAttribute() ?: ''; ?>" <?php echo $option->getInputClassAttribute(); ?>>
        <?php if ($description = $option->getArg('description')) { ?>
            <p class="description"><?php echo $description; ?></p>
        <?php } ?>
        <?php if ($error = $option->hasError()) { ?>
            <div class="wp-ai-settings-error"><?php echo $error; ?></div>
        <?php } ?>
    </td>
</tr>