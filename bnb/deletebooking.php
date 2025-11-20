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
    <title>Booking preview before deletion</title>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";
$DBC = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBDATABASE);

if (mysqli_connect_errno()) {
    echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
    exit;
}

// simple cleaner
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// If form submitted (Delete button pressed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bookingID']) && is_numeric($_POST['bookingID'])) {
        $bookingID = intval($_POST['bookingID']);

        // delete the booking
        $query = "DELETE FROM booking WHERE bookingID = $bookingID";
        $result = mysqli_query($DBC, $query);

        if ($result) {
            echo "<h1>Booking deleted</h1>";
            echo '<h2><a href="listbookings.php">[ Return to the bookings listing ]</a></h2>';
        } else {
            echo "<h1>Error deleting booking</h1>";
            echo "<p>" . mysqli_error($DBC) . "</p>";
            echo '<h2><a href="listbookings.php">[ Return to the bookings listing ]</a></h2>';
        }

        mysqli_close($DBC);
        exit;
    } else {
        echo "<h1>Invalid booking ID</h1>";
        echo '<h2><a href="listbookings.php">[ Return to the bookings listing ]</a></h2>';
        mysqli_close($DBC);
        exit;
    }
}

// Otherwise, GET request: show preview

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Invalid booking ID</h1>";
    echo '<h2><a href="listbookings.php">[ Return to the bookings listing ]</a></h2>';
    mysqli_close($DBC);
    exit;
}

$bookingID = intval($_GET['id']);

// get booking + room details
$query = "SELECT b.bookingID, b.checkinDate, b.checkoutDate,
                 r.roomname
          FROM booking b
          JOIN room r ON b.roomID = r.roomID
          WHERE b.bookingID = $bookingID";

$result = mysqli_query($DBC, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    echo "<h1>Booking not found</h1>";
    echo '<h2><a href="listbookings.php">[ Return to the bookings listing ]</a></h2>';
    mysqli_free_result($result);
    mysqli_close($DBC);
    exit;
}
?>

    <h1>Booking preview before deletion</h1>
    <h2>
        <a href="listbookings.php">[ Return to the bookings listing ]</a>
        <a href="index.php">[ Return to main page ]</a>
    </h2>

    <fieldset>
        <legend>Booking Detail #<?php echo $booking['bookingID']; ?></legend>

        <p>Room name:<br><?php echo htmlspecialchars($booking['roomname']); ?></p>
        <p>Checkin date:<br><?php echo htmlspecialchars($booking['checkinDate']); ?></p>
        <p>Checkout date:<br><?php echo htmlspecialchars($booking['checkoutDate']); ?></p>
    </fieldset>

    <h2>Are you sure you want to delete this booking?</h2>
    <br><br>

    <form method="post" action="deletebooking.php">
        <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
        <input type="submit" name="submit" value="Delete">
        <a href="listbookings.php">Cancel</a>
    </form>

<?php
mysqli_free_result($result);
mysqli_close($DBC);
?>
</body>
</html>
