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
<h2 class="my-3">My bids</h2>
<div class="container mt-5">

<ul class="list-group">

<?php
  // This page is for showing a user the auctions they've bid on.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  // TODO: Check user's credentials (cookie/session).
  $id = $_SESSION['userID'];

  // TODO: Perform a query to pull up the auctions they've bidded on.
  $query = "";
  if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer') {
      $query = "SELECT i.itemID, itemName, description, categoryID, DATE_FORMAT(endDate, '%Y-%m-%dT%H:%i:%s') as endDate, IFNULL(c.numBid, 0) as numBid, c.currentPrice, b.buyerEmail
              FROM items as i
              JOIN bids as b
              On i.itemID = b.itemID
              LEFT JOIN(SELECT itemID, count(*) as numBid, max(bidPrice) as currentPrice FROM bids GROUP BY itemID) as c
              On b.itemID = c.itemID
              WHERE b.buyerEmail = '$id' AND NOW() < endDate GROUP BY itemID ";

      // Get the total result in order to set up the pagination
      $results_per_page = 10;
      $result_pagination = mysqli_query($conn, $query) or die($conn->error);
      $start_record = ($curr_page - 1) * $results_per_page;
      $max_page = ceil(mysqli_num_rows($result_pagination) / $results_per_page);


      $query_for_each_page = $query . " LIMIT $start_record, $results_per_page";
      $result = mysqli_query($conn, $query_for_each_page) or die($conn->error);

      // TODO: Loop through results and print them out as list items.
      while ($row = $result->fetch_assoc()) {
          print_listing_li($row["itemID"], $row["itemName"], $row["description"], $row["currentPrice"], $row["numBid"], $row["endDate"]);
      }

      if (mysqli_num_rows($result)==0) {
          echo <<<EOM
      <div class="container">
        <h1 style="font-size:170%"> You have not placed bids on any items. </h1>
        <p> View listings and place bids on items via the Browse page. </p>
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
      <a class="page-link" href="mybids.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
      <a class="page-link" href="mybids.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  if ($curr_page != $max_page and $max_page != 0) {
      echo('
    <li class="page-item">
      <a class="page-link" href="mybids.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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
