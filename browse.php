<?php
//Include PHP files referenced here
include_once("header.php"); require("utilities.php"); include 'database.php';

  // Retrieve information from the URL
  if (!isset($_GET['keyword'])) {
      // TODO: Define behavior if a keyword has not been specified.
      $keyword = null;
  } else {
      $keyword = $_GET['keyword'];
  }

  if (!isset($_GET['cat'])) {
      // TODO: Define behavior if a category has not been specified.
      $category = null;
  } else {
      $category = $_GET['cat'];
  }

  if (!isset($_GET['order_by'])) {
      // TODO: Define behavior if an order_by value has not been specified.
      $ordering = null;
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
<h2 class="my-3">Browse listings</h2>
<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" name = "keyword" id="keyword" placeholder="Search for anything" value="<?php echo $keyword?>">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" name="cat" id="cat">
          <option value="all">All categories</option>
          <?php
            // Select all categories from DB
            $sql_query_category = "SELECT categoryID, categoryName FROM category;";
            $result = mysqli_query($conn, $sql_query_category) or die($conn->error);
            // Query data
            if (mysqli_num_rows($result) > 0) {
                // output data of each row
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = $category == $row['categoryID']? "selected" : "";
                    echo <<< EOM
                      <option value="${row['categoryID']}" $selected >${row['categoryName']}</option>
                   EOM;
                }
            }
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" name="order_by" id="order_by">
          <?php
            echo "<option value='pricelow' " . ($ordering == 'pricelow'? 'selected' : '') . '>Price (low to high)</option>';
            echo "<option value='pricehigh' ". ($ordering == 'pricehigh'? 'selected' : '') . '>Price (high to low)</option>';
            echo "<option value='date' " . ($ordering == 'date'? 'selected' : '') . '>Expiring first</option>';
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->
</div>

<div class="container mt-5">
<ul class="list-group">
<?php
  $query_condition = "";
  if ($keyword != "") {
      $query_condition .= " AND itemName like '%" . $keyword . "%' ";
  }

  if ($category != "" and $category != "all") {
      $query_condition .= " AND categoryID = " . $category . " ";
  }

  $query_ordering = "";
  if ($ordering == "" or $ordering == "pricelow") {
      $query_ordering .= " ORDER BY currentPrice ";
  } elseif ($ordering == "pricehigh") {
      $query_ordering .= " ORDER BY currentPrice DESC ";
  } elseif ($ordering == "date") {
      $query_ordering .= " ORDER BY enddate ";
  }
  // Perform a query to pull up the active auctions with custom condition and order
  $sql_query_items = "SELECT i.itemID, itemName, description, categoryID, IFNULL(b.numBid, 0) as numBid, IFNULL(b.currentPrice, i.startPrice) as currentPrice, DATE_FORMAT(endDate, '%Y-%m-%dT%H:%i:%s') as endDate
                      FROM items as i
                      LEFT JOIN (SELECT itemID ,count(*) as numBid, Max(bidPrice) as currentPrice FROM bids GROUP BY itemID) as b
                      On i.itemID = b.itemID
                      WHERE NOW() < endDate " . $query_condition . $query_ordering;

  $result_pagination = mysqli_query($conn, $sql_query_items) or die($conn->error);
  /* For the purposes of pagination, it would also be helpful to know the
     total number of results that satisfy the above query */

 //Determine the maximum amount of listings that appear per page.
 $results_per_page = 10;
 //Determine the first record of the page. This variable will be used later in a SQL query as the starting point of the LIMIT range.
 $start_record = ($curr_page - 1) * $results_per_page;
 // Get the total result in order to set up the pagination
 $max_page = ceil(mysqli_num_rows($result_pagination) / $results_per_page);

 $query_for_each_page = $sql_query_items . " LIMIT $start_record, $results_per_page";
 $result = mysqli_query($conn, $query_for_each_page) or die($conn->error);
 $num_results = mysqli_num_rows($result);
  if ($num_results > 0) {
      // Loop through results and print them out as list items.
      while ($row = mysqli_fetch_assoc($result)) {
          print_listing_li($row["itemID"], $row["itemName"], $row["description"], $row["currentPrice"], $row["numBid"], $row["endDate"]);
      }
  } else {
      // If there are no result match, print out error message to user
      echo <<<EOM
      <div class="container">
        <h1>OOPS, NOTHING MATCHES YOUR SEARCH. </h1>
        <p>Try checking your spelling or using less specific search terms</p>
      </div>
      EOM;
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  if ($curr_page != $max_page and $max_page != 0) {
      echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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
