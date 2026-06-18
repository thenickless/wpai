<?php
/**
 * This is part of the templates for displaying the placeholder
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/

namespace WP AI\WPAI\Common;


echo '<div id="post-' . esc_attr(get_the_ID()) . '" class="' . esc_attr(implode(' ', get_post_class())) .'">';

?>



<h1 id="droppoint" class="glossary-title" itemprop="title"><?php the_title(); ?></h1>


<?php 

the_content(); 

// wp_enqueue_style('wp-ai-css');
// wp_enqueue_script('wp-ai-accordion');

echo '</div>';

