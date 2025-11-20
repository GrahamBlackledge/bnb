<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
<?php
    // include session helpers
    include "checksession.php";

    // handle logout button
    if (isset($_POST['logout'])) {
        logout();
    }

    // handle login form submission
    if (isset($_POST['login']) && !empty($_POST['login']) && $_POST['login'] == 'Login') {

        include "config.php"; // load DB settings
        $DBC = mysqli_connect(DBHOST, DBUSER, DBPASSWORD, DBDATABASE);

        if (mysqli_connect_errno()) {
            echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
            exit;
        }

        $error = 0;
        $msg = 'Error: ';

        // validate username (we are using email as username)
        if (isset($_POST['username']) && !empty($_POST['username']) && is_string($_POST['username'])) {
            $un = htmlspecialchars(stripslashes(trim($_POST['username'])));
            $username = (strlen($un) > 32) ? substr($un, 0, 32) : $un;
        } else {
            $error++;
            $msg .= 'Invalid username ';
            $username = '';
        }

        // password (trim only, no extra cleaning)
        $password = trim($_POST['password'] ?? '');

        if ($password === '') {
            $error++;
            $msg .= 'Invalid password ';
        }

        // only query DB if no validation errors
        if ($error == 0) {

            // NOTE: for demo only - plain text password as per teacher example
            // normally you would use password_hash & password_verify
            $query = "SELECT customerID, password 
                      FROM customer 
                      WHERE email = '$username' AND password = '$password'";

            $result = mysqli_query($DBC, $query);

            if ($result && mysqli_num_rows($result) == 1) {
                // found the user
                $row = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                mysqli_close($DBC);

                // simple check – in real life you'd hash the password
                if ($password === $row['password']) {
                    // successful login
                    login($row['customerID'], $username);
                } else {
                    echo "<h6>Login fail</h6>" . PHP_EOL;
                }

            } else {
                echo "<h6>Login fail</h6>" . PHP_EOL;
            }

        } else {
            // show validation errors
            echo "<h6>$msg</h6>" . PHP_EOL;
        }
    }
?>

    <h1>Login</h1>
    <h2>
        
        <a href="registercustomer.php">[Create new customer]</a>
        <a href="index.php">[Return to main page]</a>
    </h2>

    <?php
    
    loginStatus();
    ?>

    <form method="POST">
        <p>
            <label for="username">Username (email):</label>
            <input type="text" id="username" name="username" maxlength="32" autocomplete="off">
        </p>

        <p>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" maxlength="32" autocomplete="off">
        </p>

        <input type="submit" name="login" value="Login">
        <input type="submit" name="logout" value="Logout">
    </form>

</body>
</html>
