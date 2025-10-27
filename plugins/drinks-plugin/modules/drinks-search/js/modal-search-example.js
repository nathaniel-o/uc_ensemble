/**
 * Drinks Search Module - Modal Search Example
 * 
 * This is a REFERENCE IMPLEMENTATION showing how to use the modal_search AJAX endpoint.
 * Currently NOT USED - the theme search opens the drinks carousel instead.
 * 
 * Keep this file for future reference if you want to implement a traditional
 * search modal with AJAX results.
 * 
 * @package DrinksPlugin
 * @subpackage DrinksSearch
 */

/**
 * Example: Search posts via AJAX and display results in a modal
 * 
 * Usage:
 *   const results = await searchPosts('cocktail recipes');
 *   displaySearchResults(results);
 */

/**
 * Search for posts using the drinks-search module AJAX endpoint
 * 
 * @param {string} searchTerm - The search query
 * @returns {Promise<Object>} Search results with posts array
 */
async function searchPosts(searchTerm) {
    if (!searchTerm || !searchTerm.trim()) {
        return { success: false, data: { posts: [], found: 0 } };
    }
    
    try {
        // Use WordPress ajaxurl (defined by wp_localize_script)
        const url = ajaxurl + '?action=modal_search&s=' + encodeURIComponent(searchTerm);
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
        
    } catch (error) {
        console.error('Search error:', error);
        return { 
            success: false, 
            data: { message: error.message }
        };
    }
}

/**
 * Example: Display search results in a modal
 * 
 * @param {Object} results - Results from searchPosts()
 */
function displaySearchResults(results) {
    if (!results.success) {
        console.error('Search failed:', results.data.message);
        return;
    }
    
    const { posts, found, query } = results.data;
    
    console.log(`Found ${found} results for "${query}"`);
    
    // Example: Build HTML for results
    const html = posts.map(post => `
        <div class="search-result">
            <h3><a href="${post.url}">${post.title}</a></h3>
            <p>${post.excerpt}</p>
            <span class="date">${post.date}</span>
        </div>
    `).join('');
    
    // Example: Insert into modal
    // const modal = document.querySelector('#search-modal .results');
    // if (modal) {
    //     modal.innerHTML = html;
    // }
    
    return html;
}

/**
 * Example: Debounced search as user types
 * 
 * @param {HTMLInputElement} searchInput - The search input element
 * @param {Function} callback - Function to call with results
 * @param {number} delay - Debounce delay in ms (default: 300)
 */
function setupLiveSearch(searchInput, callback, delay = 300) {
    let timeoutId;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(timeoutId);
        
        const searchTerm = e.target.value.trim();
        
        if (searchTerm.length < 2) {
            // Clear results if search is too short
            callback({ success: true, data: { posts: [], found: 0 } });
            return;
        }
        
        timeoutId = setTimeout(async () => {
            const results = await searchPosts(searchTerm);
            callback(results);
        }, delay);
    });
}

/**
 * Example: Complete modal search implementation
 * 
 * HTML structure needed:
 * <div id="search-modal" class="modal">
 *   <input type="text" id="search-input" placeholder="Search...">
 *   <div class="search-results"></div>
 * </div>
 */
function initSearchModal() {
    const searchInput = document.querySelector('#search-input');
    const resultsContainer = document.querySelector('#search-modal .search-results');
    
    if (!searchInput || !resultsContainer) {
        console.warn('Search modal elements not found');
        return;
    }
    
    // Setup live search with debouncing
    setupLiveSearch(searchInput, (results) => {
        if (results.success && results.data.posts) {
            resultsContainer.innerHTML = displaySearchResults(results);
        } else {
            resultsContainer.innerHTML = '<p>No results found</p>';
        }
    });
}

// Uncomment to auto-initialize when DOM is ready
// document.addEventListener('DOMContentLoaded', initSearchModal);

/* ===========================
   EXPORT FOR MODULE USE
   =========================== */

// If using ES6 modules:
// export { searchPosts, displaySearchResults, setupLiveSearch, initSearchModal };

// If using window globals (for WordPress):
window.DrinksSearch = window.DrinksSearch || {};
window.DrinksSearch.searchPosts = searchPosts;
window.DrinksSearch.displaySearchResults = displaySearchResults;
window.DrinksSearch.setupLiveSearch = setupLiveSearch;
window.DrinksSearch.initSearchModal = initSearchModal;

