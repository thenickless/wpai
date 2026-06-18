<?php

namespace WP AI\WPAI\Common\Shortcode;

defined('ABSPATH') || exit;

use function WP AI\WPAI\plugin;


use WP AI\WPAI\Common\Tools;
/**
 * Shortcode
 */
class ShortcodeGlossary
{



    private $settings = '';
    private $pluginname = '';

    private $bSchema = false;

    public function __construct()
    {
        $this->settings = $this->getShortcodeSettings();
        $this->pluginname = $this->settings['block']['blockname'];
        add_shortcode('glossary', [$this, 'shortcodeOutput']);
        add_shortcode('fau_glossar', [$this, 'shortcodeOutput']);
        add_action('admin_head', [$this, 'setMCEConfig']);
        add_filter('mce_external_plugins', [$this, 'addMCEButtons']);
    }

    function getShortcodeSettings()
    {
        return [
            'block' => [
                'blocktype' => 'wp-ai/glossary',
                'blockname' => 'glossary',
                'title' => 'WP AI Glossary',
                'category' => 'widgets',
                'icon' => 'book',
                'tinymce_icon' => 'info',
            ],
            'register' => [
                'values' => [
                    [
                        'id' => '',
                        'val' => __('none', 'wp-ai')
                    ],
                    [
                        'id' => 'category',
                        'val' => __('Categories', 'wp-ai')
                    ],
                    [
                        'id' => 'tag',
                        'val' => __('Tags', 'wp-ai')
                    ]
                ],
                'default' => '',
                'field_type' => 'select',
                'label' => __('Register content', 'wp-ai'),
                'type' => 'string'
            ],
            'registerstyle' => [
                'values' => [
                    [
                        'id' => '',
                        'val' => __('-- hidden --', 'wp-ai')
                    ],
                    [
                        'id' => 'a-z',
                        'val' => __('A - Z', 'wp-ai')
                    ],
                    [
                        'id' => 'tagcloud',
                        'val' => __('Tagcloud', 'wp-ai')
                    ],
                    [
                        'id' => 'tabs',
                        'val' => __('Tabs', 'wp-ai')
                    ]
                ],
                'default' => '',
                'field_type' => 'select',
                'label' => __('Register style', 'wp-ai'),
                'type' => 'string'
            ],
            'category' => [
                'default' => '0',
                'field_type' => 'text',
                'label' => __('Categories', 'wp-ai'),
                'type' => 'string'
            ],
            'tag' => [
                'default' => 0,
                'field_type' => 'text',
                'label' => __('Tags', 'wp-ai'),
                'type' => 'string'
            ],
            'id' => [
                'default' => NULL,
                'field_type' => 'text',
                'label' => __('Glossary', 'wp-ai'),
                'type' => 'number'
            ],
            'masonry' => [
                'field_type' => 'toggle',
                'label' => __('Grid', 'wp-ai'),
                'type' => 'boolean',
                'default' => FALSE,
                'checked' => FALSE
            ],
            'search' => [
                'field_type' => 'toggle',
                'label' => __('Show search field', 'wp-ai'),
                'type' => 'boolean',
                'default' => FALSE,
                'checked' => FALSE
            ],
            'hide_accordion' => [
                'field_type' => 'toggle',
                'label' => __('Hide accordion', 'wp-ai'),
                'type' => 'boolean',
                'default' => FALSE,
                'checked' => FALSE
            ],
            'hide_title' => [
                'field_type' => 'toggle',
                'label' => __('Hide title', 'wp-ai'),
                'type' => 'boolean',
                'default' => FALSE,
                'checked' => FALSE
            ],
            'expand_all_link' => [
                'field_type' => 'toggle',
                'label' => __('Show "expand all" button', 'wp-ai'),
                'type' => 'boolean',
                'default' => FALSE,
                'checked' => FALSE
            ],
            'load_open' => [
                'field_type' => 'toggle',
                'label' => __('Load website with opened accordions', 'wp-ai'),
                'type' => 'boolean',
                'default' => FALSE,
                'checked' => FALSE
            ],
            'color' => [
                'values' => [
                    [
                        'id' => 'med',
                        'val' => 'med'
                    ],
                    [
                        'id' => 'nat',
                        'val' => 'nat'
                    ],
                    [
                        'id' => 'rw',
                        'val' => 'rw'
                    ],
                    [
                        'id' => 'phil',
                        'val' => 'phil'
                    ],
                    [
                        'id' => 'tk',
                        'val' => 'tk'
                    ],
                ],
                'default' => 'tk',
                'field_type' => 'select',
                'label' => __('Color', 'wp-ai'),
                'type' => 'string'
            ],
            'style' => [
                'values' => [
                    [
                        'id' => '',
                        'val' => __('none', 'wp-ai')
                    ],
                    [
                        'id' => 'light',
                        'val' => 'light'
                    ],
                    [
                        'id' => 'dark',
                        'val' => 'dark'
                    ],
                ],
                'default' => '',
                'field_type' => 'select',
                'label' => __('Style', 'wp-ai'),
                'type' => 'string'
            ],
            'additional_class' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Additonal CSS-class(es) for sourrounding DIV', 'wp-ai'),
                'type' => 'string'
            ],
            'lang' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Language', 'wp-ai'),
                'type' => 'string'
            ],
            'sort' => [
                'values' => [
                    [
                        'id' => 'title',
                        'val' => __('Title', 'wp-ai')
                    ],
                    [
                        'id' => 'id',
                        'val' => __('ID', 'wp-ai')
                    ],
                    [
                        'id' => 'sortfield',
                        'val' => __('Sort field', 'wp-ai')
                    ],
                ],
                'default' => 'title',
                'field_type' => 'select',
                'label' => __('Sort', 'wp-ai'),
                'type' => 'string'
            ],
            'order' => [
                'values' => [
                    [
                        'id' => 'ASC',
                        'val' => __('ASC', 'wp-ai')
                    ],
                    [
                        'id' => 'DESC',
                        'val' => __('DESC', 'wp-ai')
                    ],
                ],
                'default' => 'ASC',
                'field_type' => 'select',
                'label' => __('Order', 'wp-ai'),
                'type' => 'string'
            ],
            'hstart' => [
                'default' => 2,
                'field_type' => 'text',
                'label' => __('Heading level of the first heading', 'wp-ai'),
                'type' => 'number'
            ],
        ];
    }



    /**
     * Outputs FAQs based on taxonomies (category/tag) or glossary view.
     * 
     * Supports classic and alphabetical output, tabs or tag cloud display.
     * 
     * @param array $atts Original shortcode attributes
     * @param string $hstart HTML heading level
     * @param string $style Inline styles for the accordion
     * @param string $expand_all_link Attribute for “expand all” link
     * @param bool $hide_accordion Whether the accordion should be suppressed
     * @param bool $hide_title Whether the title should be suppressed
     * @param string $color Color attribute
     * @param string $load_open Attribute for open state
     * @param string $sort Sort criterion (title, id, sortfield)
     * @param string $order Sort order
     * @param mixed $category Category(ies) as string or array
     * @param mixed $tag Tag(s) as string or array
     * @param string $register “category” or “tag”
     * @param string $registerstyle “a-z”, “tabs”, “tagcloud” or empty
     * @return string Rendered HTML content
     */
    private function renderFilteredItems(array $atts, string $hstart, string $style, string $expand_all_link, bool $hide_accordion, bool $hide_title, string $color, string $load_open, string $sort, string $order, $category, $tag, string $register, string $registerstyle): string
    {
        $content = '';
        $this->bSchema = false;

        // attribute category or tag is given or none of them
        $aLetters = [];
        $tax_query = '';

        $postQuery = array('post_type' => 'bk_glossary', 'post_status' => 'publish', 'numberposts' => -1, 'suppress_filters' => false);
        if ($sort == 'sortfield') {
            $postQuery['orderby'] = array(
                'meta_value' => $order, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                'title' => $order,
            );
            $postQuery['meta_key'] = 'sortfield'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        } else {
            $postQuery['orderby'] = $sort;
            $postQuery['order'] = $order;
        }

        // filter by category and/or tag and -if given- by domain related to category/tag, too
        $aTax = [];
        $aTax['bk_glossary_category'] = Tools::getTaxBySource($category);
        $aTax['bk_glossary_tag'] = Tools::getTaxBySource($tag);
        $aTax = array_filter($aTax); // delete empty entries

        if ($aTax) {
            $tax_query = Tools::getTaxQuery($aTax);
            if ($tax_query) {
                $postQuery['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            }
        }

        $metaQuery = [];
        $lang = $atts['lang'] ? trim($atts['lang']) : '';

        if ($lang) {
            $metaQuery[] = [
                'key' => 'lang',
                'value' => $lang,
                'compare' => '=',
            ];
        }

        $source = !empty($atts['domain']) ?
            array_filter(array_map('trim', explode(',', $atts['domain']))) :
            [];
        if ($source) {
            $metaQuery[] = [
                'key' => 'source',
                'value' => $source,
                'compare' => 'IN',
            ];
        }

        if ($metaQuery) {
            $postQuery['meta_query'] = array_merge([ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'AND'
            ], $metaQuery);
        }

        $posts = get_posts($postQuery);

        if ($posts) {
            if ($register) {
                // attribut register is given
                // get all used tags or categories
                $aUsedTerms = [];
                $aPostIDs = [];
                foreach ($posts as $post) {
                    // get all tags for each post
                    $aTermIds = [];
                    $valid_term_ids = [];
                    if ($register == 'category' && $category) {
                        if (!is_array($category)) {
                            $aCats = array_map('trim', explode(',', $category));
                        } else {
                            $aCats = $category;
                        }
                        foreach ($aCats as $slug) {
                            $filter_term = get_term_by('slug', $slug, 'bk_glossary_category');
                            if ($filter_term) {
                                $valid_term_ids[] = $filter_term->term_id;
                            }
                        }
                    } elseif ($register == 'tag' && $tag) {
                        if (!is_array($tag)) {
                            $aTags = array_map('trim', explode(',', $tag));
                        } else {
                            $aTags = $tag;
                        }
                        foreach ($aTags as $slug) {
                            $filter_term = get_term_by('slug', $slug, 'bk_glossary_tag');
                            if ($filter_term) {
                                $valid_term_ids[] = $filter_term->term_id;
                            }
                        }
                    }
                    $terms = wp_get_post_terms($post->ID, 'bk_glossary_' . $register);
                    if ($terms) {
                        foreach ($terms as $t) {
                            $term_id = 0;
                            $term_name = '';

                            if (is_object($t)) {
                                $term_id = isset($t->term_id) ? (int) $t->term_id : 0;
                                $term_name = isset($t->name) ? (string) $t->name : '';
                            } elseif (is_array($t)) {
                                $term_id = isset($t['term_id']) ? (int) $t['term_id'] : 0;
                                $term_name = isset($t['name']) ? (string) $t['name'] : '';
                            }

                            if (!$term_id || $term_name === '') {
                                continue;
                            }

                            if ($valid_term_ids && in_array($term_id, $valid_term_ids, true) === false) {
                                continue;
                            }

                            $aTermIds[] = $term_id;
                            $letter = Tools::getLetter($term_name);
                            $aLetters[$letter] = true;
                            $aUsedTerms[$term_name] = array('letter' => $letter, 'ID' => $term_id);
                            $aPostIDs[$term_id][] = $post->ID;
                        }
                    }
                }
                ksort($aUsedTerms);
                $anchor = 'ID';
                if ($aLetters) {
                    switch ($registerstyle) {
                        case 'a-z':
                            $content = Tools::createAZ($aLetters);
                            $anchor = 'letter';
                            break;
                        case 'tabs':
                            $content = Tools::createTabs($aUsedTerms, $aPostIDs);
                            break;
                        case 'tagcloud':
                            $content = Tools::createTagCloud($aUsedTerms, $aPostIDs);
                            break;
                    }
                }

                $last_anchor = '';
                foreach ($aUsedTerms as $k => $aVal) {
                    if ($registerstyle == 'a-z' && $content) {
                        $content .= ($last_anchor != $aVal[$anchor] ? '<h2 id="' . $anchor . '-' . $aVal[$anchor] . '">' . esc_html($aVal[$anchor]) . '</h2>' : '');
                    }

                    $term_id_attr = $anchor . '-' . $aVal[$anchor];
                    $content .= '<section id="' . esc_attr($term_id_attr) . '" class="wp-ai-item is-' . $color . '">';
                    $content .= '<h3>' . esc_html($k) . '</h3>';

                    $content .= '<div class="wp_ai-term-content">';

                    // find the postIDs to this tag
                    $aIDs = Tools::searchArrayByKey($aVal['ID'], $aPostIDs);

                    foreach ($aIDs as $ID) {
                        $source = get_post_meta($ID, "source", true);
                        $useSchema = ($source === 'website');

                        if ($useSchema) {
                            $this->bSchema = true;
                        }

                        $question = get_the_title($ID);
                        $answer = str_replace(']]>', ']]&gt;', apply_filters('the_content', get_post_field('post_content', $ID)));
                        $anchorfield = get_post_meta($ID, 'anchorfield', true);

                        if (empty($anchorfield)) {
                            $anchorfield = 'innerID-' . $ID;
                        }

                        $content .= Tools::renderItemAccordion('glossary', $anchorfield, $question, $answer, $color, $load_open, $useSchema);
                    }

                    $content .= '</div></section>';
                    $last_anchor = $aVal[$anchor];
                }
            } else {
                // attribut register is not given
                $last_anchor = '';
                foreach ($posts as $post) {
                    $source = get_post_meta($post->ID, "source", true);
                    $useSchema = ($source === 'website');

                    if ($useSchema) {
                        $this->bSchema = true;
                    }

                    $question = get_the_title($post->ID);
                    $answer = str_replace(']]>', ']]&gt;', apply_filters('the_content', get_post_field('post_content', $post->ID)));

                    $letter = Tools::getLetter($question);
                    $aLetters[$letter] = true;

                    if (!$hide_accordion) {
                        $anchorfield = get_post_meta($post->ID, 'anchorfield', true);

                        if (empty($anchorfield)) {
                            $anchorfield = 'ID-' . $post->ID;
                        }

                        if ($registerstyle == 'a-z' && count($posts) > 1) {
                            $content .= ($last_anchor != $letter ? '<h2 id="letter-' . $letter . '">' . $letter . '</h2>' : '');
                        }

                        $content .= Tools::renderItemAccordion('glossary', $anchorfield, $question, $answer, $color, $load_open, $useSchema);
                    } else {
                        $content .= Tools::renderItem('glossary', $question, $answer, $hstart, $useSchema, $hide_title);
                    }
                    $last_anchor = $letter;
                }

                if ($aLetters) {
                    switch ($registerstyle) {
                        case 'a-z':
                            $content = Tools::createAZ($aLetters) . $content;
                            $anchor = 'letter';
                            break;
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Translates composite shortcode attributes into individual properties
     * 
     * Splits the values of attributes such as “register”, “hide”, “show” and “class” into sub-terms 
     * and assigns these to logical individual fields in the attribute array. This simplifies further internal processing.
     * 
     * @param array $atts Reference to the shortcode attribute array
     * @return void
     */
    private function translateNewAttributes(array &$atts): void
    {
        // translate new attributes
        if (isset($atts['register'])) {
            $parts = explode(' ', $atts['register']);
            foreach ($parts as $part) {
                $part = trim($part);
                switch ($part) {
                    case 'category':
                    case 'tag':
                        $atts['register'] = $part;
                        break;
                    case 'a-z':
                    case 'tabs':
                    case 'tagcloud':
                        $atts['registerstyle'] = $part;
                        break;
                }
            }
        }

        if (isset($atts['hide'])) {
            $parts = explode(' ', $atts['hide']);
            foreach ($parts as $part) {
                $part = trim($part);
                switch ($part) {
                    case 'title':
                        $atts['hide_title'] = true;
                        break;
                    case 'accordion':
                    case 'accordeon':
                        $atts['hide_accordion'] = true;
                        break;
                    case 'register':
                        $atts['registerstyle'] = '';
                        break;
                }
            }
        }

        if (isset($atts['show'])) {
            $parts = explode(' ', $atts['show']);
            foreach ($parts as $part) {
                $part = trim($part);
                switch ($part) {
                    case 'expand-all-link':
                        $atts['expand_all_link'] = ' expand-all-link="true"';
                        break;
                    case 'load-open':
                        $atts['load_open'] = ' load="open"';
                        break;
                    case 'search':
                        $atts['search'] = true;
                        break;
                }
            }
        }

        $atts['additional_class'] = isset($atts['additional_class']) ? $atts['additional_class'] : '';
        if (isset($atts['class'])) {
            $parts = explode(' ', $atts['class']);
            foreach ($parts as $part) {
                $part = trim($part);
                switch ($part) {
                    case 'med':
                    case 'nat':
                    case 'phil':
                    case 'rw':
                    case 'tk':
                        $atts['color'] = $part;
                        break;
                    default:
                        $atts['additional_class'] .= ' ' . $part;
                        break;
                }
            }
        }

        $atts['sort'] = (isset($atts['sort']) && ($atts['sort'] == 'title' || $atts['sort'] == 'id' || $atts['sort'] == 'sortfield')) ? $atts['sort'] : 'title';

        $atts['expand_all_link'] = (isset($atts['expand_all_link']) && $atts['expand_all_link'] ? ' expand-all-link="true"' : '');
        $atts['load_open'] = (isset($atts['load_open']) && $atts['load_open'] ? ' load="open"' : '');
    }


    /**
     * Generate the shortcode output
     * @param array $atts Shortcode attributes
     * @param string $content Enclosed content
     * @return string Return the content
     */
    public function shortcodeOutput($atts, $content = null, $shortcode_tag = '')
    {
        // Workaround - see: https://github.com/wp-ai/wp-ai/issues/132#issuecomment-2839668060
        if (($skip = Tools::preventGutenbergDoubleBracketBug($shortcode_tag)) !== false) {
            return $skip;
        }

        if (empty($atts)) {
            $atts = [];
        } else {
            $atts = array_map('sanitize_text_field', $atts);
        }

        $this->translateNewAttributes($atts);

        // merge given attributes with default ones
        $atts_default = [];
        foreach ($this->settings as $k => $v) {
            if ($k != 'block') {
                $atts_default[$k] = $v['default'];
            }
        }

        $atts = shortcode_atts($atts_default, $atts);
        extract($atts);

        $content = '';
        $search = !empty($atts['search']);
        $register = (string) ($register ?? '');
        $registerstyle = (string) ($registerstyle ?? '');
        $hide_title = (isset($hide_title) ? $hide_title : false);
        $color = (isset($color) ? $color : '');
        $style = (isset($style) ? 'style="' . $style . '"' : '');

        $gutenberg = (is_array($id) ? true : false);

        if ($id && (!$gutenberg || $gutenberg && $id[0])) {
            $content = $this->renderExplicitItems($id, $gutenberg, $hstart, $style, $masonry, $expand_all_link, $hide_accordion, $hide_title, $color, $load_open);
        } else {
            $content = $this->renderFilteredItems($atts, $hstart, $style, $expand_all_link, $hide_accordion, $hide_title, $color, $load_open, $sort, $order, $category, $tag, $register, $registerstyle);
        }

        // 2020-05-12 THIS IS NOT IN USE because f.e. [faq glossary="category"] led to errors ("TypeError: e.$slides is null slick.min.js" and "TypeError: can't access property "add"" ) as FAQ can have >1 category and so equal sliders would be returned in output which leads to JS errors that avoid accordeons to work properly
        // => sliders are not syncable / this info is provided to the user during Sync and in Logfile
        // check if theme 'FAU-Einrichtungen' and [gallery ...] is in use
        // if ( ( wp_get_theme()->Name == 'FAU-Einrichtungen' ) && ( strpos( $content, 'slider') !== false ) ) {
        //     wp_enqueue_script( 'fau-js-heroslider' );
        // }

        $postID = get_the_ID();
        $headerID = (new Tools())->getHeaderID($postID);

        wp_enqueue_script('wp-ai-accordion');
        wp_enqueue_style('wp-ai-css');

        if ($search) {
            wp_enqueue_script('wp-ai-search');
        }

        $content = Tools::renderWrapper('glossary', $content, $headerID, $masonry, $color, $additional_class, $this->bSchema, $postID, $search);

        return $content;

    }


    /**
     * Outputs explicitly requested FAQs as accordion or simple content.
     *
     * Supports both Gutenberg blocks (multiple IDs as an array) and the classic editor (comma-separated).
     *
     * @param mixed $id Single ID or array of IDs
     * @param bool $gutenberg Whether Gutenberg is used
     * @param string $hstart HTML heading level
     * @param string $style Inline styles for the accordion
     * @param bool $masonry Whether tiles should be displayed (fake masonry - see https://github.com/wp-ai/wp-ai/issues/105#issuecomment-2873361435 )
     * @param string $expand_all_link Attribute for “expand all” link
     * @param bool $hide_accordion Whether the accordion should be suppressed
     * @param bool $hide_title Whether the title should be suppressed
     * @param string $color Color attribute of the accordion
     * @param string $load_open Attribute for open state
     * @return string The generated HTML content
     */
    private function renderExplicitItems($id, bool $gutenberg, string $hstart, string $style, bool $masonry, string $expand_all_link, bool $hide_accordion, bool $hide_title, string $color, string $load_open): string
    {
        $content = '';
        $this->bSchema = false;

        // EXPLICIT FAQ(s)
        if ($gutenberg) {
            $aIDs = $id;
        } else {
            // classic editor
            $aIDs = explode(',', $id);
        }

        foreach ($aIDs as $id) {
            $id = trim($id);
            if ($id) {
                $question = get_the_title($id);
                $anchorfield = get_post_meta($id, 'anchorfield', true);

                if (empty($anchorfield)) {
                    $anchorfield = 'ID-' . $id;
                }

                $answer = str_replace(']]>', ']]&gt;', apply_filters('the_content', get_post_field('post_content', $id)));
                $useSchema = (get_post_meta($id, 'source', true) === 'website');

                if ($useSchema) {
                    $this->bSchema = true;
                }

                if ($hide_accordion) {
                    $content .= Tools::renderItem('faq', $question, $answer, $hstart, $useSchema, $hide_title);
                } else {
                    $content .= Tools::renderItemAccordion('faq', $anchorfield, $question, $answer, $color, $load_open, $useSchema);
                }
            }
        }

        return $content;
    }


    public function setMCEConfig()
    {
        $shortcode = '';
        foreach ($this->settings as $att => $details) {
            if ($att != 'block') {
                $shortcode .= ' ' . $att . '=""';
            }
        }
        $shortcode = '[' . $this->pluginname . ' ' . $shortcode . ']';
        ?>
        <script type='text/javascript'>
            tmp = [{
                'name': <?php echo json_encode($this->pluginname); ?>,
                'title': <?php echo json_encode($this->settings['block']['title']); ?>,
                'icon': <?php echo json_encode($this->settings['block']['tinymce_icon']); ?>,
                'shortcode': <?php echo json_encode($shortcode); ?>,
            }];
            phpvar = (typeof phpvar === 'undefined' ? tmp : phpvar.concat(tmp)); 
        </script>
        <?php
    }

    public function addMCEButtons($pluginArray)
    {
        if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
            $pluginArray['bk_shortcode'] = plugin()->getUrl() . 'assets/js/tinymce-shortcodes.js';
        }
        return $pluginArray;
    }
}




