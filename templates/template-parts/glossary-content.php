<?php
/**
 * This is part of the templates for displaying the glossary entries
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/

namespace BK\WPAI\Common;

use BK\WPAI\Common\Tools;
$tools = new Tools();


echo '<div id="post-' . esc_attr(get_the_ID()) . '" class="' . esc_attr(implode(' ', get_post_class())) .'">';

?>



<h1 id="droppoint" class="glossary-title" itemprop="title"><?php the_title(); ?></h1>


<?php 

$postID = get_the_ID();
$cats = wp_kses_post($tools->getTermLinks( $postID, 'glossary_category' ));
$tags = wp_kses_post($tools->getTermLinks( $postID, 'glossary_tag' ));            
$details = '<article class="news-details">
<!-- bk-glossary --><p id="bk-glossary" class="meta-footer">'
. ( $cats ? '<span class="post-meta-categories"> '. __( 'Categories', 'wp-ai' ) . ': ' . $cats . '</span>' : '' )
. ( $tags ? '<span class="post-meta-tags"> '. __( 'Tags', 'wp-ai' ) . ': ' . $tags . '</span>' : '' )
. '</p></article>';

the_content(); 
echo $details;

wp_enqueue_style('wp-ai-css');
wp_enqueue_script('wp-ai-accordion');

echo '</div>';

