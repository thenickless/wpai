<?php
namespace WP AI\WPAI\Common\AdminInterfaces;

defined('ABSPATH') || exit;

class AdminMenu {
    // CPT-Slugs
    private $faq_pt       = 'bk_faq';
    private $glossary_pt  = 'bk_glossary';
    private $synonym_pt   = 'bk_synonym';

    private $faq_cat      = 'bk_faq_category';
    private $faq_tag      = 'bk_faq_tag';

    private $glossary_cat = 'bk_glossary_category';
    private $glossary_tag = 'bk_glossary_tag';

    private $syn_group    = 'bk_synonym_group';
    private $syn_tag      = 'bk_synonym_tag';

    private $parent_slug  = 'wp-ai';

    public function __construct() {
        add_filter('register_post_type_args', [$this, 'hideCptMenus'], 20, 2);

        add_action('admin_menu', [$this, 'registerMenus'], 9);

        add_filter('parent_file',  [$this, 'fixParentHighlight']);
        add_filter('submenu_file', [$this, 'fixSubmenuHighlight']);
    }

    public function hideCptMenus(array $args, string $post_type): array {
        $targets = [$this->faq_pt, $this->glossary_pt, $this->synonym_pt];
        if (in_array($post_type, $targets, true)) {
            $args['show_in_menu']       = false;
            $args['show_in_admin_bar']  = false;
        }
        return $args;
    }

    public function registerMenus(): void {
        add_menu_page(
            __('WP AI', 'wp-ai'),
            __('WP AI', 'wp-ai'),
            'edit_posts',
            $this->parent_slug,
            [$this, 'renderWP AIDashboard'],
            'dashicons-editor-help',
            25
        );

        add_submenu_page($this->parent_slug, __('FAQ', 'wp-ai'), __('FAQ', 'wp-ai'), 'edit_posts', 'bk-wp_ai_faq',      function () { $this->renderHub($this->faq_pt, $this->faq_cat, $this->faq_tag, __('FAQ', 'wp-ai')); });
        add_submenu_page($this->parent_slug, __('Glossary', 'wp-ai'), __('Glossary', 'wp-ai'), 'edit_posts', 'bk-wp_ai_glossary', function () { $this->renderHub($this->glossary_pt, $this->glossary_cat, $this->glossary_tag, __('Glossary', 'wp-ai')); });
        add_submenu_page($this->parent_slug, __('Synonym', 'wp-ai'), __('Synonym', 'wp-ai'), 'edit_posts', 'bk-wp_ai_synonym', function () { $this->renderHub($this->synonym_pt, $this->syn_group, $this->syn_tag, __('Synonym', 'wp-ai')); });
    }

    public function renderWP AIDashboard(): void {
        echo '<div class="wrap"><h1>'.esc_html__('WP AI', 'wp-ai').'</h1>';
        echo '<p>'.esc_html__('Choose a section:', 'wp-ai').'</p>';
        echo '<ul class="wp-ai-dashboard-cards">';
        $cards = [
            ['slug'=>'bk-wp_ai_faq',      'title'=>__('FAQ', 'wp-ai'),      'desc'=>__('Manage questions & wp_ai', 'wp-ai')],
            ['slug'=>'bk-wp_ai_glossary', 'title'=>__('Glossary', 'wp-ai'), 'desc'=>__('Manage glossary terms', 'wp-ai')],
            ['slug'=>'bk-wp_ai_synonym',  'title'=>__('Synonym', 'wp-ai'),  'desc'=>__('Manage synonyms & groups', 'wp-ai')],
        ];
        foreach ($cards as $c) {
            printf(
                '<li><h2>%s</h2><p>%s</p><a class="button button-primary" href="%s">%s</a></li>',
                esc_html($c['title']),
                esc_html($c['desc']),
                esc_url(admin_url('admin.php?page='.$c['slug'])),
                esc_html__('Open', 'wp-ai')
            );
        }
        echo '</ul></div>';
    }

    private function renderHub(string $post_type, string $tax_cat, string $tax_tag, string $title): void {
        $all_url  = admin_url('edit.php?post_type=' . $post_type);
        $add_url  = admin_url('post-new.php?post_type=' . $post_type);
        $cat_url  = admin_url('edit-tags.php?taxonomy=' . $tax_cat . '&post_type=' . $post_type);
        $tag_url  = admin_url('edit-tags.php?taxonomy=' . $tax_tag . '&post_type=' . $post_type);

        echo '<div class="wrap">';
        printf('<h1>%s</h1>', esc_html($title));
        echo '<div class="bk-hub">';
        $items = [
            ['label'=>sprintf(__('All %s', 'wp-ai'), $title), 'url'=>$all_url],
            ['label'=>sprintf(__('Add %s', 'wp-ai'), $title), 'url'=>$add_url],
            ['label'=>__('Categories', 'wp-ai'), 'url'=>$cat_url],
            ['label'=>__('Tags', 'wp-ai'), 'url'=>$tag_url],
        ];
        foreach ($items as $i) {
            printf(
                '<a class="button button-secondary" href="%s">%s</a>',
                esc_url($i['url']),
                esc_html($i['label'])
            );
        }
        echo '</div></div>';
    }

    public function fixParentHighlight($parent_file) {
        $screen = get_current_screen();
        if (!$screen) return $parent_file;

        $targets = [$this->faq_pt, $this->glossary_pt, $this->synonym_pt];
        if (in_array($screen->post_type, $targets, true) || in_array($screen->taxonomy ?? '', [$this->faq_cat, $this->faq_tag, $this->glossary_cat, $this->glossary_tag, $this->syn_group, $this->syn_tag], true)) {
            return $this->parent_slug;
        }
        return $parent_file;
    }

    public function fixSubmenuHighlight($submenu_file) {
        $screen = get_current_screen();
        if (!$screen) return $submenu_file;

        if ($screen->post_type === $this->faq_pt || in_array($screen->taxonomy ?? '', [$this->faq_cat, $this->faq_tag], true)) {
            return 'bk-wp_ai_faq';
        }
        if ($screen->post_type === $this->glossary_pt || in_array($screen->taxonomy ?? '', [$this->glossary_cat, $this->glossary_tag], true)) {
            return 'bk-wp_ai_glossary';
        }
        if ($screen->post_type === $this->synonym_pt || in_array($screen->taxonomy ?? '', [$this->syn_group, $this->syn_tag], true)) {
            return 'bk-wp_ai_synonym';
        }
        return $submenu_file;
    }
}
