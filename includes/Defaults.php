<?php

namespace BK\WPAI;

use BK\WPAI\Common\API\SyncAPI;
use function BK\WPAI\plugin;
use BK\WPAI\Common\Tools;


defined('ABSPATH') || exit;

define('ENDPOINT', 'wp-json/wp/v2/');

/**
 * Class Defaults
 *
 * Holds and provides access to plugin-wide default values.
 *
 * @package BK\WPAI\Common
 */
class Defaults
{
    /**
     * Plugin default values.
     *
     * @var array
     */
    private readonly array $defaults;

    /**
     * Defaults constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->defaults = $this->load();
    }

    /**
     * Returns the default values, filtered via WordPress.
     *
     * @return array
     */
    private function load(): array
    {
        $pagelist = Tools::getPageList();

        $defaults = [
            'settings' => [
                'option_name' => 'wp-ai',
                'menu_title' => __('WP AI', 'wp-ai'),
                'page_title' => __('WP AI Settings', 'wp-ai'),
                'capability' => 'manage_options',
                'checkbox_option' => false,
                'text_synonym' => __('Enter your text here...', 'wp-ai'),
                'select_default' => 'none',
            ],
            'sections' => [
                ['id' => 'permissions', 'title' => __('Permissions', 'wp-ai')],
                ['id' => 'permalink_settings', 'title' => __('Permalink Settings', 'wp-ai')],
                ['id' => 'domains', 'title' => __('Domains', 'wp-ai')],
                ['id' => 'import', 'title' => __('Import', 'wp-ai')],
                ['id' => 'faqlog', 'title' => __('Logfile', 'wp-ai')]
            ],
            'fields' => [
                'permissions' => [
                    [
                        'name' => 'api_active_bk_faq',
                        'label' => __('Allow to import FAQ', 'wp-ai'),
                        'description' => __('Allow other websites to import your FAQ. Your SEO will not be affected. Structured data is used for your content only.', 'wp-ai'),
                        'type' => 'checkbox',
                    ],
                    [
                        'name' => 'api_active_bk_glossary',
                        'label' => __('Allow to import glossary', 'wp-ai'),
                        'description' => __('Allow other websites to import your glossary. Your SEO will not be affected. Structured data is used for your content only.', 'wp-ai'),
                        'type' => 'checkbox',
                    ]
                ],
                'domains' => [
                    [
                        'name' => 'domains',
                        'label' => __('Domains', 'wp-ai'),
                        'desc' => __('Enter the domain\'s URL you want to receive FAQ from.', 'wp-ai'),
                        'type' => 'domains-table'
                    ],
                    [
                        'name' => 'new_url',
                        'label' => __('New Domain', 'wp-ai'),
                        'desc' => __('Enter the domain\'s URL you want to receive FAQ from.', 'wp-ai'),
                        'type' => 'text',
                        'default' => 'https://',
                    ]
                ],
                'permalink_settings' => [
                    [
                        'name' => 'label_faq',
                        'label' => __('FAQ', 'wp-ai'),
                        'type' => 'hr',
                    ],
                    [
                        'name' => 'redirect_archivpage_uri_faq',
                        'label' => __('Archive page', 'wp-ai'),
                        'description' => '',
                        'type' => 'select',
                        'options' => $pagelist,
                        'default' => ''
                    ],
                    [
                        'name' => 'custom_faq_slug',
                        'label' => __('FAQ Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'bk_faq',
                        'synonym' => 'bk_faq'
                    ],
                    [
                        'name' => 'custom_faq_category_slug',
                        'label' => __('Category Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'faq_category',
                        'synonym' => 'faq_category'

                    ],
                    [
                        'name' => 'custom_faq_tag_slug',
                        'label' => __('Tag Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'faq_tag',
                        'synonym' => 'faq_tag'
                    ],
                    [
                        'name' => 'label_glossary',
                        'label' => __('Glossary', 'wp-ai'),
                        'type' => 'hr',
                    ],
                    [
                        'name' => 'redirect_archivpage_uri_glossary',
                        'label' => __('Archive page', 'wp-ai'),
                        'description' => '',
                        'type' => 'select',
                        'options' => $pagelist,
                        'default' => ''
                    ],
                    [
                        'name' => 'custom_glossary_slug',
                        'label' => __('Glossary Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'bk_glossary',
                        'synonym' => 'bk_glossary'
                    ],
                    [
                        'name' => 'custom_glossary_category_slug',
                        'label' => __('Category Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'glossary_category',
                        'synonym' => 'glossary_category'

                    ],
                    [
                        'name' => 'custom_glossary_tag_slug',
                        'label' => __('Tag Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'glossary_tag',
                        'synonym' => 'glossary_tag'
                    ],
                    [
                        'name' => 'label_placeholder',
                        'label' => __('Placeholder', 'wp-ai'),
                        'type' => 'hr',
                    ],
                    [
                        'name' => 'redirect_archivpage_uri_placeholder',
                        'label' => __('Archive page', 'wp-ai'),
                        'description' => '',
                        'type' => 'select',
                        'options' => $pagelist,
                        'default' => ''
                    ],
                    [
                        'name' => 'custom_placeholder_slug',
                        'label' => __('Placeholder Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'bk_placeholder',
                        'synonym' => 'bk_placeholder'
                    ],
                    [
                        'name' => 'label_synonym',
                        'label' => __('Synonym', 'wp-ai'),
                        'type' => 'hr',
                    ],
                    [
                        'name' => 'redirect_archivpage_uri_synonym',
                        'label' => __('Archive page', 'wp-ai'),
                        'description' => '',
                        'type' => 'select',
                        'options' => $pagelist,
                        'default' => ''
                    ],
                    [
                        'name' => 'custom_synonym_slug',
                        'label' => __('Synonym Slug', 'wp-ai'),
                        'description' => '',
                        'type' => 'text',
                        'default' => 'bk_synonym',
                        'synonym' => 'bk_synonym'
                    ],
                ],
                'import' => [],
                'faqlog' => [
                    [
                        'name' => 'ANSWERSLOGFILE',
                        'type' => 'logfile',
                        'default' => Tools::getLogfilePath()
                    ]
                ]
            ],
            'lang' => [
                '' => __('All languages', 'wp-ai'),
                'de' => __('German', 'wp-ai'),
                'en' => __('English', 'wp-ai'),
                'es' => __('Spanish', 'wp-ai'),
                'fr' => __('French', 'wp-ai'),
                'ru' => __('Russian', 'wp-ai'),
                'zh' => __('Chinese', 'wp-ai')
            ],
        ];

        $tab = (!empty($_GET['tab']) ? $_GET['tab'] : '');

        if ($tab == 'domains' || $tab == 'import') {

            $syncAPI = new SyncAPI();
            $domains = $syncAPI->getDomains();

            foreach ($domains as $identifier => $url) {
                $defaults['fields']['import'][] = [
                    'name' => 'hr_' . $identifier,
                    'label' => $identifier . ' (' . $url . ')',
                    'type' => 'hr',
                ];

                if ($tab == 'import') {
                    $types = [
                        'faq' => 'FAQ',
                        'glossary' => __('Glossary', 'wp-ai')
                    ];

                    $filter = '';

                    foreach ($types as $type => $label) {

                        $cats = $syncAPI->getTaxonomies($url, 'bk_' . $type . '_category', $filter);
                        if (is_wp_error($cats)) {
                            $cats = [];
                        }
                        $options = [];

                        foreach ($cats as $slug => $name) {
                            $options[$slug] = $name;
                        }

                        if (!empty($cats)) {
                            $defaults['fields']['import'][] = [
                                'name' => $type . '_categories_' . $identifier,
                                'label' => $label . ' ' . __('Categories', 'wp-ai'),
                                'description' => __('Please select the categories you\'d like to fetch ' . $label . ' to.', 'wp-ai'),
                                'type' => 'select-multiple',
                                'options' => $options
                            ];
                        } else {
                            $defaults['fields']['import'][] = [
                                'name' => $type . '_categories_' . $identifier,
                                'label' => $label . ' ' . __('Categories', 'wp-ai'),
                                'description' => __('Please select the categories you\'d like to fetch ' . $label . ' to.', 'wp-ai'),
                                'type' => 'msg',
                                'synonym' => __('Category not found.', 'wp-ai')
                            ];
                        }
                    }
                }
            }
        }

        $defaults['fields']['import'][] = [
            'name' => 'hr_only',
            'label' => '',
            'type' => 'hr',
        ];

        $defaults['fields']['import'][] = [
            'name' => 'frequency',
            'label' => __('Synchronize automatically', 'wp-ai'),
            'description' => '',
            'default' => '',
            'options' => [
                '' => __('-- off --', 'wp-ai'),
                'daily' => __('daily', 'wp-ai'),
                'twicedaily' => __('twicedaily', 'wp-ai')
            ],
            'type' => 'select'
        ];

        return apply_filters('bk-wp_ai_defaults', $defaults);
    }

    /**
     * Retrieve a default value by key.
     *
     * @param string $key The key of the default.
     * @return mixed|null The value if found, or null.
     */
    public function get(string $key): mixed
    {
        return $this->defaults[$key] ?? null;
    }

    /**
     * Get all defaults.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->defaults;
    }

    /**
     * Prepends a deterministic, 6-char unique prefix to any key.
     *
     * @param string $key The raw key to namespace.
     * @return string The 6-char-prefixed key.
     */
    public function withPrefix(string $key = ''): string
    {
        $rawSlug = plugin()->getSlug();
        $clean = preg_replace('/[^a-z0-9]/', '', $rawSlug);

        $keep = min(3, strlen($clean));
        $part = substr($clean, 0, $keep);

        $needed = 6 - strlen($part);
        $hash = substr(md5($clean), 0, $needed);

        $prefix = $part . $hash;

        if (!preg_match('/^[a-z]/', $prefix)) {
            $prefix = 'p' . substr($prefix, 0, 5);
        }

        return $prefix . '_' . sanitize_key($key);
    }
}
