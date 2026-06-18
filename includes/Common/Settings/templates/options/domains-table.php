<?php

namespace WP AI\WPAI\Common\Settings;

defined('ABSPATH') || exit;

use WP AI\WPAI\Common\API\SyncAPI;

?>
<tr>
    <td colspan="2">
        <?php

        $api = new SyncAPI();
        $domains = $api->getDomains();

        if (count($domains) > 0) {
            $i = 1;
            echo '<style> .settings_page_bk-wp_ai #log .form-table th {width:0;}</style>';
            echo '<table class="wp-list-table widefat striped"><tbody>';
            foreach ($domains as $identifier => $url) {
                echo '<tr><td><input type="checkbox" name="del_domain_' . esc_attr($i) . '" value="' . esc_attr($identifier) . '"></td><td>' . esc_html($identifier) . '</td><td>' . esc_url($url) . '</td></tr>';
                $i++;
            }
            echo '</tbody></table>';
            echo '<p>' . esc_html__('Please note: "Delete selected domains" will DELETE every FAQ and glossary entry on this website that has been fetched from the selected domains.', 'wp-ai') . '</p>';
            submit_button(esc_html__('Delete selected domains', 'wp-ai'));
        }
        ?>
    </td>
</tr>
