<?php
/**
 * Test file for Drinks Content Lightbox
 * Include this in a WordPress page to test the functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if the plugin is active
if (!function_exists('uc_generate_drink_content_html')) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Error:</strong> Drinks Plugin is not active or not properly loaded.
    </div>';
    return;
}

// Test the drink content generation function
$test_post_id = 1; // Use post ID 1 for testing
$test_drink_content = uc_generate_drink_content_html($test_post_id, 'https://via.placeholder.com/300x200/FF6B6B/FFFFFF?text=Test+Drink', 'Test Drink');

?>

<div class="drinks-content-test" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h2>🍹 Drinks Content Lightbox Test</h2>
    
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>✅ Plugin Status:</strong> Drinks Plugin is active and working!
    </div>
    
    <h3>Test Images (Click to open lightbox)</h3>
    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
        <div data-carousel-enabled="true" style="cursor: pointer;">
            <img src="https://via.placeholder.com/300x200/FF6B6B/FFFFFF?text=Drink+1" 
                 alt="Test Drink 1" 
                 style="max-width: 300px; border-radius: 8px; transition: transform 0.2s ease;"
                 data-id="1">
            <p style="text-align: center; margin: 10px 0; color: #666;">Click to open lightbox</p>
        </div>
        
        <div data-carousel-enabled="true" style="cursor: pointer;">
            <img src="https://via.placeholder.com/300x200/4ECDC4/FFFFFF?text=Drink+2" 
                 alt="Test Drink 2" 
                 style="max-width: 300px; border-radius: 8px; transition: transform 0.2s ease;"
                 data-id="2">
            <p style="text-align: center; margin: 10px 0; color: #666;">Click to open lightbox</p>
        </div>
        
        <div data-carousel-enabled="true" style="cursor: pointer;">
            <img src="https://via.placeholder.com/300x200/45B7D1/FFFFFF?text=Drink+3" 
                 alt="Test Drink 3" 
                 style="max-width: 300px; border-radius: 8px; transition: transform 0.2s ease;"
                 data-id="3">
            <p style="text-align: center; margin: 10px 0; color: #666;">Click to open lightbox</p>
        </div>
    </div>
    
    <h3>Test Functions</h3>
    <button onclick="testDrinksContent()" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 10px 5px;">
        Test Drinks Content System
    </button>
    <button onclick="testGlobalObject()" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 10px 5px;">
        Test Global Object
    </button>
    <button onclick="checkPluginStatus()" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 10px 5px;">
        Check Plugin Status
    </button>
    
    <div id="test-results" style="margin: 20px 0;">
        <div style="background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px;">
            Click a test button above to see results here...
        </div>
    </div>
    
    <h3>Generated Drink Content Preview</h3>
    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; margin: 15px 0;">
        <p><strong>Sample drink content HTML:</strong></p>
        <pre style="background: white; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;"><?php echo htmlspecialchars($test_drink_content); ?></pre>
    </div>
    
    <h3>What's New</h3>
    <ul>
        <li><strong>Drink Content Display:</strong> Lightbox now shows drink information instead of just images</li>
        <li><strong>Template Part Format:</strong> Content matches the "Drink Post Content" template part structure</li>
        <li><strong>Metadata List:</strong> Shows category, color, glass, garnish, base, and ice information</li>
        <li><strong>Navigation:</strong> Previous/Next buttons for browsing drinks</li>
        <li><strong>Responsive Design:</strong> Lightbox adapts to different screen sizes</li>
    </ul>
    
    <h3>Troubleshooting</h3>
    <p><strong>If testDrinksContent() isn't working:</strong></p>
    <ol>
        <li>Make sure you've run <code>npm run build</code> in the plugin directory</li>
        <li>Check that the plugin is activated in WordPress</li>
        <li>Verify the built files exist in <code>build/frontend.js</code></li>
        <li>Check browser console for JavaScript errors</li>
        <li>Ensure the plugin is properly enqueuing the built JavaScript</li>
    </ol>
</div>

<script>
// Test functions for the drinks content lightbox
function testDrinksContent() {
    console.log('Testing drinks content lightbox system...');
    
    if (window.testDrinksContent) {
        const result = window.testDrinksContent();
        showResult('Drinks content system test completed. Check console for details.', 'success');
        console.log('Test result:', result);
    } else {
        showResult('testDrinksContent function not found. Plugin may not be loaded.', 'error');
    }
}

function testGlobalObject() {
    console.log('Testing global object...');
    
    if (window.drinksPluginDrinksContent) {
        showResult('Global object is available and working!', 'success');
        console.log('Global object:', window.drinksPluginDrinksContent);
    } else {
        showResult('Global object not found. Plugin may not be loaded.', 'error');
    }
}

function checkPluginStatus() {
    const results = [];
    
    // Check if global object exists
    if (window.drinksPluginDrinksContent) {
        results.push('✅ Global object available');
    } else {
        results.push('❌ Global object not found');
    }
    
    // Check if test function exists
    if (window.testDrinksContent) {
        results.push('✅ Test function available');
    } else {
        results.push('❌ Test function not found');
    }
    
    // Check if click handlers are working
    const containers = document.querySelectorAll('[data-carousel-enabled]');
    if (containers.length > 0) {
        results.push(`✅ Found ${containers.length} carousel-enabled containers`);
    } else {
        results.push('❌ No carousel-enabled containers found');
    }
    
    // Check if images have proper attributes
    const images = document.querySelectorAll('[data-carousel-enabled] img');
    let validImages = 0;
    images.forEach(img => {
        if (img.hasAttribute('data-id')) {
            validImages++;
        }
    });
    
    if (validImages > 0) {
        results.push(`✅ Found ${validImages} properly configured images`);
    } else {
        results.push('❌ No properly configured images found');
    }
    
    showResult(results.join('<br>'), validImages > 0 ? 'success' : 'error');
}

function showResult(message, type) {
    const resultsDiv = document.getElementById('test-results');
    const statusClass = type === 'success' ? 'd4edda' : type === 'error' ? 'f8d7da' : 'd1ecf1';
    const textColor = type === 'success' ? '155724' : type === 'error' ? '721c24' : '0c5460';
    
    resultsDiv.innerHTML = `<div style="background: #${statusClass}; color: #${textColor}; padding: 15px; border-radius: 5px;">${message}</div>`;
}

// Add click handlers to test images
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('[data-carousel-enabled]');
    containers.forEach(container => {
        container.addEventListener('click', function() {
            const img = this.querySelector('img');
            if (img && window.drinksPluginDrinksContent && window.drinksPluginDrinksContent.open) {
                console.log('Opening lightbox for image:', img.alt);
                window.drinksPluginDrinksContent.open(img, this);
                showResult('Lightbox opened for image: ' + img.alt, 'success');
            } else {
                showResult('Drinks content lightbox not available. Check console for details.', 'error');
            }
        });
    });
    
    // Check initial status
    checkPluginStatus();
});
</script>
