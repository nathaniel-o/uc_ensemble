/**
 * Home Button Script
 * Dynamically creates and prepends the home button to the navigation.
 */
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