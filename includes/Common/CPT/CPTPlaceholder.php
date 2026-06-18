<?php

namespace BK\WPAI\Common\CPT;

defined('ABSPATH') || exit;

class CPTPlaceholder extends CPT
{
    protected $post_type = 'bk_placeholder';
    protected $templates = [
        'single'  => 'bk_placeholder-single.php',
        'archive' => 'bk_placeholder-archive.php',
    ];

    protected $rest_base   = 'placeholder';
    protected $menu_icon   = 'dashicons-editor-paste-text';
    protected $slug_options = [
        'slug_option_key' => 'custom_placeholder_slug',
        'default_slug'    => 'placeholder'
    ];

    protected $labels = [];
    protected $taxonomies = [];

    protected $supports = ['title', 'editor', 'revisions'];




    public function __construct()
    {
        $this->labels = [
            'name' => _x('Placeholder', 'Placeholders', 'wp-ai'),
            'singular_name' => _x('Placeholder', 'Single placeholder', 'wp-ai'),
            'menu_name' => __('Placeholder', 'wp-ai'),
            'add_new' => __('Add placeholder', 'wp-ai'),
            'add_new_item' => __('Add new placeholder', 'wp-ai'),
            'edit_item' => __('Edit placeholder', 'wp-ai'),
            'all_items' => __('All placeholders', 'wp-ai'),
            'search_items' => __('Search placeholder', 'wp-ai'),
        ];

        parent::__construct($this->post_type);

        add_action('template_redirect', [$this, 'maybe_disable_canonical_redirect'], 1);
        add_action('template_redirect', [$this, 'custom_cpt_404_message'], 10);

    }

    /**
     * Disable canonical redirect if redirect page exists
     */
    public function maybe_disable_canonical_redirect(): void
    {
        $options = get_option('wp-ai');

        $slug = !empty($options['custom_placeholder_slug'])
            ? sanitize_title($options['custom_placeholder_slug'])
            : 'glossary';

        $redirect_id = (int) ($options['redirect_archivpage_uri_placeholder'] ?? 0);

        if ($redirect_id > 0 && parent::is_slug_request($slug)) {
            remove_filter('template_redirect', 'redirect_canonical');
        }
    }

    /**
     * Handle CPT redirects
     */
    public function custom_cpt_404_message(): void
    {
        global $wp_query;

        $options = get_option('wp-ai');

        $slug = !empty($options['custom_placeholder_slug'])
            ? sanitize_title($options['custom_placeholder_slug'])
            : 'faq';

        // CPT Single 404
        if (
            isset($wp_query->query_vars['post_type']) &&
            $wp_query->query_vars['post_type'] === $this->post_type &&
            empty($wp_query->post)
        ) {
            self::render_custom_404();
        }

        // Redirect archive slug
        if (parent::is_slug_request($slug)) {

            $redirect_id = (int) ($options['redirect_archivpage_uri_placeholder'] ?? 0);

            if ($redirect_id > 0) {

                $post = get_post($redirect_id);

                if ($post && get_post_status($post) === 'publish') {

                    wp_redirect(get_permalink($post), 301);
                    exit;
                }
            }
        }
    }

}
