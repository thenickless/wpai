<?php

namespace WP AI\WPAI\Common\CPT;

defined('ABSPATH') || exit;


class CPTGlossary extends CPT
{
    protected $post_type = 'bk_glossary';

    protected $templates = [
        'single' => 'bk_glossary-single.php',
        'archive' => 'bk_glossary-archive.php',
        'taxonomy' => [
            'category' => 'bk_glossary_category.php',
            'tag' => 'bk_glossary_tag.php',
        ],
    ];

    protected $rest_base = 'glossary';
    protected $menu_icon = 'dashicons-book-alt';
    protected $slug_options = [
        'slug_option_key' => 'custom_glossary_slug',
        'default_slug' => 'glossary',
    ];

    protected $supports = ['title', 'editor', 'revisions'];

    protected $labels = [];
    protected $taxonomies = [];

    public function __construct()
    {
        $this->labels = [
            'name' => _x('Glossary', 'Glossary entries', 'wp-ai'),
            'singular_name' => _x('Glossary', 'Single glossary ', 'wp-ai'),
            'menu_name' => __('Glossary', 'wp-ai'),
            'add_new' => __('Add glossary', 'wp-ai'),
            'add_new_item' => __('Add new glossary', 'wp-ai'),
            'edit_item' => __('Edit glossary', 'wp-ai'),
            'all_items' => __('All glossaries', 'wp-ai'),
            'search_items' => __('Search glossary', 'wp-ai'),
        ];

        $this->taxonomies = [
            [
                'name' => 'bk_glossary_category',
                'label' => __('Glossary Categories', 'wp-ai'),
                'slug_option_key' => 'website_custom_glossary_category_slug',
                'default_slug' => 'glossary_category',
                'rest_base' => 'bk_glossary_category',
                'hierarchical' => true,
            ],
            [
                'name' => 'bk_glossary_tag',
                'label' => __('Glossary Tags', 'wp-ai'),
                'slug_option_key' => 'website_custom_glossary_tag_slug',
                'default_slug' => 'glossary_tag',
                'rest_base' => 'bk_glossary_tag',
                'hierarchical' => false,
            ],
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

        $slug = !empty($options['custom_glossary_slug'])
            ? sanitize_title($options['custom_glossary_slug'])
            : 'glossary';

        $redirect_id = (int) ($options['redirect_archivpage_uri_glossary'] ?? 0);

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

        $slug = !empty($options['custom_glossary_slug'])
            ? sanitize_title($options['custom_glossary_slug'])
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

            $redirect_id = (int) ($options['redirect_archivpage_uri_glossary'] ?? 0);

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
