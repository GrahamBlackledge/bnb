<?php
    include "checksession.php";
    checkUser();          
    loginStatus();        
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit / Add Review</title>
</head>

<body>

<h1>Edit / Add Room Review</h1>

<h2>
    <a href="listbookings.php">[ Return to the bookings listing ]</a>
    <a href="index.php">[ Return to main page ]</a>
</h2>

<?php

// connect to database 
include "config.php";
$DBC = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBDATABASE);
if (mysqli_connect_errno()) {
    echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
    exit;
}

// gets the booking ID
$bookingID = $_GET['id'] ?? 0;

if (!is_numeric($bookingID)) {
    echo "<h2>Invalid booking ID</h2>";
    exit;
}


// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newReview = mysqli_real_escape_string($DBC, trim($_POST['review']));

    $query = "UPDATE booking SET roomReview='$newReview' WHERE bookingID=$bookingID";

    $result = mysqli_query($DBC, $query);

    if ($result) {
        echo "<h2>Review updated successfully!</h2>";
        echo "<p><a href='listbookings.php'>Return to bookings listing</a></p>";
        mysqli_close($DBC);
        exit;
    } else {
        echo "<h2>Error updating review.</h2>";
    }
}


// Retricve booking info
$query = "SELECT b.bookingID, b.roomReview, c.firstname, c.lastname
          FROM booking b
          JOIN customer c ON b.customerID = c.customerID
          WHERE b.bookingID = $bookingID";

$result = mysqli_query($DBC, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    echo "<h2>No booking found!</h2>";
    exit;
}

$customerName = $booking['firstname'] . " " . $booking['lastname'];
$review = $booking['roomReview'];

echo "<h2>Review made by $customerName</h2>";
?>

<form action="editreview.php?id=<?php echo $bookingID; ?>" method="POST">

    <label for="review">Room review:</label><br>
    <textarea id="review" name="review" rows="4" cols="50"><?php echo $review; ?></textarea>

    <br><br>

    <button type="submit">Update</button>

</form>

</body>
</html>
