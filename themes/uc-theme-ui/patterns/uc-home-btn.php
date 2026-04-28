<?php
/**
 * Title: Home Button
 * Slug: uc-theme-ui/uc-home-btn
 * Categories: featured, layout
 * Block Types: core/group, core/columns
 *
 */
?>

<!-- wp:site-logo /-->

<!-- wp:navigation-link {"label":"Home","type":"page","description":"","url":"/welcome/","title":"","kind":"post-type","className":"menu-item menu-item-type-post_type menu-item-object-page"} /-->

<?php
/*
CSS archived from style.css:

.uc-home-button {
    max-width: 250px; /* Approximate width of "Special Occasion Cocktails" */
    flex-shrink: 1;
    min-width: fit-content;
    overflow: visible;
    box-sizing: border-box;
}

.uc-home-button .wp-block-site-logo {
    max-width: 60px;
    flex-shrink: 0;
}

.uc-home-button .wp-block-site-logo img {
    max-width: 60px;
    max-height: 60px;
    width: auto;
    height: auto;
}

.uc-home-button a.wp-block-navigation-item__content {
    display: flex;
    align-items: center;
    gap: 0.5em;
    white-space: nowrap;
    overflow: visible;
    flex-shrink: 0;
}

.uc-home-button .wp-block-navigation-item__label {
    display: inline-block;
    overflow: visible;
}

#modal-1-content > ul > li:not(.uc-home-button) {
    max-width: calc((100% / 7) - 0.5rem);
    box-sizing: border-box;
}

/* Mobile view */
.uc-home-button {
    max-width: 100%;
    width: 100%;
}

.uc-home-button .wp-block-site-logo img {
    max-width: 50px;
    max-height: 50px;
    width: auto;
    height: auto;
}

*/

/*
JS archived from scripts/functions.js:

function ucHomeBtn() {
    const navContainer = document.querySelector('ul.wp-block-navigation__container.is-responsive.wp-block-navigation');
    if (navContainer) {
        const navItems = Array.from(navContainer.children);
        const ucHomeBtn = document.createElement("li");
        ucHomeBtn.classList.add("uc-home-button");
        ucHomeBtn.classList.add("wp-block-navigation-item");
        const theLogo = navItems[0].children[0];
        const theLink = navItems[1].children[0];
        navItems[0].remove();
        navItems[1].remove();
        ucHomeBtn.appendChild(theLogo);
        ucHomeBtn.appendChild(theLink);
        navContainer.prepend(ucHomeBtn);
    }
}

document.addEventListener("DOMContentLoaded", ucHomeBtn);
*/
