
<?php include_once("database.php")?>
<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php

  // Get itemID from the URL:
  $item_id = $_GET['item_id'];

  // Get userID (userEmail) from SESSION, giving variable name according to account type 
  if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == "buyer") {
    $buyerID = $_SESSION['userID'];
    $is_buyer = TRUE;
    $_SESSION["buyerID"] = $buyerID;
  
  } elseif (isset($_SESSION['account_type']) && $_SESSION['account_type'] == "seller") {
    $sellerID = $_SESSION['userID'];
    $is_seller = TRUE;
  } 

  // Query the Item/Listing, get row with item info
  $sql = "SELECT * FROM items WHERE itemID = $item_id;";
  $result = mysqli_query($conn, $sql);
  $listing = mysqli_fetch_assoc($result);

  // Get the current highest bid price
  $sql = "SELECT MAX(bidPrice) AS bidPrice
          FROM bids 
          WHERE itemID = $item_id;"; 
  $result = mysqli_query($conn, $sql);
  $bid_price = mysqli_fetch_assoc($result);

  // Get the current number of bids
  $sql = "SELECT COUNT(bidID) AS bidCount
          FROM bids 
          WHERE itemID = $item_id;";
  $result = mysqli_query($conn, $sql);
  $num_bids = mysqli_fetch_assoc($result);

  // Get the item category name
  $sql = "SELECT c.categoryName as category
          FROM category c, items i
          WHERE (c.categoryID = i.categoryID
                 AND i.itemID = $item_id);";
  $result = mysqli_query($conn, $sql);
  $category = mysqli_fetch_assoc($result);

  // Get amount of users watching the item
  $sql = "SELECT COUNT(watchID) AS watchCount
          FROM listings_watched 
          WHERE itemID = $item_id;";
  $result = mysqli_query($conn, $sql);
  $listings_watched = mysqli_fetch_assoc($result);

  // assign all item/listing infos to variables
  $title = $listing["itemName"];
  $category_name = $category["category"];
  $condition_of_use = $listing["conditionOfUse"];
  $colour = $listing["colour"];
  $gearbox = $listing["gearbox"];
  $fuel_type = $listing["fuelType"];
  $initial_reg = $listing["initialReg"];
  $doors = $listing["doors"];
  $seats = $listing["seats"];
  $mileage = $listing["mileage"];
  $acceleration_0_60 = $listing["acceleration0to60mph"];
  $top_speed = $listing["topSpeedMph"];
  $engine_power = $listing["enginePowerBhp"];
  $description = $listing["description"];
  $start_price = $listing["startPrice"];
  $reserve_price = $listing["reservePrice"];
  $current_price = $bid_price["bidPrice"];
  $num_bids = $num_bids["bidCount"];
  $num_watched = $listings_watched["watchCount"];
  
  // Create session for variables needed in place_bid.php and watch_listing.php
  $_SESSION["current_price"] = $current_price;
  $_SESSION["title"] = $title;
  $_SESSION['itemID'] = $item_id;
  $_SESSION['start_price'] = $start_price;

  // Get the current highest bidder
  $sql = "SELECT buyerEmail
          FROM bids
          WHERE itemID = $item_id
          AND bidPrice = (SELECT MAX(bidPrice)
                          FROM bids
                          WHERE itemID = $item_id);";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  $highest_bidder = $row["buyerEmail"];

  // Set the time DateTime objects for item endDate and the current time   
  $timezone = date_default_timezone_set('Europe/London');
  
  $listing_end = $listing["endDate"];

  $end_time = new DateTime($listing_end);
  $now = new DateTime(date("Y-m-d H:i:s"));

  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not.
  
  // Calculate time to auction end:
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  
  // TODO: If the user has a session, use it to make a query to the database
  //       to determine if the user is already watching this item.
  //       For now, this is hardcoded.

  // Check if user is logged in (has session)
  if (isset($_SESSION['logged_in']) and ($_SESSION['logged_in'] == true)) {
    $has_session = true;
  } else {
    $has_session = false;
  }
  
  // Query database to check if user is waching
  if (isset($is_buyer)) {
    $sql = "SELECT COUNT(watchID) as wCount
            FROM listings_watched
            WHERE buyerEmail = '$buyerID'
            AND itemID = $item_id;";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $check_watching = $row["wCount"];
  }
  
  // assign true ir false to watching variable
  if(isset($check_watching) and $check_watching==1) {
    $watching = true;
  } else {
    $watching = false;
  }
  
?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div> <!-- END Left col -->
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>

  <!-- if account type is seller, do not display watchlist button -->
  <?php if (isset($is_seller) and $is_seller == true) : ?>
    <style type="text/css">#watch_nowatch{display:none;}</style>
  <?php endif?>

  <!-- if not logged in, direct to login form when try adding to watchlist -->
  <?php if ($has_session == false) : ?>
    <!-- hide functioning watchlist button if user is not logged in -->
    <style type="text/css">#watch_nowatch{display:none;}</style>
    <!-- dummy button to login when clicked, directing user to login form -->
    <div id="watch_nowatch_dummy">
      <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#loginModal">+ Add to watchlist</button>
    </div> 
  <?php endif?>
  
  <!-- Watchlist buttons -->
  <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
  </div>
  <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
    <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
    <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
  </div>
<?php endif /* Print nothing otherwise */ ?>
  </div> <!-- END Right col -->
</div> <!-- END Row #1 with auction title + watch button -->

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->
  <?php
  // Query item images
  $sql = "SELECT image1, image2, image3 FROM items WHERE itemID = $item_id";
  $sth = $conn->query($sql);
  $result=mysqli_fetch_array($sth);
  ?>
    
        <!-- slideshow -->
        <div id="demo" class="carousel slide" data-ride="carousel" style="width:100%; height: 500px !important; margin-bottom:30px;">

          <!-- Indicators -->
          <ul class="carousel-indicators">
            <li data-target="#demo" data-slide-to="0" class="active"></li>
            <li data-target="#demo" data-slide-to="1"></li>
            <li data-target="#demo" data-slide-to="2"></li>
          </ul>

          <!-- The slideshow -->
          <div class="carousel-inner">
            <div class="carousel-item active">
              <!-- Image #1 -->
              <img style=" width:100%; height: 500px !important;" src="data:image1/jpeg;base64,<?=base64_encode($result['image1'])?>"/>
            </div>
            <div class="carousel-item">
              <!-- Image #2 -->
              <img style=" width:100%; height: 500px !important;" src="data:image2/jpeg;base64,<?=base64_encode($result['image2'])?>"/>
            </div>
            <div class="carousel-item">
              <!-- Image #3 -->
              <img style=" width:100%; height: 500px !important;" src="data:image3/jpeg;base64,<?=base64_encode($result['image3'])?>"/>
            </div>
          </div>

          <!-- Left and right controls -->
          <a class="carousel-control-prev" href="#demo" data-slide="prev">
            <span class="carousel-control-prev-icon"></span>
          </a>
          <a class="carousel-control-next" href="#demo" data-slide="next">
            <span class="carousel-control-next-icon"></span>
          </a>

        </div>

    <!-- Table with item information/descriptions -->
    <div class="itemDescription">
    <table style="width:100%">
      <tr>
        <td><strong>Category</strong></td>
        <td><?=$category_name?></td> 
      </tr>
      <tr>
        <td><strong>Condition of Use</strong></td>
        <td><?=$condition_of_use?></td> 
      </tr>
      <tr>
        <td><strong>Mileage</strong></td>
        <td><?=number_format($mileage, 0, ',', ',')?></td> 
      </tr>
      <tr>
        <td><strong>Color</strong></td>
        <td><?=$colour?></td> 
      </tr>
      <tr>
        <td><strong>Gearbox</strong></td>
        <td><?=$gearbox?></td> 
      </tr>
      <tr>
        <td><strong>Fuel Type</strong></td>
        <td><?=$fuel_type?></td> 
      </tr>
      <tr>
        <td><strong>Initial Registration</strong></td>
        <td><?=$initial_reg?></td> 
      </tr>
      <tr>
        <td><strong>Doors</strong></td>
        <td><?=$doors?></td> 
      </tr>
      <tr>
        <td><strong>Seats</strong></td>
        <td><?=$seats?></td> 
      </tr>
      <tr>
        <td><strong>Acceleration (0-60 mph)</strong></td>
        <td><?=$acceleration_0_60?></td> 
      </tr>
      <tr>
        <td><strong>Top Speed</strong></td>
        <td><?=$top_speed?></td> 
      </tr>
      <tr>
        <td><strong>Engine Power (bhp)</strong></td>
        <td><?=$engine_power?></td> 
      </tr>
    </table>
    
    <hr>
    <p> <?=$description?> </p>
    

    </div>

  </div>

<div class="col-sm-4"> <!-- Right col with bidding info -->

<!-- 
  If listing has expired print outcome.
  If item is purchased print purchase price.
  If item is not purchased print highest bid price (should be lower then reserve price).
  If no bid has been placed only print item has not been sold.
--> 
<?php if ($now > $end_time): ?>
  <p>
  This auction ended <?php echo(date_format($end_time, 'j M H:i')) ?>
  </p>
  
  <?php 
  // Check if item is purchased
  $sql = "SELECT COUNT(p.purchaseID) AS pCount
          FROM purchase p
          WHERE itemID=$item_id;";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  $check_purchase = $row["pCount"];

  // get purchase price, e.g. highest bid price
  $sql = "SELECT b.bidPrice AS purchasePrice
          FROM purchase p 
          INNER JOIN items i ON p.itemID = i.itemID
          INNER JOIN bids b ON i.itemID = b.itemID
          WHERE b.itemID=$item_id
          AND b.bidPrice = (SELECT MAX(bidPrice)
                            FROM bids
                            WHERE itemID = $item_id);";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  $purchase_price = $row["purchasePrice"];
  ?>

  <!-- Print result depending on condition -->
  <?php if (intval($check_purchase)==1): ?>
    <p> The item <?=$title?> has been purchased for <span style="color:#38B6FF;font-weight:bold;">£&nbsp;<?=$purchase_price?></p>
  <?php else: ?>
    <?php if ($current_price != 0) : ?>
      <p> The item <?=$title?> has not been purchased. <br>The highest bid price was <span style="font-weight:bold;">£&nbsp;<?=$current_price?></p>
    <?php else : ?>
      <p> The item <?=$title?> has not been purchased. </p>
    <?php endif ; ?>
  <?php echo $check_purchase?>

  <?php endif ?>
     
<!-- Active auction --> 
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  
    <p class="lead" style="margin-bottom:0px;color:#38B6FF;font-weight:bold;">Start price: £<?php echo(number_format($start_price, 2)) ?></p>
    <p class="lead" style="margin-top:0px;margin-bottom:2px;">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>
    <p><span style="color:#38B6FF;font-weight:bold;"><?=$num_bids?> Bids</span> already placed<p>

    <!-- Bidding form -->
    <?php if ($has_session==true) : ?>
      <!-- hide bidding form if logged in as seller -->
      <?php if (isset($is_seller) and $is_seller == true) : ?>
        <style type="text/css">#submission_form{display:none;}</style>
      <?php endif?>
      <!-- Direct to place_bid.php to check for bid validity and ask user for definite answer (submit or nor submit bid) -->
      <form id="submission_form" method="post" action="place_bid.php?item_id=<?=$item_id?>">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">£</span>
          </div>
        <input type="number" name="price" placeholder="Type in a price" class="form-control" id="bid">
        </div>
        <button type="submit" name="submit" value="submit" class="btn btn-primary form-control">Place bid</button>
      </form>
      <?php if (isset($is_buyer) and $highest_bidder == $buyerID) : ?>
        <p class="lead" style="font-weight:bold;">You are currently the highest bidder</p>
      <?php endif ?>

    <!-- if no session: ask user to login when submitting a bid -->
    <?php else : ?>
      <form>
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">£</span>
          </div>
        <input type="number" name="price" placeholder="Type in a price" class="form-control" id="bid">
        </div>
        <button type="button" class="btn btn-primary form-control" data-toggle="modal" data-target="#loginModal">Place bid</button>        
      </form>
    <?php endif ?>
    

<?php endif ?>

<!-- Display number of users watching the item -->
<br>
<img src="images/watched_icon.jpg" alt="Watchlist icon" width="30" height=30>
<span style="color:#38B6FF;"><strong><?=$num_watched?></strong></span>



  
  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php")?>


<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>