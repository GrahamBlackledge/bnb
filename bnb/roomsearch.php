<?php
// Include database configuration
include "config.php";

// Get search parameters
$fromDate = $_GET['fromDate'];
$toDate   = $_GET['toDate'];

// Create a new database connection
$DBC = new mysqli(DBHOST, DBUSER, DBPASSWORD, DBDATABASE);

// Check if the connection was successful
if ($DBC->connect_errno) {
    echo "Error: Unable to connect to MySQL. " . $DBC->connect_error;
    exit; // Stop processing the page further
}

/*
    SQL to find available rooms:
    A room is available if it is NOT part of a booking
    where checkinDate >= fromDate AND checkoutDate <= toDate.
*/

$query = "
SELECT roomID, roomname, roomtype, beds
FROM room
WHERE roomID NOT IN (
    SELECT roomID FROM booking
    WHERE checkinDate >= ? AND checkoutDate <= ?
)
ORDER BY roomID
";

// Prepare the statement
$stmt = mysqli_prepare($DBC, $query);

// Bind parameters (same order as ? placeholders)
mysqli_stmt_bind_param($stmt, "ss", $fromDate, $toDate);

// Execute the query
mysqli_stmt_execute($stmt);

// Get the result set
$result = mysqli_stmt_get_result($stmt);

// Check if the query was successful
if ($result) {
    // Display search result in table rows
    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['roomID'] . "</td>";
            echo "<td>" . $row['roomname'] . "</td>";
            echo "<td>" . $row['roomtype'] . "</td>";
            echo "<td>" . $row['beds'] . "</td>";
            echo "</tr>";
        }

    } else {
        echo "<tr><td colspan='4'>No rooms available for the selected date range.</td></tr>";
    }

} else {
    // Handle query error
    echo "<tr><td colspan='4'>Error executing the query: " . $DBC->error . "</td></tr>";
}

// Close the statement
mysqli_stmt_close($stmt);

// Close database connection
mysqli_close($DBC);
?>
