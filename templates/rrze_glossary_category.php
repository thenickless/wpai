<?php
/* 
Template Name: Custom Taxonomy faq_category Template
*/

get_header();

?>

<main id="main" class="site-main wp-ai category">

<?php

$post_type = 'bk_glossary';
$taxonomy = 'bk_glossary_category';
include_once('template-parts/taxonomy.php');
?>
</main>

<?php
get_footer();