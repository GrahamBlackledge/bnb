 <?php
    include "checksession.php";
    checkUser();          
    loginStatus();        
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
</head>

<body>
   

    <?php
   
    // include database configuration
    include "config.php";

    //open the database connection
    $DBC = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBDATABASE);

    //check if the connection was good
    if (mysqli_connect_errno()) {
        echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
        exit; 
    }

    //prepare a query and send it to the server
    //join booking with customer and room so can show  info
    $query = 'SELECT b.bookingID, b.checkinDate, b.checkoutDate,
                 c.firstname, c.lastname,
                 r.roomname
          FROM booking b
          JOIN customer c ON b.customerID = c.customerID
          JOIN room r ON b.roomID = r.roomID
          ORDER BY b.bookingID';

    $result = mysqli_query($DBC, $query);
    $rowcount = mysqli_num_rows($result);
    ?>

    <h1>Bookings Listing</h1>
    <h2>
        <a href="make_booking.php">[Add a booking]</a>
        <a href="index.php">[Return to main page]</a>
    </h2>

    <?php
    if ($rowcount > 0) {
        echo '<table border="1">';
        echo '<thead><tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Action</th>
              </tr></thead>';

        //loop through each booking and display
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['bookingID'];

            echo '<tr>';
            echo '<td>' . $row['bookingID'] . '</td>';
            echo '<td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>';
            echo '<td>' . $row['roomname'] . '</td>';
            echo '<td>' . $row['checkinDate'] . '</td>';
            echo '<td>' . $row['checkoutDate'] . '</td>';

            // Action links 
            echo '<td>';
            echo '<a href="viewbooking.php?id=' . $id . '">[view]</a> ';
            echo '<a href="editbooking.php?id=' . $id . '">[edit]</a> ';
            echo '<a href="editreview.php?id=' . $id . '">[review]</a> ';
            echo '<a href="deletebooking.php?id=' . $id . '">[delete]</a>';
            echo '</td>';

            echo '</tr>' . PHP_EOL;
        }

        echo '</table>';

    } else {
        echo "<h2>No bookings found!</h2>";
    }

    //free any memory used by the query
    mysqli_free_result($result);
    //close the connection
    mysqli_close($DBC);
    ?>

</body>

</html>
