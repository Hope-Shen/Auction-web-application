<?php
//Include PHP files referenced here
include_once("sendEmail.php");
include 'database.php';

$sql ="
/* Get expired auctions' outcome and insert into a temporary table, which is defined by current DateTime greater than endDate and items is not in purchase table.
The columns include itemID(item was sold), item Name, seller email, seller name , buyer(winner) email, buyer name, winner's bidID, sold price(maximum bid price is greater than reserved price) and enddate */
CREATE TEMPORARY TABLE temp_auction_outcome
SELECT org.itemID,items.itemName, items.sellerEmail, CONCAT(seller.fName, ' ', seller.lName) As SellerName,
    bidID, org.buyerEmail, CONCAT(buyer.fName, ' ', buyer.lName) As BuyerName, maxPrice.price AS buyerBidPrice
FROM bids as org
INNER JOIN
 (SELECT itemID, max(bidPrice)as price
  FROM bids
  WHERE itemID in (SELECT itemID FROM items
           WHERE NOW() > endDate
           AND itemID NOT IN (SELECT itemID FROM purchase))
  GROUP BY itemID) as maxPrice
ON org.itemID = maxprice.itemID and org.bidPrice = maxprice.price
INNER JOIN items
ON org.itemID = items.itemID and maxprice.price >= items.reservePrice
INNER JOIN buyer
ON buyer.email = org.buyerEmail
INNER JOIN seller
ON seller.email = items.sellerEmail;

/* Insert result into purchase table in order to save which item had been sold. */
INSERT INTO purchase(itemID)
SELECT itemID FROM temp_auction_outcome;

/* Get expired items which are still in the watchlist and insert into a temporary table. */
CREATE TEMPORARY TABLE temp_remove_lw
SELECT items.itemID, items.itemName, lw.buyerEmail, CONCAT(buyer.fName, ' ', buyer.lName) AS name
FROM items
JOIN listings_watched AS lw
ON lw.itemID = items.itemID
JOIN buyer
ON buyer.email = lw.buyerEmail
WHERE NOW() > items.endDate;

/* Delete temp_remove_lw records */
DELETE FROM listings_watched
WHERE itemID IN (SELECT DISTINCT itemID FROM temp_remove_lw);

/* Select information of the winner who has won the item in order to send the email announcement (you won the item).
And also select seller information of the item sold in order to send the email announcement(you won the item) */
SELECT 'timer_event', temp_auction_outcome.* FROM temp_auction_outcome;

/* Select information of buyer who had added expiry items into the watch list but had not won the item in order to send the email announcement (item has been closed and sold to someone else). */
SELECT 'itemClosed', temp_remove_lw.*
FROM temp_remove_lw
WHERE temp_remove_lw.buyerEmail NOT IN (select buyerEmail from temp_auction_outcome);

/* Drop both temporary tables */
DROP TEMPORARY TABLE temp_auction_outcome;
DROP TEMPORARY TABLE temp_remove_lw;";

$errmsg = "";
if (mysqli_multi_query($conn, $sql)) {
    do {
        /* store first result set */
        if ($result = mysqli_store_result($conn)) {
            while ($row = mysqli_fetch_row($result)) {
                if ($row[0] == "itemClosed") {
                    $item_name = $row[2];
                    $buyer_mail = $row[3];
                    $buyer_name = $row[4];
                    send_email($buyer_name, $buyer_mail, "itemClosed", $item_name);
                }
                if ($row[0] == "timer_event") {
                    $item_name = $row[2];
                    $seller_mail = $row[3];
                    $seller_name = $row[4];
                    $buyer_mail = $row[6];
                    $buyer_name = $row[7];
                    $price = $row[8];
                    send_email($seller_name, $seller_mail, "timer_event", array("seller", $item_name, $price));
                    send_email($buyer_name, $buyer_mail, "timer_event", array("buyer", $item_name, $price));
                }
                // $email_type = ($row[0] == "itemClosed" ? "itemClosed" : "timer_event");
                // send_email($user_name, $user_mail, $email_type, array($user_type, $item_name, $price));

                // echo $user_name. ' '. $user_mail.' ' . "timer_event". array($send_email_detail, $user_type, $item_name, $price);
            }
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
} else {
    $errmsg = " Error description: " . mysqli_error($conn);
}

if ($sql_del_watchlist != "") {
    $sql_del_watchlist = "Delete FROM listings_watched WHERE itemID IN (" . substr($delWatchlist, 0, strlen($delWatchlist)-2) . ")";
    $result_del_watchlist = mysqli_query($conn, $sql_del_watchlist) or die($conn->error);
}
//log file
if ($errmsg != "") {
    $file = dirname(__FILE__) . "/log.txt";
    $text = "Time: " . date("d/m/y H:i:s") . $errmsg ." \n";
    file_put_contents($file, $text, FILE_APPEND);
}
