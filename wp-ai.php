<?php

/*
Plugin Name:        WP AI
Plugin URI:         https://github.com/BK-Webteam/wp-ai
Version:            0.0.1
Description:        Explain your content with FAQ, glossary, synonyms and placeholder.
Author:             Benjamin Klemencic
Author URI:         https://github.com/thenickless
License:            GNU General Public License Version 3
License URI:        https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:        wp-ai
Domain Path:        /languages
Requires at least:  6.8
Requires PHP:       8.2
*/

namespace BK\WPAI;

use BK\WPAI\Main;
use BK\WPAI\Common\Tools;
use BK\WPAI\Common\Plugin\Plugin;

defined('ABSPATH') || exit;

const BK_WPAI_PLUGIN = 'wp-ai/wp-ai.php';
const MIGRATE_DONE_KEY = 'wp_ai_migrate_multisite_done';
const MIGRATE_REPORT_KEY = 'wp_ai_migrate_multisite_report';

/**
 * ------------------------------------------------------------
 * PSR-4-ish autoloader for /includes
 * ------------------------------------------------------------
 */
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $baseDir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});


// Only ONE activation hook. It runs the multisite migration ONLY when network-activated.
register_activation_hook(__FILE__, __NAMESPACE__ . '\wp_ai_on_activate_network');

// Deactivation (optional cleanup).
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');

// Normal bootstrap.
add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

// Persisted migration report notice (survives activation redirects).
add_action('network_admin_notices', __NAMESPACE__ . '\wp_ai_migrate_multisite_notice');

/**
 * Deactivation callback function.
 */
function deactivation(): void
{
    // Cleanup could go here if needed.
}

/**
 * Activation callback for multisite "Network Activate".
 *
 * Requirements:
 * - Migration is triggered ONLY by clicking "Network Activate".
 * - No pending flag/state machine: run migration immediately in activation.
 * - BK-WP AI MUST NOT remain network-activated after migration.
 *
 * IMPORTANT:
 * - On multisite, WordPress passes $network_wide to activation hooks.
 * - In some contexts WP may call hooks without args; therefore $network_wide is optional.
 */
function wp_ai_on_activate_network($network_wide = false): void
{
    $network_wide = (bool) $network_wide;

    // Only run for multisite "Network Activate".
    if (!is_multisite() || !$network_wide) {
        return;
    }

    wp_ai_ensure_plugin_functions();

    // Do not run twice.
    if (get_site_option(MIGRATE_DONE_KEY)) {
        wp_ai_store_report([
            'type' => 'info',
            'title' => 'BK-WP AI',
            'intro' => __('Migration already marked as done. No changes were made.', 'wp-ai'),
            'items' => [],
            'footer' => '',
        ]);
        return;
    }

    /**
     * PRECONDITION:
     * BK-WP AI must NOT be network-activated, otherwise it becomes active on all sites.
     * The user just network-activated it, so immediately undo that.
     */
    wp_ai_force_network_deactivate(BK_WPAI_PLUGIN);

    // Hard abort if we cannot ensure it is NOT network-active.
    if (wp_ai_is_network_active(BK_WPAI_PLUGIN)) {
        wp_ai_store_report([
            'type' => 'error',
            'title' => 'BK-WP AI',
            'intro' => __('Migration aborted: BK-WP AI could not be deactivated network-wide during activation.', 'wp-ai'),
            'items' => [],
            'footer' => __('No site changes were made. Please deactivate BK-WP AI network-wide manually and retry.', 'wp-ai'),
        ]);
        return;
    }

    // Run the migration.
    $result = wp_ai_migrate_multisite_core();

    /**
     * FINAL POSTCONDITION:
     * Ensure BK-WP AI is NOT network-activated after migration.
     * If it is, abort and do NOT mark migration as done.
     */
    wp_ai_force_network_deactivate(BK_WPAI_PLUGIN);

    if (wp_ai_is_network_active(BK_WPAI_PLUGIN)) {
        wp_ai_store_report([
            'type' => 'error',
            'title' => 'BK-WP AI',
            'intro' => __('Migration failed: BK-WP AI is network-activated after migration.', 'wp-ai'),
            'items' => $result['items'] ?? [],
            'footer' => __('The migration was NOT marked as done. Please ensure BK-WP AI is not network-activated and retry.', 'wp-ai'),
        ]);
        return; // IMPORTANT: do NOT set MIGRATE_DONE_KEY
    }

    // Mark migration as done ONLY when final invariant holds.
    update_site_option(MIGRATE_DONE_KEY, 1);

    // Store report (success/info) for display after activation redirect.
    if (!empty($result['report'])) {
        wp_ai_store_report($result['report']);
    }
}


function plugin(): Plugin
{
    static $instance;

    if (null === $instance) {
        $instance = new Plugin(__FILE__);
    }

    return $instance;
}

function main(): Main
{
    static $instance;

    if (null === $instance) {
        $instance = new Main();
    }

    return $instance;
}

function load_textdomain(): void
{
    load_plugin_textdomain(
        'wp-ai',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

function register_blocks(): void
{
    register_block_type_from_metadata(__DIR__ . '/blocks/faq');
    register_block_type_from_metadata(__DIR__ . '/blocks/faq-widget');
    register_block_type_from_metadata(__DIR__ . '/blocks/glossary');
    register_block_type_from_metadata(__DIR__ . '/blocks/synonym');
    register_block_type_from_metadata(__DIR__ . '/blocks/placeholder');

    $faq_handle = generate_block_asset_handle('wp-ai/faq', 'editorScript');
    $faq_widget_handle = generate_block_asset_handle('wp-ai/faq-widget', 'editorScript');
    $glossary_handle = generate_block_asset_handle('wp-ai/glossary', 'editorScript');
    $synonym_handle = generate_block_asset_handle('wp-ai/synonym', 'editorScript');
    $placeholder_handle = generate_block_asset_handle('wp-ai/placeholder', 'editorScript');

    $path = plugin_dir_path(__FILE__) . 'languages';

    wp_set_script_translations($faq_handle, 'wp-ai', $path);
    wp_set_script_translations($faq_widget_handle, 'wp-ai', $path);
    wp_set_script_translations($glossary_handle, 'wp-ai', $path);
    wp_set_script_translations($synonym_handle, 'wp-ai', $path);
    wp_set_script_translations($placeholder_handle, 'wp-ai', $path);
}

/**
 * Handle the loading of the plugin.
 */
function loaded(): void
{
    // Trigger the 'loaded' method of the main plugin instance.
    plugin()->loaded();

    // Load the plugin textdomain for translations.
    add_action('init', __NAMESPACE__ . '\load_textdomain');

    $wpCompatibe = is_wp_version_compatible(plugin()->getRequiresWP());
    $phpCompatible = is_php_version_compatible(plugin()->getRequiresPHP());

    // Check system requirements.
    if (!$wpCompatibe || !$phpCompatible) {
        add_action('init', function () use ($wpCompatibe, $phpCompatible) {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            $pluginName = plugin()->getName();

            $error = '';
            if (!$wpCompatibe) {
                $error = sprintf(
                    __('The server is running WordPress version %1$s. The plugin requires at least WordPress version %2$s.', 'wp-ai'),
                    wp_get_wp_version(),
                    plugin()->getRequiresWP()
                );
            } elseif (!$phpCompatible) {
                $error = sprintf(
                    __('The server is running PHP version %1$s. The plugin requires at least PHP version %2$s.', 'wp-ai'),
                    PHP_VERSION,
                    plugin()->getRequiresPHP()
                );
            }

            add_action('admin_notices', function () use ($pluginName, $error) {
                printf(
                    '<div class="notice notice-error"><p>' .
                    esc_html__('Plugins: %1$s: %2$s', 'wp-ai') .
                    '</p></div>',
                    esc_html($pluginName),
                    esc_html($error)
                );
            });
        });

        return;
    }

    // Initialize plugin.
    main();

    add_action('init', __NAMESPACE__ . '\register_blocks');
    add_action('init', __NAMESPACE__ . '\bk_update_glossary_cpt');
    add_action('init', __NAMESPACE__ . '\bk_update_synonym_cpt');
    add_action('init', __NAMESPACE__ . '\bk_migrate_domains');
    add_action('init', __NAMESPACE__ . '\bk_migrate_blocks');
    add_action('init', __NAMESPACE__ . '\bk_update_placeholder_cpt');    
}

function bk_update_glossary_cpt(): void
{
    global $wpdb;

    if (get_option('bk_update_glossary_cpt_done')) {
        return;
    }

    $wpdb->query("
        UPDATE {$wpdb->term_taxonomy} tt
        INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        SET tt.taxonomy = 'bk_glossary_category'
        WHERE p.post_type = 'glossary'
        AND tt.taxonomy = 'glossary_category'
    ");

    $wpdb->query("
        UPDATE {$wpdb->term_taxonomy} tt
        INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        SET tt.taxonomy = 'bk_glossary_tag'
        WHERE p.post_type = 'glossary'
        AND tt.taxonomy = 'glossary_tag'
    ");

    $wpdb->update(
        $wpdb->posts,
        ['post_type' => 'bk_glossary'],
        ['post_type' => 'glossary']
    );

    wp_cache_flush();
    flush_rewrite_rules();

    update_option('bk_update_glossary_cpt_done', 1);
}

function bk_update_synonym_cpt(): void
{
    global $wpdb;

    if (get_option('bk_rename_synonym_to_synonym_done')) {
        return;
    }

    // 1) post_type: bk_placeholder -> bk_synonym
    $wpdb->update($wpdb->posts, ['post_type' => 'bk_synonym'], ['post_type' => 'bk_placeholder']);

    // 2) meta key: 'placeholder' -> 'synonym'
    $wpdb->update($wpdb->postmeta, ['meta_key' => 'placeholder'], ['meta_key' => 'synonym']); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

    // 3) taxonomies: bk_placeholder_group -> bk_synonym_group, bk_placeholder_tag -> bk_synonym_tag
    $wpdb->update($wpdb->term_taxonomy, ['taxonomy' => 'bk_placeholder_group'], ['taxonomy' => 'bk_synonym_group']);
    $wpdb->update($wpdb->term_taxonomy, ['taxonomy' => 'bk_placeholder_tag'],   ['taxonomy' => 'bk_synonym_tag']);

    wp_cache_flush();
    flush_rewrite_rules();

    update_option('bk_rename_synonym_to_synonym_done', 1);
}

function bk_migrate_domains(): void
{
    if (get_option('bk_migrate_domains_done')) {
        return;
    }

    $domains = [];
    $source_options = ['bk-faq', 'bk-glossary'];

    foreach ($source_options as $option_name) {
        $option = get_option($option_name);

        if (!empty($option['registeredDomains'])) {
            foreach ($option['registeredDomains'] as $shortname => $url) {
                $identifier = Tools::getIdentifier($url);
                $domains[$identifier] = $url;
            }
        }
    }

    $wp_ai_option = get_option('wp-ai', []);
    $wp_ai_option['registeredDomains'] = $domains;

    delete_option('wp-ai');
    add_option('wp-ai', $wp_ai_option);

    update_option('bk_migrate_domains_done', 1);
}


function bk_migrate_blocks(): void
{
    if (get_option('bk_migrate_blocks_done')) {
        return;
    }

    $aBlocks = [
        'wp:create-block/bk-faq' => 'wp:wp-ai/faq',
        'wp:create-block/bk-glossary' => 'wp:wp-ai/glossary',
        'wp:create-block/bk-synonym' => 'wp:wp-ai/synonym'        
    ];

    $posts = get_posts([
        'post_type' => ['post', 'page'],
        'posts_per_page' => -1
    ]);

    foreach ($posts as $post) {
        foreach($aBlocks as $old => $new){

        $content = str_replace(
            $old,
            $new,
            $post->post_content
        );

        if ($content !== $post->post_content) {
            wp_update_post([
                'ID' => $post->ID,
                'post_content' => $content
            ]);
        }
        }
    }


    update_option('bk_migrate_blocks_done', 1);
}

function wp_ai_migrate_targets(): array
{
    return [
        'bk-faq/bk-faq.php',
        'bk-glossary/bk-glossary.php',
        'bk-synonym/bk-synonym.php',
    ];
}

// new placeholder 1.3.0
function bk_update_placeholder_cpt(): void
{

    global $wpdb;

    if (get_option('bk_rename_placeholder_to_synonym_done')) {
        return;
    }

    $wpdb->update($wpdb->posts, ['post_type' => 'bk_synonym'], ['post_type' => 'bk_placeholder']);

    $wpdb->update($wpdb->postmeta, ['meta_key' => 'synonym'], ['meta_key' => 'placeholder']); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

    wp_cache_flush();
    flush_rewrite_rules();

    update_option('bk_rename_placeholder_to_synonym_done', 1);
}


function wp_ai_ensure_plugin_functions(): void
{
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
}

function wp_ai_refresh_plugin_caches(): void
{
    if (function_exists('wp_clean_plugins_cache')) {
        wp_clean_plugins_cache(true);
    }

    // active_sitewide_plugins is stored in the site-options cache group.
    wp_cache_delete('active_sitewide_plugins', 'site-options');
    wp_cache_delete('active_plugins', 'options');
}

/**
 * Robust "is network active" check using the site option as source of truth.
 * (Helpful on installations with persistent object cache.)
 */
function wp_ai_is_network_active(string $plugin_basename): bool
{
    $sitewide = (array) get_site_option('active_sitewide_plugins', []);
    if (isset($sitewide[$plugin_basename])) {
        return true;
    }

    // Fallback to core helper if available.
    return function_exists('is_plugin_active_for_network')
        ? is_plugin_active_for_network($plugin_basename)
        : false;
}

/**
 * Force network deactivation and verify with cache refresh.
 */
function wp_ai_force_network_deactivate(string $plugin_basename): void
{
    if (!wp_ai_is_network_active($plugin_basename)) {
        return;
    }

    deactivate_plugins($plugin_basename, false, true);

    wp_ai_refresh_plugin_caches();
}

/**
 * Store report for display after activation redirect.
 */
function wp_ai_store_report(array $payload): void
{
    set_site_transient(MIGRATE_REPORT_KEY, $payload, 10 * MINUTE_IN_SECONDS);
}

/**
 * One-time migration report notice in Network Admin.
 */
function wp_ai_migrate_multisite_notice(): void
{
    if (!is_multisite() || !is_network_admin()) {
        return;
    }

    $payload = get_site_transient(MIGRATE_REPORT_KEY);
    if (empty($payload) || !is_array($payload)) {
        return;
    }

    delete_site_transient(MIGRATE_REPORT_KEY);

    $type = $payload['type'] ?? 'info'; // info|success|warning|error
    $title = $payload['title'] ?? 'BK-WP AI';
    $intro = $payload['intro'] ?? '';
    $items = $payload['items'] ?? [];
    $footer = $payload['footer'] ?? '';

    $class = match ($type) {
        'success' => 'notice notice-success',
        'warning' => 'notice notice-warning',
        'error' => 'notice notice-error',
        default => 'notice notice-info',
    };

    echo '<div class="' . esc_attr($class) . '"><p><strong>' . esc_html($title) . '</strong>';
    if ($intro !== '') {
        echo ' ' . esc_html($intro);
    }
    echo '</p>';

    if (!empty($items)) {
        echo '<ul class="wp-ai-notice-list">';
        foreach ($items as $row) {
            echo '<li>' . wp_kses_post($row) . '</li>';
        }
        echo '</ul>';
    }

    if ($footer !== '') {
        echo '<p>' . esc_html($footer) . '</p>';
    }

    echo '</div>';
}

function wp_ai_migrate_multisite_core(): array
{
    // In activation context we might not be on a screen, but the user must have capability.
    if (!current_user_can('manage_network_plugins')) {
        $report = [
            'type' => 'error',
            'title' => 'BK-WP AI',
            'intro' => __('Migration aborted: insufficient permissions (manage_network_plugins).', 'wp-ai'),
            'items' => [],
            'footer' => '',
        ];
        return ['items' => [], 'report' => $report];
    }

    wp_ai_ensure_plugin_functions();

    // Safety: do not proceed if network-active.
    if (wp_ai_is_network_active(BK_WPAI_PLUGIN)) {
        $report = [
            'type' => 'error',
            'title' => 'BK-WP AI',
            'intro' => __('Migration aborted: BK-WP AI is network-activated. Please deactivate it network-wide and retry.', 'wp-ai'),
            'items' => [],
            'footer' => '',
        ];
        return ['items' => [], 'report' => $report];
    }

    $targets = wp_ai_migrate_targets();

    $items = [];

    foreach (get_sites(['number' => 0]) as $site) {
        $blog_id = (int) $site->blog_id;

        switch_to_blog($blog_id);

        try {
            // Check if any old plugin is active on this site.
            $has_target = false;
            foreach ($targets as $p) {
                if (is_plugin_active($p)) {
                    $has_target = true;
                    break;
                }
            }

            if (!$has_target) {
                continue;
            }

            // Deactivate old plugins on this site.
            $deactivated = [];
            foreach ($targets as $p) {
                if (is_plugin_active($p)) {
                    deactivate_plugins($p, false, false);
                    $deactivated[] = dirname($p);
                }
            }

            // Activate BK-WP AI on this site only.
            $activated_now = false;
            $activation_error = '';

            if (!is_plugin_active(BK_WPAI_PLUGIN)) {
                // silent=true prevents redirects/exits that would interrupt migration.
                $res = activate_plugin(BK_WPAI_PLUGIN, '', false, true);

                if (is_wp_error($res)) {
                    $activation_error = $res->get_error_message();
                } else {
                    $activated_now = true;
                }
            }

            // Report line.
            $label = get_bloginfo('name') . ' (' . home_url() . ')';

            $parts = [];
            $parts[] = !empty($deactivated)
                ? sprintf('%s %s.', esc_html__('Deactivated:', 'wp-ai'), esc_html(implode(', ', $deactivated)))
                : esc_html__('Deactivated: none.', 'wp-ai');

            if ($activation_error !== '') {
                $parts[] = sprintf(
                    '<strong class="wp-ai-error">%s</strong> %s',
                    esc_html__('BK-WP AI activation failed:', 'wp-ai'),
                    esc_html($activation_error)
                );
            } else {
                $parts[] = $activated_now
                    ? esc_html__('BK-WP AI activated.', 'wp-ai')
                    : esc_html__('BK-WP AI already active (no change).', 'wp-ai');
            }

            $items[] = '<strong>' . esc_html($label) . '</strong>: ' . implode(' ', $parts);
        } finally {
            restore_current_blog();
        }
    }

    $report = !empty($items)
        ? [
            'type' => 'success',
            'title' => 'BK-WP AI',
            'intro' => __('Migration result (old plugins deactivated, BK-WP AI activated where needed):', 'wp-ai'),
            'items' => $items,
            'footer' => '',
        ]
        : [
            'type' => 'info',
            'title' => 'BK-WP AI',
            'intro' => __('No sites required changes.', 'wp-ai'),
            'items' => [],
            'footer' => '',
        ];

    return ['items' => $items, 'report' => $report];
}
