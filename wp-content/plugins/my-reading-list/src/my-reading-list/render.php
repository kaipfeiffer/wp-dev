<?php
$args  = array(
    'post_type' => 'book',
);
$books = get_posts($args);

$wrapper_attributes = get_block_wrapper_attributes();

$output  = '';
$output .= sprintf('<div %1$s>', $wrapper_attributes);
$output .= '<p>My Reading List – hello from the rendered content!</p>';

foreach ($books as $book) {
    $output .= '<div>';
    $output .= '<h2>' . $book->post_title . '</h2>';
    if ($attributes['showImage']) {
        $output .= get_the_post_thumbnail($book->ID, 'medium');
    }
    if ($attributes['showContent']) {
        $output .= $book->post_content;
    }
    $output .= '</div>';
}

$output .= '</div>';
echo $output;
