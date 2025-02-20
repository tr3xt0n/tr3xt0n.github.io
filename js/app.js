// Wait until the document content is fully loaded before executing the JavaScript.
document.addEventListener('DOMContentLoaded', function () {

    // Open login modal when login button is clicked (desktop)
    document.getElementById('loginButton').onclick = function() {
        // Remove 'hidden' class to make the login modal visible
        document.getElementById('loginModal').classList.remove('hidden');
    };

    // Open signup modal when signup button is clicked (desktop)
    document.getElementById('signupButton').onclick = function() {
        // Remove 'hidden' class to make the signup modal visible
        document.getElementById('signupModal').classList.remove('hidden');
    };

    // Open login modal when login button is clicked (mobile)
    document.getElementById('loginButtonMobile').onclick = function() {
        // Remove 'hidden' class to make the login modal visible
        document.getElementById('loginModal').classList.remove('hidden');
    };

    // Open signup modal when signup button is clicked (mobile)
    document.getElementById('signupButtonMobile').onclick = function() {
        // Remove 'hidden' class to make the signup modal visible
        document.getElementById('signupModal').classList.remove('hidden');
    };

    // Handle the login form submission asynchronously
    document.getElementById('loginForm').onsubmit = async function(e) {
        e.preventDefault();  // Prevent default form submission behavior

        // Get the values of the username and password fields
        const username = document.getElementById('loginUsername').value;
        const password = document.getElementById('loginPassword').value;

        // Send a POST request to 'login.php' with the credentials
        const response = await fetch('login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })  // Send the credentials as JSON
        });

        // Wait for the response and parse it as JSON
        const result = await response.json();

        console.log('Login Result:', result);  // Log the result to help debug

        // Check if the login was successful
        if (result.success) {
            alert('Login successful!');  // Show a success message
            // Hide the login modal after success
            document.getElementById('loginModal').classList.add('hidden');
            // Update the UI based on the user information (username, user_type)
            updateUI(result.username, result.user_type);  
        } else {
            alert('Login failed: ' + result.message);  // Show an error message if login failed
        }
    };

    // Handle the signup form submission asynchronously
    document.getElementById('signupForm').onsubmit = async function(e) {
        e.preventDefault();  // Prevent default form submission behavior

        // Get the values of the signup form fields
        const username = document.getElementById('signupUsername').value;
        const email = document.getElementById('signupEmail').value;
        const password = document.getElementById('signupPassword').value;

        // Send a POST request to 'signup.php' with the new user's details
        const response = await fetch('signup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })  // Send the data as JSON
        });

        // Wait for the response and parse it as JSON
        const result = await response.json();
        console.log('Signup Result:', result);  // Log the result to help debug

        // Check if the signup was successful
        if (result.success) {
            alert('Signup successful!');  // Show a success message
            // Hide the signup modal after success
            document.getElementById('signupModal').classList.add('hidden');
            // Update the UI based on the new user's information
            updateUI(result.username, result.user_type);  
        } else {
            alert('Signup failed: ' + result.message);  // Show an error message if signup failed
        }
    };
});

// Function to update the UI based on the user's login status
function updateUI(username, user_type) {
    console.log('Updating UI for:', username, user_type);  // Debugging output

    // Hide all buttons first (for both desktop and mobile)
    // Desktop buttons
    document.getElementById('loginButton').style.display = 'none';
    document.getElementById('signupButton').style.display = 'none';
    document.getElementById('logoutButton').style.display = 'none';
    document.getElementById('adminButton').style.display = 'none';
    document.getElementById('userButton').style.display = 'none';

    // Mobile buttons
    document.getElementById('loginButtonMobile').style.display = 'none';
    document.getElementById('signupButtonMobile').style.display = 'none';
    document.getElementById('logoutButtonMobile').style.display = 'none';
    document.getElementById('adminButtonMobile').style.display = 'none';
    document.getElementById('userButtonMobile').style.display = 'none';

    // If no user is logged in, show login/signup buttons (both on desktop and mobile)
    if (!username || !user_type) {
        document.getElementById('loginButton').style.display = 'block';
        document.getElementById('signupButton').style.display = 'block';
        document.getElementById('loginButtonMobile').style.display = 'block';
        document.getElementById('signupButtonMobile').style.display = 'block';
        return; // Exit the function if no user is logged in
    }

    // Show logout button if logged in (both on desktop and mobile)
    document.getElementById('logoutButton').style.display = 'block';
    document.getElementById('logoutButtonMobile').style.display = 'block';

    // Show the correct dashboard button based on user_type (both on desktop and mobile)
    if (user_type === 'regular') {
        // Show user dashboard for regular users
        document.getElementById('userButton').style.display = 'block';
        document.getElementById('userButtonMobile').style.display = 'block';
    } else if (user_type === 'admin') {
        // Show admin dashboard for admins
        document.getElementById('adminButton').style.display = 'block';
        document.getElementById('adminButtonMobile').style.display = 'block';
    }

    // Handle logout (for both desktop and mobile)
    document.getElementById('logoutButton').onclick = function() {
        // Send a request to 'logout.php' to log the user out
        fetch('logout.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    window.location.reload();  // Refresh the page to reset the UI after logout
                }
            });
    };

    // Same logout handler for mobile logout button
    document.getElementById('logoutButtonMobile').onclick = function() {
        fetch('logout.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    window.location.reload();  // Refresh the page to reset the UI after logout
                }
            });
    };
}

// On page load, check if a user is logged in (session management)
document.addEventListener("DOMContentLoaded", function() {
    // Ensure the user stays logged in on page load (without logout)
    fetch('check_session.php')
        .then(response => response.json())
        .then(result => {
            console.log('Session Check Result:', result);  // Debugging output
            if (result.success) {
                updateUI(result.username, result.user_type);  // Update UI based on logged-in user
            }
        });
});

// Handle the search form submission (for checking availability of apartments)
document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault();  // Prevent form submission (page reload)

    // Get the values of the checkin and checkout dates
    const checkin_date = document.getElementById('checkin_date').value;
    const checkout_date = document.getElementById('checkout_date').value;

    // Build the URL for fetching search results with query parameters
    const url = new URL('/casa-cavaleri/index.php', window.location.origin);
    const params = new URLSearchParams();
    if (checkin_date) params.append('checkin_date', checkin_date);
    if (checkout_date) params.append('checkout_date', checkout_date);
    url.search = params.toString();

    // Fetch the results from the server
    fetch(url)
        .then(response => response.text())  // Expecting HTML response from the server
        .then(data => {
            // Insert the results into the 'apartmentResults' element in the DOM
            document.getElementById('apartmentResults').innerHTML = data;
        })
        .catch(error => console.error('Error fetching apartments:', error));  // Handle errors if the fetch fails
});
