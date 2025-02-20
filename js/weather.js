document.addEventListener('DOMContentLoaded', function () {
    // Define the API key for OpenWeatherMap
    const apiKey = '6dfe424fe57de555514f01bcf5ae7c22';  // Your API key from OpenWeatherMap

    // Define the city for which we want to fetch weather information
    const city = 'Albenga,it';  // This is the city we are getting the weather for

    const lang = 'it';

    // Construct the API URL with the necessary query parameters
    // The 'q' parameter specifies the city, 'units=metric' sets the temperature to be in Celsius, and 'appid' is your API key
    const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&appid=${apiKey}&lang=${lang}`;

    // Use fetch to make a GET request to the weather API
    fetch(apiUrl)
        .then(response => response.json())  // Parse the response as JSON
        .then(data => {
            // Once the response is successfully received and parsed, we extract the weather data

            // Extract the temperature from the 'main' object in the API response
            const temperature = data.main.temp;
            console.log(temperature);
            // Extract the weather description (e.g., "clear sky") from the 'weather' array in the response
            const description = data.weather[0].description;

            // Construct the URL for the weather icon (based on the icon code from the response)
            const icon = `https://openweathermap.org/img/wn/${data.weather[0].icon}.png`;

            // Update the UI elements with the fetched weather data

            // Set the temperature (Celsius) in the DOM element with ID 'temperature'
            document.getElementById('temperature').textContent = `${temperature}°C`;

            // Set the weather description in the DOM element with ID 'weatherDescription'
            // Capitalize the first letter of the description for readability
            document.getElementById('weatherDescription').textContent = description.charAt(0).toUpperCase() + description.slice(1);

            // Set the weather icon image in the DOM element with ID 'weatherIcon'
            // The 'src' attribute is set to the icon URL
            document.getElementById('weatherIcon').src = icon;
        })
        .catch(error => {
            // Handle any errors that occur during the fetch request (e.g., network issues)
            console.error('Error fetching weather data:', error);
        });
});
