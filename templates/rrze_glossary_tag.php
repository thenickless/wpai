<?php
/* 
Template Name: Custom Taxonomy bk_glossary_tag Template
*/

get_header();

?>

<main id="main" class="site-main wp-ai tag">

<?php

$post_type = 'bk_glossary';
$taxonomy = 'bk_glossary_tag';
include_once('template-parts/taxonomy.php');
?>
</main>

<?php
get_footer();