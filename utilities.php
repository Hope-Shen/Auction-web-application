<?php

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval)
{
    if ($interval->days == 0 && $interval->h == 0) {
        // Less than one hour remaining: print mins + seconds:
        $time_remaining = $interval->format('%im %Ss');
    } elseif ($interval->days == 0) {
        // Less than one day remaining: print hrs + mins:
        $time_remaining = $interval->format('%hh %im');
    } else {
        // At least one day remaining: print days + hrs:
        $time_remaining = $interval->format('%ad %hh');
    }

    return $time_remaining;
}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time)
{
    // Truncate long descriptions
    if (strlen($desc) > 250) {
        $desc_shortened = substr($desc, 0, 250) . '...';
    } else {
        $desc_shortened = $desc;
    }

    // Fix language of bid vs. bids
    if ($num_bids == 1) {
        $bid = ' bid';
    } else {
        $bid = ' bids';
    }

    // Calculate time to auction end
    $now = new DateTime();
    $end_time = new DateTime($end_time);
    if ($now > $end_time) {
        $time_remaining = 'This auction has ended';
    } else {
        // Get interval:
        $time_to_end = date_diff($now, $end_time);
        $time_remaining = display_time_remaining($time_to_end) . ' remaining';
    }

    // Print HTML
    echo('
        <li class="list-group-item d-flex justify-content-between">
            <div class="p-2 mr-5">
                <h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a>
                </h5>' . $desc_shortened . '
            </div>
            <div class="text-center text-nowrap">
                <span style="font-size: 1.5em;">£' . number_format($price, 2) . '</span><br/>' .
                $num_bids . $bid . '<br/>' .
                $time_remaining . '
            </div>
        </li>'
    );
}



// Print listing for sold items: Adds label
function print_listing_li_sold($item_id, $title, $desc, $price, $num_bids, $end_time)
{
    // Truncate long descriptions
    if (strlen($desc) > 250) {
        $desc_shortened = substr($desc, 0, 250) . '...';
    } else {
        $desc_shortened = $desc;
    }

    // Fix language of bid vs. bids
    if ($num_bids == 1) {
        $bid = ' bid';
    } else {
        $bid = ' bids';
    }

    // Calculate time to auction end
    $now = new DateTime();
    $end_time = new DateTime($end_time);
    if ($now > $end_time) {
        $time_remaining = 'This auction has ended';
    } else {
        // Get interval:
        $time_to_end = date_diff($now, $end_time);
        $time_remaining = display_time_remaining($time_to_end) . ' remaining';
    }

    // Print HTML
    echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="ribbon"><span>SOLD</span></div>
    <div class="p-2 mr-5">
      <h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a>
      </h5>' . $desc_shortened . '
    </div>
    <div class="text-center text-nowrap">
      <span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' .
      $num_bids . $bid . '<br/>' .
      $time_remaining . '
    </div>
  </li>'
  );
}

// Print listing for unsold items: Adds label
function print_listing_li_unsold($item_id, $title, $desc, $price, $num_bids, $end_time)
{
    // Truncate long descriptions
    if (strlen($desc) > 250) {
        $desc_shortened = substr($desc, 0, 250) . '...';
    } else {
        $desc_shortened = $desc;
    }

    // Fix language of bid vs. bids
    if ($num_bids == 1) {
        $bid = ' bid';
    } else {
        $bid = ' bids';
    }

    // Calculate time to auction end
    $now = new DateTime();
    $end_time = new DateTime($end_time);
    if ($now > $end_time) {
        $time_remaining = 'This auction has ended';
    } else {
        // Get interval:
        $time_to_end = date_diff($now, $end_time);
        $time_remaining = display_time_remaining($time_to_end) . ' remaining';
    }

    // Print HTML
    echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="ribbon"><span>UNSOLD</span></div>
    <div class="p-2 mr-5">
      <h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a>
      </h5>' . $desc_shortened . '
    </div>
    <div class="text-center text-nowrap">
      <span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' .
      $num_bids . $bid . '<br/>' .
      $time_remaining . '
    </div>
  </li>'
  );
}

function isLoginUserValid($account_type, $user_email, $password, $conn)
{
    $user=null;
    $sql = "";
    if ($account_type == "buyer") {
        $sql = "SELECT email as buyerID, CONCAT(fName,' ', lName) as username, email FROM buyer WHERE email = ? AND password = SHA(?)";
    } else {
        $sql = "SELECT email as sellerID, CONCAT(fName,' ', lName) as username, email FROM seller WHERE email = ? AND password = SHA(?)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user['name'] = $row["username"];
        $user['ID'] = $row[$account_type. 'ID'];
        $user['email'] = $row["email"];
        $user['account_type'] = $account_type;

        if (session_status() == PHP_SESSION_NONE) {
            // Set session variables and redirect.
            session_start();
        } elseif (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['name'];
            $_SESSION['userID'] = $user['ID'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['email'] = $user['email'];
        }
    }

    $stmt->close();
    return $user;
}
