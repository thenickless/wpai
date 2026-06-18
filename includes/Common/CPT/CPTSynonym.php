<?php

namespace WP AI\WPAI\Common\CPT;

defined('ABSPATH') || exit;

class CPTSynonym extends CPT
{
    protected $post_type = 'bk_synonym';
    protected $templates = [
        'single'  => 'bk_synonym-single.php',
        'archive' => 'bk_synonym-archive.php',
    ];

    protected $rest_base   = 'synonym';
    protected $menu_icon   = 'dashicons-translation';
    protected $slug_options = [
        'slug_option_key' => 'custom_synonym_slug',
        'default_slug'    => 'synonym'
    ];

    protected $labels = [];
    protected $taxonomies = [];

    protected $supports = ['title'];




    public function __construct()
    {
        $this->labels = [
            'name' => _x('Synonym', 'Synonyms', 'wp-ai'),
            'singular_name' => _x('Synonym', 'Single synonym', 'wp-ai'),
            'menu_name' => __('Synonym', 'wp-ai'),
            'add_new' => __('Add synonym', 'wp-ai'),
            'add_new_item' => __('Add new synonym', 'wp-ai'),
            'edit_item' => __('Edit synonym', 'wp-ai'),
            'all_items' => __('All synonyms', 'wp-ai'),
            'search_items' => __('Search synonym', 'wp-ai'),
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

        $slug = !empty($options['custom_synonym_slug'])
            ? sanitize_title($options['custom_synonym_slug'])
            : 'glossary';

        $redirect_id = (int) ($options['redirect_archivpage_uri_synonym'] ?? 0);

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

        $slug = !empty($options['custom_synonym_slug'])
            ? sanitize_title($options['custom_synonym_slug'])
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

            $redirect_id = (int) ($options['redirect_archivpage_uri_synonym'] ?? 0);

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
