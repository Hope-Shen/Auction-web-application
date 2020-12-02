<?php
include_once("database.php");
include("sendEmail.php");
session_start();

// set buyer_id: user can only be buyer, as watchlist function is only available to buyers
$buyerID = $_SESSION['userID'];


if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}


$item_id = $_SESSION['itemID'];

// set values needed for send email function
$username = $_SESSION['username'];
$recipient_email = $_SESSION['email'];

// get item title/name from DB
$sql = "SELECT itemName
        FROM items
        WHERE itemId=$item_id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$title = strval($row["itemName"]);
$content = array(1 => $title);

// add item to watchlist
if ($_POST['functionname'] == "add_to_watchlist") {
  // TODO: Update database and return success/failure.
  
  // set email type for send_email function (in send_email.php), to notify user.
  $type = "addWatchlist";

  // Insert row into listings_watched table
  $sql = "INSERT INTO listings_watched(`buyerEmail`, `itemID`)
          VALUES ('$buyerID', $item_id);";
  $conn->query($sql);


  $res = "success";
  
  
}
// remove item from watchlist
else if ($_POST['functionname'] == "remove_from_watchlist") {
  // TODO: Update database and return success/failure.
  
  // set email type for send_email function (in send_email.php), to notify user.
  $type = "remWatchlist";

  // Delete row from listings_watched table
  $sql = "DELETE FROM listings_watched
          WHERE buyerEmail='$buyerID'
          AND itemID=$item_id;";
  $conn->query($sql);

  $res = "success";
}


// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

// send out email
send_email($username, $recipient_email, $type, $content);

?>