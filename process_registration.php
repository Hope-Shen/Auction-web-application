<?php
//Include PHP files referenced here
include_once("header.php");
include_once("sendEmail.php");
require("utilities.php");
include 'database.php';

// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation
// options.

// check is the textboxs are empty or not
function isDataValid($conn)
{
    // List into an array all input features that are required and/or need to be validated in terms of input length
    $content_check = ['firstName' => ['postName' => 'firstName', 'UI name' => 'First Name', 'length' => 15],
                'lastName' => ['postName' =>'lastName', 'UI name' => 'Last Name', 'length' => 15],
                'email' => ['postName' =>'registerEmail', 'UI name' => 'Email', 'length' => 30],
                'password' => ['postName' =>'inputPassword', 'UI name' => 'Password', 'length' => 40],
                'repeatPassword' => ['postName' =>'passwordConfirmation', 'UI name' => 'Repeat password', 'length' => 40],
                'phoneNo' => ['postName' =>'phoneNo', 'UI name' => 'Phone Number', 'length' => 15],
                'street' => ['postName' =>'street', 'UI name' => 'Street', 'length' => 20],
                'city' => ['postName' =>'city', 'UI name' => 'City', 'length' => 20],
                'postcode' => ['postName' =>'postcode', 'UI name' => 'Postcode', 'length' => 10]];

    // Initialise $errorMessage variable
    $errorMessage = null;

    // Check each element of the $content_check array for errors
    foreach ($content_check as $x => $val) {
        // Check if all required inputs have been entered.
        if (!in_array($x, ['phoneNo', 'street', 'city', 'postcode'])) {
            if (!isset($_POST[$val['postName']]) or trim($_POST[$val['postName']]) == '') {
                $errorMessage .= 'You must enter <b>' . $val['UI name'] . '</b> <br/>';
            }
        }
        // email: validate email(format, existence)
        if ($x == 'email') {
            if (!filter_var($_POST['registerEmail'], FILTER_VALIDATE_EMAIL)) {
                $errorMessage .= '<b>' . $val['UI name']. ': </b>' . $val['postName'] . ' is not a valid email address <br/>';
            }
            // Check email existence
            $accounttype = trim($_POST['accountType']);
            $sql = "";
            $sql_2 = "";
            $cnt = 0;
            if ($accounttype != "both") {
                $sql = "SELECT email FROM $accounttype WHERE email = ? ;";
            } else {
                $sql = "SELECT email FROM buyer WHERE email = ? ;";
                $sql_2 ="SELECT email FROM seller WHERE email = ? ;";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_POST['registerEmail']);
            /* execute query */
            $result = $stmt->execute();
            /* store result */
            $stmt->store_result();
            $cnt +=  $stmt->num_rows;
            $stmt->close();

            if ($accounttype == "both") {
                $stmt_2 = $conn->prepare($sql_2);
                $stmt_2->bind_param("s", $_POST['registerEmail']);
                /* execute query */
                $result_2 = $stmt_2->execute();
                /* store result */
                $stmt_2->store_result();
                $cnt +=  $stmt_2->num_rows;
                $stmt_2->close();
            }

            if ($cnt > 0) {
                $errorMessage .= 'Your email address already exists. <br/>';
            }
        }

        // password and repeat password should be matched.
        if ($x == 'repeatPassword' and $_POST['inputPassword'] != $_POST['passwordConfirmation']) {
            $errorMessage .= 'Your <b>' . $val['UI name'] . '</b> doesn\'t match your password<br/>';
        }

        // valid length (not include: ['password', 'repeatPassword'] b/c password will save by HASH)
        if (!in_array($x, ['password', 'repeatPassword'])) {
            // Validate the input length for the user inputs. This is important, as the maximum number of characters must not exceed the length of the attribute defined in the database.
            if (strlen($_POST[$val['postName']]) > $val['length']) {
                $errorMessage .= '<b>' . $val['UI name'] . ': </b> cannot be greater than '. $val['length'] .' characters <br/>';
            }
        }
    }

    // If there are error messages, advise the user to check his inputs. Allow him to go to the previous page and correct his inputs.
    if ($errorMessage !== null) {
        echo <<<EOM
          <div class="container">
            <h1>Sorry, the information is incorrect. Pleace check again.</h1>
            <p>Error: </br/>$errorMessage</p>
            <button type='submit' class='btn btn-primary' value='Back' onClick='history.back()');>Back to previous page</button>
          </div>
    EOM;
        return false;
    } else {
        return true;
    }
}

// Get all item attributes from the user's input. Trim all string attributes.
function getUser()
{
    $user = array();
    $user['accountType'] = trim($_POST['accountType']);
    $user['email'] = trim($_POST['registerEmail']);
    $user['password'] = trim($_POST['inputPassword']);
    $user['firstName'] = ucfirst(trim($_POST['firstName']));
    $user['lastName'] = ucfirst(trim($_POST['lastName']));
    $user['PhoneNo'] = trim($_POST['phoneNo']);
    $user['street'] = trim($_POST['street']);
    $user['city'] = trim($_POST['city']);
    $user['postcode'] = trim($_POST['postcode']);
    return $user;
}

function printUser($user)
{
    echo <<<EOM
      <p>Account Type: <b>${user['accountType']}</b><br/>
      First Name: <b>${user['firstName']}</b><br/>
      Last Name: <b>${user['lastName']}</b><br/>
      Email: <b>${user['email']}</b></p>
    EOM;
}

// Get user inputs into the database.
function saveToDatabase($user, $conn)
{
    $query = "";
    if ($user['accountType'] == "buyer") {
        $query = "INSERT INTO buyer (fName, lName, email, password, phoneNo, street, city, postcode)
              VALUES (?, ?, ?, SHA(?), ?, ?, ?, ?)";
    } elseif ($user['accountType'] == "seller") {
        $query = "INSERT INTO seller (fName, lName, email, password, phoneNo, street, city, postcode)
                VALUES (?, ?, ?, SHA(?), ?, ?, ?, ?)";
    } else {
        $query = "INSERT INTO buyer (fName, lName, email, password, phoneNo, street, city, postcode)
              VALUES (?, ?, ?, SHA(?), ?, ?, ?, ?);";

        $query_2 ="INSERT INTO seller (fName, lName, email, password, phoneNo, street, city, postcode)
              VALUES (?, ?, ?, SHA(?), ?, ?, ?, ?)";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssssss",
        $user['firstName'],
        $user['lastName'],
        $user['email'],
        $user['password'],
        $user['PhoneNo'],
        $user['street'],
        $user['city'],
        $user['postcode']
    );
    /* execute query */
    $result = $stmt->execute();
    $stmt->close();

    if ($user['accountType'] == "both") {
        $stmt_2 = $conn->prepare($query_2);
        $stmt_2->bind_param(
            "ssssssss",
            $user['firstName'],
            $user['lastName'],
            $user['email'],
            $user['password'],
            $user['PhoneNo'],
            $user['street'],
            $user['city'],
            $user['postcode']
        );
        /* execute query */
        $result_2 = $stmt_2->execute();
        $stmt_2->close();
    }
    return $result;
}

// If the registration is successful then automatically log in and redirect to browse page.
function autologin($newUser, $conn)
{
    $login_account_type= $newUser['accountType'];
    if ($login_account_type == "both") {
        $login_account_type = "buyer";
    }
    // Check user account validation
    isLoginUserValid($login_account_type, $newUser['email'], $newUser['password'], $conn);
    echo "<h4>You will be automatically logged in and redirected to the home page shortly.</h4></div>";
    // Redirect to browse page after 10 seconds
    header("refresh:10;url=browse.php");
}

// Call the isDataValid function. If it is valid, call the saveToDatabase function to import the new data into the database.
if (isDataValid($conn)) {
    $newUser = getUser();
    if (saveToDatabase($newUser, $conn)) {
        echo '<div class="container"><h2>Registration successful, please login using your new account credentials! </h2>';
        printUser($newUser);
        // Send sign up sucessed email
        echo send_email($newUser['firstName'] . ' '. $newUser['lastName'], $newUser['email'], "signup", "");
        autologin($newUser, $conn);
    }
}
