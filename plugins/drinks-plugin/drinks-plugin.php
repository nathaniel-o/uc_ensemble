<?php
/**
 * Plugin Name: Drinks - Lightbox
 * Plugin URI: https://example.com/drinks-plugin
 * Description: A plugin for displaying drinks with LightBox and Carousel functionality using WordPress Interactivity API
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: drinks-plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DRINKS_PLUGIN_VERSION', '1.0.0');
define('DRINKS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DRINKS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Drinks Plugin Class
 */
class DrinksPlugin {
    
    private $lightbox_images = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'display_drinks_message'));
        add_action('wp_footer', array($this, 'display_drinks_lightbox'));
        add_action('wp_head', array($this, 'add_lightbox_styles'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_filter('render_block_core/image', array($this, 'modify_image_block'), 10, 2);
        add_action('wp_footer', array($this, 'set_lightbox_state'), 5);
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Plugin initialization code will go here
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue WordPress Interactivity API
        wp_enqueue_script('wp-interactivity');
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'drinks-plugin-editor',
            DRINKS_PLUGIN_URL . 'js/editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            DRINKS_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'drinks-plugin-editor-style',
            DRINKS_PLUGIN_URL . 'css/editor.css',
            array(),
            DRINKS_PLUGIN_VERSION
        );
    }
    
    /**
     * Set Lightbox state for all collected images
     */
    public function set_lightbox_state() {
        if (!empty($this->lightbox_images)) {
            // Debug: Log the collected images
            error_log('Drinks Plugin: Setting lightbox state with ' . count($this->lightbox_images) . ' images');
            
            wp_interactivity_state('drinks-plugin', array(
                'metadata' => $this->lightbox_images
            ));
        } else {
            error_log('Drinks Plugin: No lightbox images collected');
        }
    }
    
    /**
     * Modify image block to add Lightbox functionality
     */
    public function modify_image_block($block_content, $block) {
        // Only modify if this is a core/image block
        if ($block['blockName'] !== 'core/image') {
            return $block_content;
        }
        
        // Check if Lightbox is enabled (default to true)
        $lightbox_enabled = isset($block['attrs']['lightboxEnabled']) ? $block['attrs']['lightboxEnabled'] : true;
        
        if (!$lightbox_enabled) {
            return $block_content;
        }
        
        // Parse the block content to find the image
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($block_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $figure = $dom->getElementsByTagName('figure')->item(0);
        if (!$figure) {
            error_log('Drinks Plugin: No figure found in image block');
            return $block_content;
        }
        
        $img = $figure->getElementsByTagName('img')->item(0);
        if (!$img) {
            error_log('Drinks Plugin: No img found in image block');
            return $block_content;
        }
        
        // Get image attributes
        $src = $img->getAttribute('src');
        $alt = $img->getAttribute('alt');
        $caption = '';
        
        // Get caption if it exists
        $figcaption = $figure->getElementsByTagName('figcaption')->item(0);
        if ($figcaption) {
            $caption = $figcaption->textContent;
        }
        
        // Generate unique ID
        $unique_id = uniqid('drink-lightbox-');
        
        // Store image data for later state setting
        $this->lightbox_images[$unique_id] = array(
            'uploadedSrc' => $src,
            'alt' => $alt,
            'caption' => $caption,
            'ariaLabel' => __('Enlarge image', 'drinks-plugin')
        );
        
        error_log('Drinks Plugin: Added image to lightbox collection: ' . $src);
        
        // Add Lightbox classes and attributes
        $figure->setAttribute('class', $figure->getAttribute('class') . ' wp-lightbox-container');
        $figure->setAttribute('data-wp-interactive', 'drinks-plugin');
        $figure->setAttribute('data-wp-context', '{"imageId": "' . $unique_id . '"}');
        
        // Add click handler to image
        $img->setAttribute('data-wp-on--click', 'actions.showLightbox');
        
        return $dom->saveHTML();
    }
    
    /**
     * Display "See Drinks" message in red
     */
    public function display_drinks_message() {
        echo '<div style="position: fixed; top: 20px; right: 20px; background: #ff0000; color: white; padding: 10px 15px; border-radius: 5px; z-index: 9999; font-weight: bold;">See Drinks</div>';
    }
    
    /**
     * Display drinks gallery with Lightbox functionality
     */
    public function display_drinks_lightbox() {
        $drinks = $this->get_sample_drinks();
        ?>
        <div class="drinks-gallery-container" style="margin: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
            <h2 style="color: #333; margin-bottom: 20px;">Drinks Gallery with Lightbox</h2>
            <div class="drinks-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <?php foreach ($drinks as $index => $drink): ?>
                    <div class="drink-item" 
                         data-wp-interactive="drinks-plugin"
                         data-wp-context='{"drinkIndex": <?php echo $index; ?>}'
                         style="text-align: center; cursor: pointer;">
                        <img src="<?php echo esc_url($drink['image']); ?>" 
                             alt="<?php echo esc_attr($drink['name']); ?>"
                             style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;"
                             data-wp-on--click="actions.openLightbox"
                             data-wp-bind--data-drink-index="state.drinkIndex">
                        <h3 style="margin: 10px 0; color: #333;"><?php echo esc_html($drink['name']); ?></h3>
                        <p style="color: #666; font-size: 14px;"><?php echo esc_html($drink['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add Lightbox styles
     */
    public function add_lightbox_styles() {
        ?>
        <style>
            .drinks-lightbox-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                display: none;
                z-index: 10000;
                align-items: center;
                justify-content: center;
            }
            
            .drinks-lightbox-overlay.active {
                display: flex;
            }
            
            .drinks-lightbox-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
                text-align: center;
            }
            
            .drinks-lightbox-image {
                max-width: 100%;
                max-height: 80vh;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }
            
            .drinks-lightbox-caption {
                color: white;
                margin-top: 15px;
                font-size: 18px;
                font-weight: bold;
            }
            
            .drinks-lightbox-description {
                color: #ccc;
                margin-top: 10px;
                font-size: 14px;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .drinks-lightbox-close {
                position: absolute;
                top: -40px;
                right: 0;
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 24px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .drinks-lightbox-close:hover {
                background: rgba(255, 255, 255, 0.3);
            }
            
            .drink-item:hover {
                transform: translateY(-2px);
                transition: transform 0.2s ease;
            }
            
            /* WordPress Core Lightbox styles for compatibility */
            .wp-lightbox-container {
                position: relative;
                cursor: pointer;
            }
            
            .wp-lightbox-container:hover {
                opacity: 0.9;
                transition: opacity 0.2s ease;
            }
            
            .wp-lightbox-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                display: none;
                z-index: 10000;
                align-items: center;
                justify-content: center;
            }
            
            .wp-lightbox-overlay.active {
                display: flex;
            }
            
            .wp-lightbox-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
                text-align: center;
            }
            
            .wp-lightbox-image {
                max-width: 100%;
                max-height: 80vh;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }
            
            .wp-lightbox-close {
                position: absolute;
                top: -40px;
                right: 0;
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                font-size: 24px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .wp-lightbox-close:hover {
                background: rgba(255, 255, 255, 0.3);
            }
        </style>
        <?php
    }
    
    /**
     * Get sample drinks data
     */
    private function get_sample_drinks() {
        return array(
            array(
                'name' => 'Mojito',
                'description' => 'A refreshing Cuban cocktail with rum, mint, lime, and sugar',
                'image' => 'https://images.unsplash.com/photo-1575023782549-62ca0d244b39?w=400&h=300&fit=crop'
            ),
            array(
                'name' => 'Margarita',
                'description' => 'Classic Mexican cocktail with tequila, lime juice, and orange liqueur',
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=300&fit=crop'
            ),
            array(
                'name' => 'Martini',
                'description' => 'Elegant cocktail with gin and vermouth, garnished with olive or lemon twist',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop'
            ),
            array(
                'name' => 'Old Fashioned',
                'description' => 'Traditional whiskey cocktail with bitters, sugar, and orange peel',
                'image' => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=800&h=600&fit=crop'
            ),
            array(
                'name' => 'Negroni',
                'description' => 'Italian aperitif with gin, vermouth rosso, and Campari',
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=300&fit=crop'
            ),
            array(
                'name' => 'Gin & Tonic',
                'description' => 'Simple and refreshing cocktail with gin and tonic water',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop'
            )
        );
    }
}

// Initialize the plugin
new DrinksPlugin();

// Add Interactivity API script for Lightbox functionality
add_action('wp_footer', function() {
    ?>
    <script type="module">
        import { store, getContext } from '@wordpress/interactivity';
        
        // Initialize the store with default state
        const initialState = {
            isOpen: false,
            currentDrink: null,
            currentImage: null,
            metadata: {},
            drinks: [
                {
                    name: 'Mojito',
                    description: 'A refreshing Cuban cocktail with rum, mint, lime, and sugar',
                    image: 'https://images.unsplash.com/photo-1575023782549-62ca0d244b39?w=800&h=600&fit=crop'
                },
                {
                    name: 'Margarita',
                    description: 'Classic Mexican cocktail with tequila, lime juice, and orange liqueur',
                    image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&h=600&fit=crop'
                },
                {
                    name: 'Martini',
                    description: 'Elegant cocktail with gin and vermouth, garnished with olive or lemon twist',
                    image: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop'
                },
                {
                    name: 'Old Fashioned',
                    description: 'Traditional whiskey cocktail with bitters, sugar, and orange peel',
                    image: 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=800&h=600&fit=crop'
                },
                {
                    name: 'Negroni',
                    description: 'Italian aperitif with gin, vermouth rosso, and Campari',
                    image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&h=600&fit=crop'
                },
                {
                    name: 'Gin & Tonic',
                    description: 'Simple and refreshing cocktail with gin and tonic water',
                    image: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop'
                }
            ]
        };
        
        store('drinks-plugin', {
            state: initialState,
            actions: {
                openLightbox(event) {
                    const { state } = getContext();
                    const drinkIndex = parseInt(event.target.dataset.drinkIndex);
                    state.currentDrink = state.drinks[drinkIndex];
                    state.isOpen = true;
                    document.body.style.overflow = 'hidden';
                },
                closeLightbox() {
                    const { state } = getContext();
                    state.isOpen = false;
                    state.currentDrink = null;
                    state.currentImage = null;
                    document.body.style.overflow = '';
                },
                showLightbox() {
                    const { state } = getContext();
                    const { imageId } = getContext();
                    
                    console.log('ShowLightbox called with imageId:', imageId);
                    console.log('State metadata:', state.metadata);
                    
                    // Ensure metadata exists and has the imageId
                    if (state.metadata && state.metadata[imageId]) {
                        const metadata = state.metadata[imageId];
                        console.log('Found metadata for imageId:', metadata);
                        state.currentImage = {
                            src: metadata.uploadedSrc,
                            alt: metadata.alt,
                            caption: metadata.caption
                        };
                        state.isOpen = true;
                        document.body.style.overflow = 'hidden';
                    } else {
                        console.warn('Lightbox metadata not found for imageId:', imageId);
                        console.warn('Available metadata keys:', state.metadata ? Object.keys(state.metadata) : 'none');
                    }
                }
            },
            callbacks: {
                handleEscape(event) {
                    const { state, actions } = getContext();
                    if (event.key === 'Escape' && state.isOpen) {
                        actions.closeLightbox();
                    }
                }
            }
        });
        
        // Add event listener for escape key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const { state, actions } = getContext('drinks-plugin');
                if (state.isOpen) {
                    actions.closeLightbox();
                }
            }
        });
    </script>
    
    <!-- Lightbox Overlay -->
    <div class="drinks-lightbox-overlay" 
         data-wp-interactive="drinks-plugin"
         data-wp-class--active="state.isOpen"
         data-wp-on--click="actions.closeLightbox">
        <div class="drinks-lightbox-content" data-wp-on--click="event.stopPropagation()">
            <button class="drinks-lightbox-close" data-wp-on--click="actions.closeLightbox">&times;</button>
            <img class="drinks-lightbox-image" 
                 data-wp-bind--src="state.currentDrink.image || state.currentImage.src" 
                 data-wp-bind--alt="state.currentDrink.name || state.currentImage.alt">
            <div class="drinks-lightbox-caption" data-wp-text="state.currentDrink.name || state.currentImage.caption"></div>
            <div class="drinks-lightbox-description" data-wp-text="state.currentDrink.description"></div>
        </div>
    </div>
    <?php
}); 