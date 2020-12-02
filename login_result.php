<?php
// Include PHP files referenced here
include_once("header.php");
include 'database.php';
require("utilities.php");

// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// Check user inputs and throw custom error messages
function isDataValid()
{
    $errorMessage = null;
    if (!isset($_POST['email']) or trim($_POST['email']) == '') {
        $errorMessage .= 'You must enter your <b>Email</b> <br/>';
    }
    if (!isset($_POST['password']) or trim($_POST['password']) == '') {
        $errorMessage .= 'You must enter your <b>Password</b> <br/>';
    }
    if ($errorMessage !== null) {
        echo <<<EOM
        <div class="container">
          <h1>Sorry, the information is incorrect. Pleace check again.</h1>
          <p> Error: <br/> $errorMessage </p>
  EOM;
        return false;
    } else {
        return true;
    }
}

if (isDataValid()) {
    // Get all item attributes from the user's input.
    $account_type = $_POST['accountType'];
    $user_email = $_POST['email'];
    $password = $_POST['password'];
    // Check user account validation
    $user = isLoginUserValid($account_type, $user_email, $password, $conn);
    // If the user account is validated then redirect to browse page. Otherwise, display login invalidation.
    if ($user['name'] != "") {
        echo "<div class='container'><h1>You are now logged in! You will be redirected shortly.</h1></div>";
        // Redirect to browse page after 3 seconds
        header("refresh:3;url=browse.php");
    } else {
        echo "<div class='container'><h1>Incorrect email or password, please try again!</h1>";
        echo "<button type='submit' class='btn btn-primary' value='Back' onClick='history.back()');>Back to previous page</button></div>";
    }
} else {
    echo "<button type='submit' class='btn btn-primary' value='Back' onClick='history.back()');>Back to previous page</button></div>";
}
