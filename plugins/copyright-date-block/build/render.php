<?php
    $block_props = get_block_wrapper_attributes();
    $starting_year = $attributes['startingYear'];
    $show_start_year = isset( $attributes['showStartYear'] ) ? $attributes['showStartYear'] : true;
    $show_copyright = isset( $attributes['showCopyright'] ) ? $attributes['showCopyright'] : true;
    $current_year = wp_date( 'Y' );
    
    // Build copyright text based on settings
    $copyright_word = $show_copyright ? 'Copyright ' : '';
    
    if ( $show_start_year && $starting_year !== $current_year ) {
        $copyright_text = "{$copyright_word}© {$starting_year} - {$current_year}";
    } else {
        $copyright_text = "{$copyright_word}© {$current_year}";
    }
?>
<span <?php echo $block_props?>>
    <?php echo $copyright_text; ?>
</span>