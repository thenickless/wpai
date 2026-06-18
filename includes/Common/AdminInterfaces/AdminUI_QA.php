<?php
declare(strict_types=1);

namespace BK\WPAI\Common\AdminInterfaces;

defined('ABSPATH') || exit;

use BK\WPAI\Common\Tools;
use BK\WPAI\Defaults;


/**
 * Shared admin UI for bk_faq and bk_glossary.
 * - Same meta boxes (lang, sort, anchor)
 * - Same list-table columns (lang, sortfield, source)
 * - Same filtering (category, tag, source)
 */
class AdminUI_QA extends AdminUI
{
    /**
     * Output save nonce once for QA metaboxes.
     */
    private function renderMetaNonce(): void
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        wp_nonce_field($this->post_type . '_save_meta', $this->post_type . '_meta_nonce');
        $printed = true;
    }

    public function __construct(string $post_type)
    {
        parent::__construct($post_type, [
            'has_taxonomies' => true,
            'default_orderby' => 'title',
            'default_order' => 'ASC',
            'sortable_meta_keys' => ['sortfield'],
            'sync_readonly' => true,
            'show_shortcode_box' => true,
            'lang_quick_bulk_edit' => true,
        ]);
    }

    /* ---------------- Title synonym ---------------- */

    protected function get_title(): string
    {
        return ($this->post_type === 'bk_faq')
            ? __('Enter question here', 'wp-ai')
            : __('Enter glossary term here', 'wp-ai');
    }

    /* ---------------- Metaboxes ---------------- */

    protected function metaboxes(): array
    {
        return [
            [
                'id' => 'langbox',
                'title' => __('Language', 'wp-ai'),
                'callback' => [$this, 'langboxCallback'],
                'context' => 'side',
            ],
            [
                'id' => 'sortbox',
                'title' => __('Sort', 'wp-ai'),
                'callback' => [$this, 'sortboxCallback'],
                'context' => 'side',
            ],
            [
                'id' => 'anchorbox',
                'title' => __('Anchor', 'wp-ai'),
                'callback' => [$this, 'anchorboxCallback'],
                'context' => 'side',
            ],
        ];
    }

    public function langboxCallback($meta_id)
    {
        $this->renderMetaNonce();

        $current = get_post_meta($meta_id->ID, 'lang', true);
        if (empty($current)) {
            $current = substr(get_locale(), 0, 2);
        }

        $defaults = new Defaults();
        $langlist = $defaults->get('lang');


        $output = '<select name="lang" id="lang" class="lang">';
        foreach ($langlist as $code => $label) {
            $selected = selected($current, $code, false);
            $output .= sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr($code),
                $selected,
                esc_html($label)
            );
        }
        $output .= '</select>';
        $output .= '<p class="description">' . esc_html__('Language of this FAQ', 'wp-ai') . '</p>';

        echo wp_kses_post($output);
    }

    public function sortboxCallback(\WP_Post $post): void
    {
        $this->renderMetaNonce();

        // Hidden "source" to keep/update origin; defaults to website
        $source = (string) get_post_meta($post->ID, 'source', true);
        if ($source === '') {
            $source = 'website';
        }

        echo '<input type="hidden" name="source" id="source" value="' . esc_attr($source) . '">';

        $sortfield = (string) get_post_meta($post->ID, 'sortfield', true);
        echo '<input type="text" name="sortfield" id="sortfield" class="sortfield" value="' . esc_attr($sortfield) . '">';
        echo '<p class="description">' . esc_html__('Criterion for sorting the output of the shortcode', 'wp-ai') . '</p>';
    }

    public function anchorboxCallback(\WP_Post $post): void
    {
        $this->renderMetaNonce();

        $anchorfield = (string) get_post_meta($post->ID, 'anchorfield', true);
        echo '<input type="text" name="anchorfield" id="anchorfield" class="anchorfield" value="' . esc_attr($anchorfield) . '">';
        echo '<p class="description">' . esc_html__('Anchor field (optional) to define jump marks when displayed in accordions', 'wp-ai') . '</p>';
    }

    /* ---------------- Classic editor shortcode helper ---------------- */

    // public function renderShortcodeBox(): void
    // {
    //     global $post;
    //     if (!$post || (int) $post->ID <= 0) {
    //         return;
    //     }

    //     $ret = '';
    //     $category = '';
    //     $tag = '';

    //     // Build taxonomy slug lists (comma separated)
    //     foreach (["{$this->post_type}_category", "{$this->post_type}_tag"] as $tax) {
    //         $terms = wp_get_post_terms($post->ID, $tax);
    //         $list = '';
    //         foreach ($terms as $t) {
    //             $list .= $t->slug . ', ';
    //         }
    //         $list = rtrim($list, ', ');
    //         if ($tax === "{$this->post_type}_category") {
    //             $category = $list;
    //         } else {
    //             $tag = $list;
    //         }
    //     }

    //     // Keep original plugin’s shortcode style (always [faq])
    //     $ret .= '<h3 class="hndle">' . esc_html__('Single entries', 'wp-ai') . ':</h3><p>[faq id="' . (int) $post->ID . '"]</p>';
    //     if ($category) {
    //         $ret .= '<h3 class="hndle">' . esc_html__('Accordion with category', 'wp-ai') . ':</h3><p>[faq category="' . esc_html($category) . '"]</p>';
    //         $ret .= '<p>' . esc_html__('If there is more than one category listed, use at least one of them.', 'wp-ai') . '</p>';
    //     }
    //     if ($tag) {
    //         $ret .= '<h3 class="hndle">' . esc_html__('Accordion with tag', 'wp-ai') . ':</h3><p>[faq tag="' . esc_html($tag) . '"]</p>';
    //         $ret .= '<p>' . esc_html__('If there is more than one tag listed, use at least one of them.', 'wp-ai') . '</p>';
    //     }
    //     $ret .= '<h3 class="hndle">' . esc_html__('Accordion with all entries', 'wp-ai') . ':</h3><p>[faq]</p>';

    //     echo wp_kses_post($ret);
    // }

    /* ---------------- Save meta ---------------- */

    public function savePostMeta(int $post_id): void
    {
        // Capability & autosave guards
        if (!current_user_can('edit_post', $post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return;
        }

        if (!empty($_REQUEST['bulk_edit']) || isset($_POST['_inline_edit'])) {
            return;
        }

        // Nonce must be present and valid
        $nonce_field = $this->post_type . '_meta_nonce';
        if (!isset($_POST[$nonce_field]) || !wp_verify_nonce(wp_unslash((string) $_POST[$nonce_field]), $this->post_type . '_save_meta')) {
            return;
        }

        // Sanitize incoming fields
        $source = isset($_POST['source']) ? sanitize_text_field(wp_unslash((string) $_POST['source'])) : 'website';
        update_post_meta($post_id, 'source', $source);

        $lang = (isset($_POST['lang']) && $_POST['lang'] !== '')
            ? sanitize_text_field(wp_unslash((string) $_POST['lang']))
            : (get_post_meta($post_id, 'lang', true) === '' ? substr(get_locale(), 0, 2) : (string) get_post_meta($post_id, 'lang', true));
        update_post_meta($post_id, 'lang', $lang);

        update_post_meta($post_id, 'remoteID', $post_id);
        update_post_meta($post_id, 'remoteChanged', get_post_timestamp($post_id, 'modified'));

        if (isset($_POST['sortfield'])) {
            update_post_meta($post_id, 'sortfield', sanitize_text_field(wp_unslash((string) $_POST['sortfield'])));
        }
        if (isset($_POST['anchorfield'])) {
            update_post_meta($post_id, 'anchorfield', sanitize_title(wp_unslash((string) $_POST['anchorfield'])));
        }
    }

    /* ---------------- List table: posts ---------------- */

    protected function listTableColumns(array $cols): array
    {
        // Rename title column depending on CPT
        $cols['title'] = ($this->post_type === 'bk_faq') ? __('Question', 'wp-ai') : __('Glossary', 'wp-ai');
        $cols['lang'] = __('Language', 'wp-ai');
        $cols['sortfield'] = __('Sort criterion', 'wp-ai');

        if ((new Tools())->hasSync($this->post_type)) {
            $cols['source'] = __('Source', 'wp-ai');
        }
        return $cols;
    }

    protected function listTableSortableColumns(array $cols): array
    {
        $cols["taxonomy-{$this->post_type}_category"] = __('Category', 'wp-ai');
        $cols["taxonomy-{$this->post_type}_tag"] = __('Tag', 'wp-ai');
        $cols['lang'] = __('Language', 'wp-ai');
        $cols['sortfield'] = __('Sort by', 'wp-ai');

        if ((new Tools())->hasSync($this->post_type)) {
            $cols['source'] = __('Source', 'wp-ai');
        }
        return $cols;
    }

    protected function renderListTableColumn(string $col, int $post_id): void
    {
        if ($col === 'lang') {
            echo esc_html((string) get_post_meta($post_id, 'lang', true));
        } elseif ($col === 'source' && (new Tools())->hasSync($this->post_type)) {
            echo esc_html((string) get_post_meta($post_id, 'source', true));
        } elseif ($col === 'sortfield') {
            echo esc_html((string) get_post_meta($post_id, 'sortfield', true));
        }
    }

    /* ---------------- List table: taxonomies ---------------- */

    protected function taxonomyColumns(array $cols): array
    {
        $cols['lang'] = __('Language', 'wp-ai');
        if ((new Tools())->hasSync($this->post_type)) {
            $cols['source'] = __('Source', 'wp-ai');
        }
        return $cols;
    }

    protected function renderTaxonomyColumn(string $col, int $term_id): ?string
    {
        if ($col === 'lang') {
            return esc_html((string) get_term_meta($term_id, 'lang', true));
        }

        if ($col === 'source' && (new Tools())->hasSync($this->post_type)) {
            return esc_html((string) get_term_meta($term_id, 'source', true));
        }

        return null;
    }
}
