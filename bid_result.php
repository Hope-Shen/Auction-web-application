<?php
    include_once("header.php");
    include_once("database.php");
    include_once("sendEmail.php");
    include_once("utilities.php");

    // Set (session) variables needed
    $new_bid_price = $_SESSION["new_price"];
    $item_id = $_GET["item_id"];
    $current_bid_price = $_SESSION["current_price"];
    $title = $_SESSION["title"];
    $buyer_id = $_SESSION["userID"];

?>

<!-- 
    Get POST variable from form in place_bid.php (YES or NO).
-->
<?php if($_POST["submit"] == "YES" and $new_bid_price != 0) : ?>
    <?php
        // values to send confirmation email for placeBid
        $content = array(1 => strval($new_bid_price), 2 => strval($title));
        $recipient_email = $_SESSION["email"];
        $username = $_SESSION['username'];
        $content_bid_confirmation = array(1 => strval($title), 2 => strval($new_bid_price));

        // get info from user who is the highest bidder until not, to send email 
        // get user ID (buyerEmail) => PK
        $sql = "SELECT buyerEmail
                FROM bids
                WHERE itemId = $item_id
                AND bidPrice = (SELECT MAX(bidPrice)
                                FROM bids
                                WHERE itemID = $item_id)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $highest_bidder = $row["buyerEmail"];

        // get highest bidder mail (again) and his first and last name concatenated
        $sql = "SELECT email, CONCAT(fName,' ', lName) as username
                FROM buyer
                WHERE email = '$highest_bidder';";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);                
        $highest_bidder_email = $row["email"];
        $highest_bidder_username = $row["username"];
        $content_outbid = array(1 => strval($current_bid_price), 2 => strval($title), 3 => strval($new_bid_price));                            

        // Get list of users watching the item to notify them that new bid has been placed
        $sql = "SELECT b.email, CONCAT(b.fName,' ', b.lName) AS username
                FROM listings_watched lw
                INNER JOIN buyer b ON b.email = lw.buyerEmail
                WHERE lw.itemID = $item_id;";
        $result = mysqli_query($conn, $sql);
        
        $type = "watchlistBid";
        $content_wl_bid = array(1 => strval($new_bid_price), 2 => strval($title));
        
        // send out emails that bid has been placed on item on watchlist to all users watching
        while($row=mysqli_fetch_assoc($result)){
            $wl_username = $row['username'];
            $wl_email = $row['email'];

            if ($wl_email != $buyer_id) {
                send_email($wl_username, $wl_email, $type, $content_wl_bid);
            }
            
        }

        // add item to watchlist of user who placed the bid, if not exists and send email for watchlist
        // first check if entry exists in watchlist
        $sql = "SELECT COUNT(1) as countTrue
                FROM listings_watched
                WHERE buyerEmail = '$buyer_id'
                AND itemID = $item_id;";
        $result = mysqli_query($conn, $sql);
        $is_watched = mysqli_fetch_assoc($result);

        // if item is not watched insert into watchlist
        if($is_watched["countTrue"] != 1) {
            $sql = "INSERT INTO listings_watched(`buyerEmail`, `itemID`)
                    VALUES ('$buyer_id', $item_id);";
            $conn->query($sql);

            // type and content for send_email function, that item has been added to watchlist
            $type = "addWatchlist";
            $content = array(1 => strval($title));
            send_email($username, $recipient_email, $type, $content);
        }


        // Insert new bid into database
        $sql = "INSERT INTO bids (`buyerEmail`, `itemID`, `bidPrice`, `bidDate`)
                VALUES ('$buyer_id', $item_id, $new_bid_price, now());";
        $conn->query($sql);

        

        // email bid confirmation
        $type = "placeBid";
        send_email($username, $recipient_email, $type, $content_bid_confirmation);

        // email you have been outbid
        if ($highest_bidder != $buyer_id) {
            $type = "outbid";
            send_email($highest_bidder_username, $highest_bidder_email, $type, $content_outbid);
        }
    ?>

    <!-- Output that bid is placed -->
    <div class="container">
        <div class="row">
            <div class="col-sm-8">
                <br>
                <h4> 
                Your bid for <strong>£&nbsp;<?php echo number_format($new_bid_price, 2)?></strong> 
                on the listing <strong><?php echo $title?></strong> is successfully placed! 
                </h4> 

<!-- 
    SPECIAL CASE:
    If user clicks back button after submitting a bit and resubmits it,
    the POST variable (i.e. new bid price) will be 0.
       
    This is added to catch this case and the user has to try submitting a bid again
-->
<?php elseif($_POST["submit"]=="YES" and $new_bid_price == 0) : ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-8">
                <h4> 
                Your bid was not placed.
                Please try again.
                </h4> 

<!-- if user submits NO as an answer in form on place_bid.php (Are you sure you want to submit a bid...) -->
<?php elseif($_POST["submit"]=="NO") : ?>
    <!-- Output for NO -->
    <div class="container">
        <div class="row">
            <div class="col-sm-8">
                <h4> 
                Your bid was not placed.
                </h4> 
            


<?php endif; ?>

<!-- Always redirect to listing --> 
<h6>You will be automatically redirected.</h6>
            </div>
        </div>
    </div> <!-- END container -->

<?php
    $_SESSION["new_price"] = NULL;
    header("refresh:5;url=listing.php?item_id=$item_id");
    
?>

<?php include_once("footer.php"); ?>