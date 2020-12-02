<?php
require "PHPMailer/Exception.php";
require "PHPMailer/PHPMailer.php";
require "PHPMailer/SMTP.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_email($username, $recipient_email, $type, $content)
{
    $name = "Allaboutcars"; //sender’s name
    $sender_email = "auction.allaboutcars@gmail.com"; //sender’s email address
    $sender_password = "Allaboutcars5"; //sender’s email password

    $mail = new PHPMailer();
    // Server settings
    // $mail ->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
    $mail -> isSMTP(); // Send using SMTP
    $mail -> Host = "smtp.gmail.com"; // Set the SMTP server to send through
    $mail -> SMTPAuth  = true; // Enable SMTP authentication
    $mail -> Username = $sender_email; // SMTP username
    $mail -> Password = $sender_password; // SMTP password
    $mail -> SMTPSecure = "ssl"; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail -> Port = 465; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    // Recipients
    $mail -> SetFrom($sender_email);
    $mail -> AddAddress($recipient_email, $username); // Add a recipient (Name is optional)
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // email content
    $email_subject = ""; //subject
    $email_body = ""; //mail body
    if ($type == "signup") {
        $email_subject = "Thanks for signing up on AllAboutCars!";
        $email_body = "<h1>Hi ". $username .", thanks for signing up on <b>AllAboutCars</b></h1>
                    <p>Your AllAboutCars account has been created.<br/>
                    Please use your email: " . $recipient_email . " to login.</p>";
    } elseif ($type == "timer_event") {
        if ($content[0] == "buyer") {
            $email_subject = "Congratulations! You have won the auction on AllAboutCars!";
            $email_body = "<h1>Hi " . $username . ", you have won " . $content[1] . ".</h1>
                    <p>Your bid was<b> " . $content[2] . "</b><br/>";
        } else {
            $email_subject = "Congratulations! Your item has been sold on AllAboutCars!";
            $email_body = "<h1>Hi ". $username .", your item " . $content[1] ." has been sold.</h1>
                  <p>The winning bid was <b>" . $content[2] . "</b><br/>";
        }
    } elseif ($type == "addWatchlist") {
        $email_subject = "You added an item to your watchlist!";
        $email_body = "<h1>Hi ". $username .", the item " . $content[1] ." has been added to your watchlist.</h1>";
    } elseif ($type == "remWatchlist") {
        $email_subject = "You removed an item from your watchlist!";
        $email_body = "<h1>Hi ". $username .", the item " . $content[1] ." has been removed from your watchlist.</h1>";
    } elseif ($type == "placeBid") {
        $email_subject = "Your bid has been placed successfully!";
        $email_body = "<h1>Hi ". $username .", your bid for GBP ". $content[2] ." on the item ". $content[1] ." has been placed.</h1>";
    } elseif ($type == "outbid") {
        $email_subject = "You have been outbid!";
        $email_body = "<h1>Hi ". $username .", your bid for GBP ". $content[1] ." on the item ". $content[2] ." has been outbid.
                       The new highest bid is ". $content[3] .".</h1>";
    } elseif ($type == "watchlistBid") {
        $email_subject = "A bid has been placed on an item you are currently watching!";
        $email_body = "<h1>Hi ". $username .", a bid for GBP ". $content[1] ." has been just placed on the item ". $content[2] .", that you are watching.</h1>";
    } elseif ($type = "itemClosed") {
        $email_subject = "Your watchlist item has been closed!";
        $email_body = "<h1>Hi ". $username .", the item " . $content . " has been closed and it has been removed from your watchlist.</h1>";
    } else {
        $email_subject = "Test subject";
        $email_body = "<h1>Test body h1</h1>
                  <p>Test body p</p>";
    }
    // Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz'); // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name

    // Content
    $mail -> isHTML(true); // Set email format to HTML
    $mail -> Subject = $email_subject;
    $mail -> Body = $email_body;

    if ($mail -> send()) {
        return "Email has been sent!";
    } else {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
