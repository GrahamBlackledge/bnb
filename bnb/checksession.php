<?php
// start or resume the session for tracking logged-in user
session_start();

// simple helper to make sure session variables exist
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = 0;
    $_SESSION['userid']   = -1;
    $_SESSION['username'] = '';
    $_SESSION['URI']      = '';
}

// check if the user is logged in, otherwise redirect to login page
function checkUser() {

    // clear stored URI by default
    $_SESSION['URI'] = '';

    if ($_SESSION['loggedin'] == 1) {
        return true;
    } else {
        // remember where the user was trying to go
        $_SESSION['URI'] = $_SERVER['REQUEST_URI'];
        // redirect to login page for the bnb site
        header('Location:/bnb/login.php', true, 303);
        exit();
    }
}

// show login status on the page if needed
function loginStatus() {

    $un = $_SESSION['username'];

    if ($_SESSION['loggedin'] == 1) {
        echo "<h6>logged in as $un</h6>";
    } else {
        echo "<h6>logged out</h6>";
    }
}

// log the user in and redirect them
function login($id, $username) {

    // if we stored a URI, we could use it; for now we always go to listbookings
    if (empty($_SESSION['URI'])) {
        $_SESSION['URI'] = '/bnb/listbookings.php';
    }

    $_SESSION['loggedin'] = 1;
    $_SESSION['userid']   = $id;
    $_SESSION['username'] = $username;

    $target = $_SESSION['URI'];
    $_SESSION['URI'] = '';

    header("Location:$target", true, 303);
    exit();
}

// log the user out and send them back to the login page
function logout() {

    $_SESSION['loggedin'] = 0;
    $_SESSION['userid']   = -1;
    $_SESSION['username'] = '';
    $_SESSION['URI']      = '';

    header('Location:/bnb/login.php', true, 303);
    exit();
}
