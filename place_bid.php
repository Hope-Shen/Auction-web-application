<?php
  header("Cache-Control: no cache");
  session_cache_limiter("private_no_expire");
?>
<?php
    include_once("header.php"); 
    include("database.php");
// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.

    // set values needed
    // create Sessions for bid_result.php
    $new_bid_price = $_POST["price"];
    $_SESSION["new_price"] = $new_bid_price;
    $item_id = $_GET["item_id"];
    $current_bid_price = $_SESSION["current_price"];
    $title = $_SESSION["title"];
    $start_price = $_SESSION["start_price"];

    // Get items buyer and seller email to prohibit buyers bidding on their own items
    $sql = "SELECT sellerEmail
            FROM items
            WHERE itemID = $item_id;";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $seller_id = $row["sellerEmail"];

    $buyer_id = $_SESSION["userID"];
?>

<!-- Check if the buyer is not the same as the seller who listed the auction -->
<?php if ($buyer_id != $seller_id) : ?>
    <?php 
        // Only allow to submit bid price if the new bid price is higher then the current highest bid price by 1%.
        // And if the new bid price is higher than the start price
        if($new_bid_price>($current_bid_price*1.01) and $new_bid_price >= $start_price) :;
        // create session for bid_result.php
        $_SESSION["new_price"] = $new_bid_price; 

    ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-8">
                <br>
                <!-- Ask user if he is sure that he wants to place the bid -->
                <h4> 
                    Are you sure you want to place a bid for <strong>£&nbsp;<?php echo number_format($new_bid_price, 2)?></strong> 
                    on the listing <strong><?=$title?></strong>? 
                </h4> 
                <br>
                <!-- form for answer -->
                <form action="bid_result.php?item_id=<?=$item_id?>" method="post">
                    <button name="submit" type="submit" value="YES" class="btn btn-primary form-control">Yes</button>
                    <button name="submit" type="submit" value="NO" class="btn btn-primary form-control" style="background-color:red; border:red;margin-top:2px;">No</button>
                </form>
            </div>
        </div>
    </div>

    <?php 
        // If bid price is invalid:
        // calculate the minimum bid price to tell user what price he has to type in at least.
        else :;
            if ($new_bid_price <= $start_price and $current_bid_price<$start_price) {
                $min_price = $start_price;
            } else {
                $min_price = ($current_bid_price)*1.01;
            }
            
    ?>
    <!-- redirect to item page if bid is invalid -->
    <?php header("refresh:7;url=listing.php?item_id=$item_id"); ?>
    
    <!-- Output message for invaluid bid price -->
    <div class="container">
        <div class="row"> 
            <div class="col-sm-8"> 
                <br>
                <h4 style="color:red;font-weight:bold;">Invalid bid price</h4>
                <h4>
                    The bid price of <strong>£&nbsp;<?php echo number_format($new_bid_price, 2)?> </strong> is invalid
                    for the listing <strong><?=$title?></strong>.
                </h4> 
                <h4>The new bid price must be at least <strong>£&nbsp;<?php echo number_format($min_price, 2)?><strong>.</h4>
                <br>
                <h6>You will be automatically redirected to the listing.</h6> 
                    
                
            </div>
        </div>
    </div>
    <?php endif; ?>

<!-- Output message if user wants to bid on his "own" item -->
<?php else: ?>
    <?php header("refresh:7;url=listing.php?item_id=$item_id"); ?>
    <div class="container">
        <div class="row"> 
            <div class="col-sm-8"> 
                <br>
                <h4 style="color:red;font-weight:bold;">Bid cannot be submitted</h4>
                <h4>
                    It is not possible to place a bid on your own listing.
                </h4> 
                <br>
                <h6>You will be automatically redirected to the listing.</h6> 
            </div>
        </div>
    </div>

<?php endif;?>
<?php include_once("footer.php"); ?>