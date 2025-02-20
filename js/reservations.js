// The Reservation class is used to handle the reservation date range.
class Reservation {
    // The constructor takes a start date and an end date.
    constructor(beginDate, endDate) {
        // Ensure the end date is not before the start date
        if (new Date(endDate) < new Date(beginDate)) {
            throw new Error("End date cannot be before the begin date.");
        }

        // Assign the dates to instance variables as Date objects
        this.beginDate = new Date(beginDate);
        this.endDate = new Date(endDate);
    }

    // Method to get all the dates between the start and end dates (inclusive)
    getBetweenDays() {
        var currentDate = new Date(this.beginDate);
        const dates = [];

        // Loop through the days from beginDate to endDate
        while (currentDate <= this.endDate) {
            // Push a new Date object of current date to the array
            dates.push(new Date(currentDate));
            // Increment the currentDate by 1 day
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // Return an array of all the dates between beginDate and endDate
        return dates;
    }
}

// Function to generate all the reserved dates from a list of reservations
function generateReservedDates(reservations) {
    const dates = [];
    
    // For each reservation, get all the dates it covers
    reservations.forEach(reservation => {
        const reservationDates = reservation.getBetweenDays();
        // Add the dates of each reservation to the main list
        dates.push(...reservationDates);
    });
    
    // Return an array containing all the reserved dates
    return dates;
}

// Helper function to check if a specific date is in an array of dates
function isDateInArray(date, dateArray) {
    // Check if any date in the array matches the given date
    return dateArray.some(d => d.getTime() === date.getTime());
}

// Async function to handle the reservation process
async function reserve(startDate, endDate, apartment_id, user_id) {
    // Create a new Reservation object with the given start and end dates
    const reservation = new Reservation(startDate, endDate);
    
    // Get all the dates that the reservation covers
    const reservationDates = reservation.getBetweenDays();

    // Fetch already reserved dates for the given apartment
    const reservedDates = await fetchReservedDates(apartment_id);

    // Check if any of the reservation dates overlap with already reserved dates
    for (let date of reservationDates) {
        if (isDateInArray(date, reservedDates)) {
            // If overlap found, throw an error and inform the user
            throw new Error("Your reservation overlaps reserved dates. Please choose other dates.");
        }
    }

    // Send the reservation request to the server
    const response = await fetch('createReservation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ apartment_id, user_id, begin_date: startDate, end_date: endDate })
    });

    // Wait for the server's response and parse the JSON
    const result = await response.json();

    // If the reservation creation was successful, notify the user
    if (result.status === "success") {
        alert("Reservation successfully submitted!");
    } else {
        // If there was an error, throw an error
        throw new Error("Failed to create reservation.");
    }
}
