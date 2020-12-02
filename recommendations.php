<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php include 'database.php';?>


<?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  //Initialise all variables with null, so they can be overwritten later.

    $keyword = null;
    $category = null;
    $ordering = null;
  
  // TODO: Check user's credentials (cookie/session).
 
  if(!isset($_SESSION))
  {
    session_start();
  }

  if (isset($_SESSION['userID'])) {
    ;
  }

  // TODO: Perform a query to pull up auctions they might be interested in.
  //Show auctions, that user has not yet bid on. Show auctions, that have same category as the item he already has bid on.
  
  // TODO: Loop through results and print them out as list items.


?>


<?php 
  // Retrieve these from the URL
  if (!isset($_GET['keyword'])) {
      // TODO: Define behavior if a keyword has not been specified.
  } else {
      $keyword = $_GET['keyword'];
  }

  if (!isset($_GET['cat'])) {
      // TODO: Define behavior if a category has not been specified.
  } else {
      $category = $_GET['cat'];
  }

  if (!isset($_GET['order_by'])) {
      // TODO: Define behavior if an order_by value has not been specified.
  } else {
      $ordering = $_GET['order_by'];
  }
  if (!isset($_GET['page'])) {
      $curr_page = 1;
  } else {
      $curr_page = $_GET['page'];
  }
?>


<div class="container">
<!-- Buyers will see auctions that other people, who have bid on similar auctions like the buyer (based on categoryID), are currently bidding on.-->
<h2 class="my-3">Recommendations for you <p style = "font-size:50%; font-weight:normal;">Customers who bid on similar items like you are also bidding on these. </p> </h2>
</div>

<?php
  //Determine the maximum amount of listings that appear per page.
  $results_per_page = 10;

  //Determine the first record of the page. This variable will be used later in a SQL query as the starting point of the LIMIT range.
  $start_record = ($curr_page - 1) * $results_per_page;

  //Retrieve the buyerID.
  $buyerID = $_SESSION['userID'];



  //Subqueries
  //Count the bids per item and select the maximum bid price as the current price for each item.
  $numbid_curr_price_per_item = "SELECT itemID, count(*) as numBid, Max(bidPrice) as currentPrice FROM bids GROUP BY itemID";

  //Select all items of a specific buyer.
  $items_of_buyer ="SELECT itemID FROM bids WHERE buyerEmail = '$buyerID'";
  

  //Select all categories that the specific buyer has bid on.
  $categories_bid_by_spec_buyer = "SELECT c.categoryID FROM bids b INNER JOIN items c ON c.itemID = b.itemID WHERE b.buyerEmail = '$buyerID'"; 

  //Select all buyers that have bid on the same categories as the specific buyer.
  $buyers_same_categories = "SELECT b.buyerEmail FROM bids b LEFT JOIN items c ON c.itemID = b.itemID WHERE c.categoryID in (".$categories_bid_by_spec_buyer.")";

  //Select all items that all buyers, who have bid on the same categories as the specific buyer, have bid on.
  $recommended_items = "SELECT itemID FROM bids WHERE buyerEmail in (".$buyers_same_categories.")";


  //Main SQL query, containing all the relevant subqueries and listing all the recommended items for the buyer.
  $main_query_items = "SELECT i.itemID, itemName, description, i.categoryID, b.numBid, 	b.currentPrice, DATE_FORMAT(endDate, '%Y-%m-%dT%H:%i:%s') as endDate
                        FROM items as i
                        LEFT JOIN (".$numbid_curr_price_per_item.") b
                      ON i.itemID = b.itemID
                      WHERE now() < endDate and numBid > 0 and i.itemID NOT IN (".$items_of_buyer.") and i.itemID IN (".$recommended_items.")
                      ORDER BY endDate";

  


  //SQL query, counting the number of the recommended items to determine the pagination.
  $sql = "SELECT COUNT(*) as count FROM (".$main_query_items.") t ";
  $result = mysqli_query($conn, $sql) or die('Error making select login query' . mysql_error());
  $num_results = mysqli_fetch_assoc($result);
  $max_page = ceil($num_results['count'] / $results_per_page);
?>

<div class="container mt-5">

<ul class="list-group">
<?php
//This query will show up to 10 recommendations per page.
  $sql_query_items_page = $main_query_items . " LIMIT $start_record, $results_per_page";
  $result = mysqli_query($conn, $sql_query_items_page) or die('Error making select login query' . mysql_error());

  

  //If there exist some recommended items, display certain features of these items. Otherwise, inform the user that 
  //he will receive personalised recommendations once he becomes an active user.
  $num_results = mysqli_num_rows($result);
  if ($num_results > 0) {
      // output data of each row
      while ($row = mysqli_fetch_assoc($result)) {
          print_listing_li($row["itemID"], $row["itemName"], $row["description"], $row["currentPrice"], $row["numBid"], $row["endDate"]);
      }
  } else {
      echo <<<EOM
      <div class="container">
        <h2 class="my-3">You have no recommendations yet. <p style = "font-size:50%; font-weight:normal;">Once you become an active user, e.g. by placing a bid, you will receive personalised recommendations about auctions that fit your needs. </p></h2>
      </div>
      EOM;
  }
?>
</ul>

<!-- Pagination for recommended items. -->
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

  //Displays a range of 5 pages. The range depends on the current page and is calculated as follows.
  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);


  //If the current page is not 1, a 'previous' button will enable the user to go back to the previous page.
  if ($curr_page != 1) {
      echo('
    <li class="page-item">
      <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }

  //For a range of 5 pages highlight the current page only.
  for ($i = $low_page; $i <= $high_page; $i++) {
      if ($i == $curr_page) {
          // Highlighted link.
          echo('
    <li class="page-item active ">');
      } else {
          // Non-highlighted link.
          echo('
    <li class="page-item ">');
      }

      // Link to any page inside the range.
      echo('
      <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  //If the current page is not the last one, a 'next' button will enable the user to go to the next page.
  if ($curr_page != $max_page and $max_page != 0) {
      echo('
    <li class="page-item">
      <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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
