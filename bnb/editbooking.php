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
    <title>Edit Booking</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script>
    $( function() {
        $("#checkin").datepicker({
            dateFormat: "yy-mm-dd"
        });
        $("#checkout").datepicker({
            dateFormat: "yy-mm-dd"
        });
    });
    </script>
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

function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// work out booking ID
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "<h1>Invalid booking ID</h1>";
        mysqli_close($DBC);
        exit;
    }
    $bookingID = intval($_GET['id']);
} else {
    if (!isset($_POST['bookingID']) || !is_numeric($_POST['bookingID'])) {
        echo "<h1>Invalid booking ID</h1>";
        mysqli_close($DBC);
        exit;
    }
    $bookingID = intval($_POST['bookingID']);
}

// if POST: update booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $roomID        = intval($_POST['room']);
    $checkinDate   = cleanInput($_POST['checkin']);
    $checkoutDate  = cleanInput($_POST['checkout']);
    $contactNumber = cleanInput($_POST['contact']);
    $bookingExtras = cleanInput($_POST['extras']);
    $roomReview    = cleanInput($_POST['review']);

    $errors = array();
    if ($roomID <= 0) $errors[] = "Room must be selected.";
    if ($checkinDate === "" || $checkoutDate === "") $errors[] = "Checkin and checkout dates are required.";

    if (count($errors) == 0) {

        $checkinDate   = mysqli_real_escape_string($DBC, $checkinDate);
        $checkoutDate  = mysqli_real_escape_string($DBC, $checkoutDate);
        $contactNumber = mysqli_real_escape_string($DBC, $contactNumber);
        $bookingExtras = mysqli_real_escape_string($DBC, $bookingExtras);
        $roomReview    = mysqli_real_escape_string($DBC, $roomReview);

        $query = "UPDATE booking SET
                    roomID = $roomID,
                    checkinDate = '$checkinDate',
                    checkoutDate = '$checkoutDate',
                    contactNumber = '$contactNumber',
                    bookingExtras = '$bookingExtras',
                    roomReview = '$roomReview'
                  WHERE bookingID = $bookingID";

        $result = mysqli_query($DBC, $query);

        if ($result) {
            echo "<h1>Booking updated</h1>";
            echo '<h2><a href="listbookings.php">[ Return to the bookings listing ]</a></h2>';
            mysqli_close($DBC);
            exit;
        } else {
            echo "<h2>Error updating booking: " . mysqli_error($DBC) . "</h2>";
        }
    } else {
        echo "<h2>There were problems with your form:</h2><ul>";
        foreach ($errors as $e) {
            echo "<li>$e</li>";
        }
        echo "</ul>";
    }
}



// get booking row (join with room & customer just for display)
$query = "SELECT b.*, r.roomname, c.firstname, c.lastname
          FROM booking b
          JOIN room r ON b.roomID = r.roomID
          JOIN customer c ON b.customerID = c.customerID
          WHERE b.bookingID = $bookingID";

$result = mysqli_query($DBC, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    echo "<h1>Booking not found</h1>";
    mysqli_free_result($result);
    mysqli_close($DBC);
    exit;
}
mysqli_free_result($result);

// get rooms for dropdown
$rooms = array();
$rq = "SELECT roomID, roomname, roomtype, beds FROM room ORDER BY roomID";
$rr = mysqli_query($DBC, $rq);
while ($row = mysqli_fetch_assoc($rr)) {
    $rooms[] = $row;
}
mysqli_free_result($rr);
?>

    <h1>Edit a Booking</h1>
    <h2>
        <a href="listbookings.php">[ Return to the bookings listing ]</a>
        <a href="index.php">[ Return to main page ]</a>
    </h2>
    <h2>Booking made for <?php echo htmlspecialchars($booking['firstname'] . " " . $booking['lastname']); ?></h2>

    <form method="post" action="editbooking.php">
        <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">

        <label for="room">Room (name, type, beds):</label>
        <select name="room" id="room" required>
            <option value="0">-- Select room --</option>
            <?php
            foreach ($rooms as $r) {
                $label = $r['roomname'];
                if (isset($r['roomtype']) && isset($r['beds'])) {
                    $label .= ", " . $r['roomtype'] . ", " . $r['beds'];
                }
                $selected = ($r['roomID'] == $booking['roomID']) ? 'selected' : '';
                echo '<option value="' . $r['roomID'] . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
            }
            ?>
        </select>
        <br><br><br>

        <label for="checkin">Checkin date:</label>
        <input type="text" id="checkin" name="checkin" value="<?php echo htmlspecialchars($booking['checkinDate']); ?>" required>
        <br><br><br>

        <label for="checkout">Checkout date:</label>
        <input type="text" id="checkout" name="checkout" value="<?php echo htmlspecialchars($booking['checkoutDate']); ?>" required>
        <br><br><br>

        <label for="contact">Contact number:</label>
        <input type="tel" id="contact" name="contact"
               value="<?php echo htmlspecialchars($booking['contactNumber']); ?>"
               placeholder="(###) ### ####" pattern="\(\d{3}\) \d{3} \d{4}" required>
        <br><br><br>

        <label for="extras">Booking extras:</label>
        <textarea id="extras" name="extras" rows="3" cols="40"><?php echo htmlspecialchars($booking['bookingExtras']); ?></textarea>
        <br><br><br>

        <label for="review">Room review:</label>
        <textarea id="review" name="review" rows="3" cols="40"><?php echo htmlspecialchars($booking['roomReview']); ?></textarea>
        <br><br>

        <button type="submit">Update</button>
        <a href="listbookings.php">[Cancel]</a>
    </form>

<?php
mysqli_close($DBC);
?>
</body>
</html>
