// Wait until the DOM is fully loaded before executing the JavaScript code
document.addEventListener("DOMContentLoaded", function() {

    // Get references to the mobile menu button and mobile navigation links
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileNavLinks = document.getElementById('mobileNavLinks');

    // Check if both elements exist on the page before adding event listeners
    if (mobileMenuButton && mobileNavLinks) {

        // Add an event listener to the mobile menu button for the 'click' event
        mobileMenuButton.addEventListener('click', () => {
            // Log to the console whenever the mobile menu button is clicked
            console.log('Mobile menu button clicked');
            
            // Toggle the 'hidden' class on the mobile navigation links
            // This will either show or hide the navigation links depending on their current state
            mobileNavLinks.classList.toggle('hidden');
        });
    } else {
        // If either the mobile menu button or the navigation links are not found, log an error
        console.log('Mobile menu button or nav links not found!');
    }
});
