<?php
    include "checksession.php";
    checkUser();          
    loginStatus();        
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Make a Booking</title>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

  <style>
    .container{
      width: 600px;
      margin: 0 auto;
    }
  </style>

  <script>
   
    // Set default date format for all jQuery date pickers
  // Runs when the page first loads
    $(document).ready(function() {
      $.datepicker.setDefaults({ dateFormat: 'yy-mm-dd' });
        // Attach datepickers to form inputs
      $("#checkin").datepicker();
      $("#checkout").datepicker();
      $("#fromDate").datepicker();
      $("#toDate").datepicker();

        // Search availability and update the room dropdown
      $("#searchRoomsBtn").on("click", function(event) {
        event.preventDefault();

        var fromDate = $("#checkin").val();
        var toDate   = $("#checkout").val();

        if (!fromDate || !toDate) {
          alert("Please choose both checkin and checkout dates first.");
          return;
        }
        // Send AJAX request to update the ROOM drop downn based on checkin/checkout dates
        $.ajax({
          url: "roomsearch.php",
          method: "GET",
          data: { fromdate: fromDate, enddate: toDate },
          success: function(response) {
            $("#room").html(response);
          }
        });
      });
    });

      // Searchh room function
    function searchRooms() {
      var fromDate = $("#fromDate").val();
      var toDate   = $("#toDate").val();

      if (!fromDate || !toDate) {
        alert("Please enter both dates.");
        return;
      }
      // Basic validation: from must be earlier than to
      if (fromDate > toDate) {
        alert("From date cannot be later than To date.");
        return;
      }
      // AJAX request to fetch available rooms and show them in a table
      $.ajax({
        url: "roomsearch.php",
        method: "GET",
        data: { fromdate: fromDate, enddate: toDate },
        success: function(response) {
          if ($.trim(response) === "") {
            $("#result").hide().html("");
          } else {
            $("#result").show().html(response);
          }
        }
      });
    }
  </script>
</head>
<body>
  
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database using config.php settings
include "config.php";
$DBC = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBDATABASE);

if (mysqli_connect_errno()) {
  echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
  exit;
}
// Clean input before saving to database
function cleanInput($data) {
  return htmlspecialchars(stripslashes(trim($data)));
}

$errors = 0;
$msg = "";

// Check if form was submitted
if (isset($_POST['submit']) && $_POST['submit'] == 'Add') {

  $customerID = 1;


  // Read all form data and clean where needed
  $roomID        = intval($_POST['room']);
  $checkinDate   = $_POST['checkin'];
  $checkoutDate  = $_POST['checkout'];
  $contactNumber = cleanInput($_POST['contact']);
  $bookingExtras = cleanInput($_POST['extras']);

  // Validate that the user selected a room
  if ($roomID <= 0) {
    $errors++;
    $msg .= "You must select a room.<br>";
  }
  // Check date fields
  if ($checkinDate == "" || $checkoutDate == "") {
    $errors++;
    $msg .= "Dates cannot be empty.<br>";
  } else {
    $in  = new DateTime($checkinDate);
    $out = new DateTime($checkoutDate);

    if ($in >= $out) {
      $errors++;
      $msg .= "Checkout must be later than checkin.<br>";
    }
  }

  if ($errors == 0) {
    // Insert booking iinto databasA
    $query = "INSERT INTO booking (roomID, customerID, checkinDate, checkoutDate, contactNumber, bookingExtras)
              VALUES (?,?,?,?,?,?)";

    $stmt = mysqli_prepare($DBC, $query);
    mysqli_stmt_bind_param($stmt, 'iissss',
      $roomID, $customerID, $checkinDate, $checkoutDate, $contactNumber, $bookingExtras
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<h3>Booking added successfully.</h3>";
    echo "<a href='listbookings.php'>[Return to Bookings]</a>";

  } else {
    // Show any validation error messages
    echo "<h3>Error:</h3><p>$msg</p>";
  }
}
// Query rooms to fill the dropdown on page load
$queryRooms = "SELECT roomID, roomname, roomtype, beds FROM room ORDER BY roomID";
$resultRooms = mysqli_query($DBC, $queryRooms);
?>

<h1>Make a Booking</h1>
<h2>
  <a href="listbookings.php">[Return to Bookings]</a>
  <a href="index.php">[Return to Home]</a>
</h2>
<h2>Booking for Test</h2>


<form method="POST" action="make_booking.php">
  <div>
    <label for="room">Room (name, type, beds):</label>
    <select name="room" id="room" required>
      <option value="">-- Select Room --</option>
      <?php
      while ($row = mysqli_fetch_assoc($resultRooms)) {
        $text = $row['roomname'] . ", " . $row['roomtype'] . ", " . $row['beds'];
        echo "<option value='{$row['roomID']}'>".htmlspecialchars($text)."</option>";
      }
      ?>
    </select>
  </div>

  <br>

  <div>
    <label for="checkin">Checkin Date:</label>
    <input type="text" id="checkin" name="checkin" required>
    <input type="button" id="searchRoomsBtn" value="Search availability">
  </div>

  <br>

  <div>
    <label for="checkout">Checkout date:</label>
    <input type="text" id="checkout" name="checkout" required>
  </div>

  <br>

  <div>
    <label for="contact">Contact number:</label>
    <input type="tel" id="contact" name="contact"
      placeholder="(###) ### ####"
      pattern="\(\d{3}\) \d{3} \d{4}" required>
  </div>

  <br>

  <div>
    <label for="extras">Booking extras:</label>
    <textarea id="extras" name="extras" rows="4" cols="40"></textarea>
  </div>

  <br>

  <div>
    <input type="submit" name="submit" value="Add">
    <a href="listbookings.php">[Cancel]</a>
  </div>
</form>

<hr>


<div class="container">
    <h1>Search Rooms</h1>

    <form>
        <p>
            <label for="fromDate">From Date:</label>
            <input type="text" id="fromDate" placeholder="yyyy-mm-dd">

            &nbsp;&nbsp;

            <label for="toDate">To Date:</label>
            <input type="text" id="toDate" placeholder="yyyy-mm-dd">
        </p>

        <p>
            <input type="button" value="Search" onclick="searchRooms()">
        </p>
    </form>

    <table id="result" border="1" style="display:none;"></table>
</div>

<?php mysqli_close($DBC); ?>

</body>
</html>
