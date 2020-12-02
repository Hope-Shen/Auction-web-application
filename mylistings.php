<?php include_once("header.php"); require("utilities.php"); include("database.php");
 // Retrieve these from the URL
if (!isset($_GET['page'])) {
    $curr_page = 1;
} else {
    $curr_page = $_GET['page'];
}

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['userID'])) {
    ;
}


?>


<div class="container">
<h2 class="my-3">My listings</h2>
<div class="container mt-5">

<ul class="list-group">

<?php
  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  // TODO: Check user's credentials (cookie/session).
    $id = $_SESSION['userID'];

  // TODO: Perform a query to pull up their auctions.
  $query = "";
  if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller') {
      $query = "SELECT i.itemID, itemName, description, categoryID, IFNULL(b.numBid, 0) as numBid, IFNULL(b.currentPrice, i.startPrice) as currentPrice, DATE_FORMAT(endDate, '%Y-%m-%dT%H:%i:%s') as endDate
              FROM items as i
              LEFT JOIN (SELECT itemID ,count(*) as numBid, Max(bidPrice) as currentPrice FROM bids GROUP BY itemID) as b
              On i.itemID = b.itemID
              WHERE sellerEmail = '$id' ";

      // Get the total result in order to set up the pagination
      $results_per_page = 10;
      $result_pagination = mysqli_query($conn, $query) or die($conn->error);
      $start_record = ($curr_page - 1) * $results_per_page;
      $max_page = ceil(mysqli_num_rows($result_pagination) / $results_per_page);


      $query_for_each_page = $query . " LIMIT $start_record, $results_per_page";
      $result = mysqli_query($conn, $query_for_each_page) or die($conn->error);

      $expireditems = "SELECT itemID FROM items WHERE NOW() > endDate AND sellerEmail = '$id' ";
      $expiredresult = mysqli_query($conn, $expireditems);
      $expiredlist = array();
      while ($row_temp1 = mysqli_fetch_assoc($expiredresult)) {
        $expiredlist[] = $row_temp1["itemID"];
      }


      
      // // get sold items for seller
      $sql = "SELECT p.itemID AS itemID
            FROM purchase p
            INNER JOIN bids b ON p.itemID = b.itemID
            INNER JOIN items i ON b.itemID = i.itemID
            WHERE i.sellerEmail = '$id';";
      $sold_result = mysqli_query($conn, $sql);
      // //$sold_list = mysqli_fetch_assoc($sold_result);

      // // array of all sold items
      $sold_list = array();
      while ($row_temp2 = mysqli_fetch_assoc($sold_result)) {
          $sold_list[] = $row_temp2["itemID"];
      }


      // array of unsold items

      // TODO: Loop through results and print them out as list items.
      while ($row = $result->fetch_assoc()) {
          // if item is sold call funtion woth sold ribbon
          if (in_array($row["itemID"], $sold_list)) {
              print_listing_li_sold($row["itemID"], $row["itemName"], $row["description"], $row["currentPrice"], $row["numBid"], $row["endDate"]);
          } elseif (in_array($row["itemID"], $expiredlist)) {
            print_listing_li_unsold($row["itemID"], $row["itemName"], $row["description"], $row["currentPrice"], $row["numBid"], $row["endDate"]);
          } else {
              print_listing_li($row["itemID"], $row["itemName"], $row["description"], $row["currentPrice"], $row["numBid"], $row["endDate"]);
          }
      }

      if (mysqli_num_rows($result)==0) {
          echo <<<EOM
      <div class="container">
        <h1 style="font-size:170%"> You have no listings currently. </h1>
        <p> To add a new listing, click Create Auction. </p>
      </div>
      EOM;
      }
  }
?>

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">

<?php
  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
      if ($key != "page") {
          $querystring .= "$key=$value&amp;";
      }
  }

  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

  if ($curr_page != 1) {
      echo('
    <li class="page-item">
      <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }

  for ($i = $low_page; $i <= $high_page; $i++) {
      if ($i == $curr_page) {
          // Highlight the link
          echo('
    <li class="page-item active">');
      } else {
          // Non-highlighted link
          echo('
    <li class="page-item">');
      }

      // Do this in any case
      echo('
      <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  if ($curr_page != $max_page and $max_page != 0) {
      echo('
    <li class="page-item">
      <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>

</div>


<?php include_once("footer.php")?>
