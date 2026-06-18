<?php

namespace BK\WPAI\Common\Settings;

defined('ABSPATH') || exit;
?>
<tr valign="top">
    <th scope="row" class="bk-wp-form-label">
        <label for="<?php echo $option->getIdAttribute(); ?>" <?php echo $option->getLabelClassAttribute(); ?>><?php echo $option->getLabel(); ?></label>
    </th>
    <td class="bk-wp-form bk-wp-form-input">
        <?php echo $option->getsynonymAttribute(); ?>
    </td>
</tr>