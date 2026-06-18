<?php
/* 
Template Name: Custom Taxonomy bk_faq_tag Template
*/

get_header();

?>

<main id="main" class="site-main wp-ai tag">

<?php

$post_type = 'bk_faq';
$taxonomy = 'bk_faq_tag';
include_once('template-parts/taxonomy.php');
?>
</main>

<?php
get_footer();