<?php
/**
 * Drinks Search Module - Centralized WP_Query Operations
 * 
 * This class consolidates all WP_Query operations used throughout the theme and plugin.
 * Each query is documented with its   to explain what it searches and returns.
 * 
 * @package DrinksPlugin
 * @subpackage DrinksSearch
 */

if (!defined('ABSPATH')) {
    exit;
}

class DrinksSearch {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        // Module initialized
    }
    
    /**
     *   1: Get All Drink Posts
     * 
     * Retrieves all posts that have ANY term in the 'drinks' taxonomy.
     * No status filter - includes drafts, pending, etc.
     * 
     * Migrated from: plugins/drinks-plugin/drinks-plugin.php
     * 
     * Use Case: Get total count and complete list of all cocktail recipes
     * Returns: WP_Query object (useful when you need the query object itself)
     * 
     * @return WP_Query Query object with all drink posts
     */
    public function get_all_drink_posts_query() {
        $args = array(
            'post_type' => 'post',
            'tax_query' => array(
                array(
                    'taxonomy' => 'drinks', // Plural
                    'operator' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1
        );
        
        return new WP_Query($args);
    }
    
    /**
     *   2: Get Published Drink Posts
     * 
     * THIS is the FRONT END   for click and search-based carousels . 
     * Retrieves only published posts with 'drinks' taxonomy.
     * Filters: Must be published AND have drinks taxonomy
     * 
     * Migrated from: plugins/drinks-plugin/sync-drinks-metadata.php
     * 
     * Use Case: Sync metadata across all drink posts, bulk operations
     * Returns: Array of WP_Post objects (for direct manipulation)
     * 
     * @return array Array of WP_Post objects
     */
    public function get_published_drink_posts() {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'drinks',
                    'operator' => 'EXISTS'
                )
            )
        );
        
        $query = new WP_Query($args);
        $posts = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = get_post(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return $posts;
    }
    
    /**
     *   3: Get All Media Attachments
     * 
     * Retrieves ALL media files (images, PDFs, videos) from WordPress Media Library.
     * Filters: Must have a file path stored in _wp_attached_file meta
     * 
     * Migrated from: 3 duplicate implementations in cocktail-images module
     * 
     * Use Case: Media library audit/checker tools to verify file integrity
     * Returns: Array of attachment data including metadata
     * 
     * @return array Array of attachment data with full metadata
     */
    public function get_all_media_attachments() {
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_wp_attached_file',
                    'compare' => 'EXISTS'
                )
            )
        );
        
        $query = new WP_Query($args);
        $attachments = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $attachment_id = get_the_ID();
                
                // Get attachment metadata
                $metadata = wp_get_attachment_metadata($attachment_id);
                $file = get_attached_file($attachment_id);
                
                $attachments[] = [
                    'id' => $attachment_id,
                    'title' => get_the_title($attachment_id),
                    'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                    'caption' => get_the_excerpt($attachment_id),
                    'description' => get_the_content($attachment_id),
                    'file' => $file,
                    'url' => wp_get_attachment_url($attachment_id),
                    'metadata' => $metadata
                ];
            }
            wp_reset_postdata();
        }
        
        return $attachments;
    }
}