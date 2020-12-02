<?php 
    include 'database.php';
            
    if($conn->connect_error) {
        die("Connection failed: ".$conn->connect_error);
    }

    //Create all database tables

    //Create user tables
    //Seller
    $seller = "CREATE TABLE IF NOT EXISTS seller (`email` varchar(30) NOT NULL, `fName` varchar(15) NOT NULL, `lName` varchar(15) NOT NULL, `password` varchar(40) NOT NULL, `phoneNo` varchar(15) DEFAULT NULL, 
    `street` varchar(20) DEFAULT NULL, `city` varchar(20) DEFAULT NULL, `postcode` varchar(10) DEFAULT NULL, PRIMARY KEY (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


    $result = mysqli_query($conn, $seller) or die('Error: Check create seller query');


    //Buyer
    $buyer = "CREATE TABLE IF NOT EXISTS buyer (`email` varchar(30) NOT NULL, `fName` varchar(15) NOT NULL, `lName` varchar(15) NOT NULL, `password` varchar(40) NOT NULL, `phoneNo` varchar(15) DEFAULT NULL, 
    `street` varchar(20) DEFAULT NULL, `city` varchar(20) DEFAULT NULL, `postcode` varchar(10) DEFAULT NULL, PRIMARY KEY (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $result = mysqli_query($conn, $buyer) or die('Error: Check create buyer query');


    //Category
    $category = "CREATE TABLE IF NOT EXISTS category (`categoryID` int(2) NOT NULL AUTO_INCREMENT, `categoryName` varchar(65) NOT NULL, PRIMARY KEY (`categoryID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $result = mysqli_query($conn, $category) or die('Error: Check create category query');


    //Items 
    $items = "CREATE TABLE IF NOT EXISTS items (
        `itemID` int(11) NOT NULL AUTO_INCREMENT,
        `itemName` varchar(50) NOT NULL,
        `sellerEmail` varchar(30) NOT NULL,
        `image1` longblob NOT NULL,
        `image2` longblob NOT NULL,
        `image3` longblob NOT NULL,
        `description` text NOT NULL,
        `categoryID` int(2) NOT NULL,
        `conditionOfUse` varchar(100) NOT NULL,
        `colour` VARCHAR(30) DEFAULT NULL,
        `gearbox` VARCHAR(30) NOT NULL,
        `fuelType` VARCHAR(30) NOT NULL,
        `initialReg` INT(10) NOT NULL,
        `mileage` INT(10) NOT NULL,
        `doors` INT(3) DEFAULT NULL,
        `seats` INT(3) DEFAULT NULL,
        `acceleration0to60mph` VARCHAR(5) DEFAULT NULL,
        `topSpeedMph` INT(4) DEFAULT NULL,
        `enginePowerBhp` INT(5) DEFAULT NULL,
        `startPrice` decimal(8,2) NOT NULL,
        `reservePrice` decimal(8,2) DEFAULT NULL,
        `startDate` datetime NOT NULL,
        `endDate` datetime NOT NULL, 
        PRIMARY KEY (`itemID`),
        KEY `fk_categoryID_items` (`categoryID`),
        KEY `fk_sellerEmail_items` (`sellerEmail`), 
        CONSTRAINT `fk_categoryID_items` FOREIGN KEY (`categoryID`) REFERENCES `category` (`categoryID`),
        CONSTRAINT `fk_sellerEmail_items` FOREIGN KEY (`sellerEmail`) REFERENCES `seller` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $result = mysqli_query($conn, $items) or die('Error: Check create items query');




    //Bids
    $bids = "CREATE TABLE IF NOT EXISTS bids (`bidID` int(11) NOT NULL AUTO_INCREMENT, `buyerEmail` varchar(30) NOT NULL, `itemID` int(11) NOT NULL, `bidPrice` decimal(8,2) NOT NULL, `bidDate` datetime NOT NULL, 
    PRIMARY KEY (`bidID`) USING BTREE, KEY `fk_buyerEmail_bids` (`buyerEmail`), KEY `fk_itemID_bids` (`itemID`), CONSTRAINT `fk_buyerEmail_bids` FOREIGN KEY (`buyerEmail`) REFERENCES `buyer` (`email`), CONSTRAINT `fk_itemID_bids` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $result = mysqli_query($conn, $bids) or die('Error: Check create bids query');



    //Listings watched
    $listings_watched = "CREATE TABLE IF NOT EXISTS listings_watched (`watchID` int(11) NOT NULL AUTO_INCREMENT, `buyerEmail` varchar(30) NOT NULL, `itemID` int(11) NOT NULL, 
    PRIMARY KEY (`watchID`), KEY `fk_buyerEmail_listings_watched` (`buyerEmail`), KEY `fk_itemID_listings_watched` (`itemID`), CONSTRAINT `fk_buyerEmail_listings_watched` FOREIGN KEY (`buyerEmail`) REFERENCES `buyer` (`email`), 
    CONSTRAINT `fk_itemID_listings_watched` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    $result = mysqli_query($conn, $listings_watched) or die('Error: Check create listings watched query');
      

    //Purchase 
    $purchase = "CREATE TABLE IF NOT EXISTS `purchase` (`purchaseID` int(11) NOT NULL AUTO_INCREMENT, `itemID` int(11) NOT NULL,
    PRIMARY KEY (`purchaseID`), KEY `fk_itemID_purchase` (`itemID`), 
    CONSTRAINT `fk_itemID_purchase` FOREIGN KEY (`itemID`) REFERENCES `items` (`itemID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $result = mysqli_query($conn, $purchase) or die('Error: Check create purchase query');



    //Test seller

    $test_data_seller = "INSERT INTO seller (`email`, `fName`, `lName`, `password`, `phoneNo`, `street`, `city`, `postcode`)
    VALUES ('boris.johnson@uk.com', 'Boris', 'Johnson', SHA('PASSWORD123'), '0123456789', '10 Downing St', 'London', 'SW1A 2AB');";
    $result = mysqli_query($conn, $test_data_seller) or die('Error: Check insert into seller query');


    //Test categories

    $test_data_categories = "INSERT INTO category (`categoryName`) VALUES ('SUV'), ('Coupe');";
    $result = mysqli_query($conn, $test_data_categories) or die('Error: Check insert into category query');




    //Get test data images

    $mercedes_img1 = addslashes(file_get_contents('images/MercedesGClass1.jpg'));
    $mercedes_img2 = addslashes(file_get_contents('images/MercedesGClass2.jpg'));
    $mercedes_img3 = addslashes(file_get_contents('images/MercedesGClass3.jpg'));

    $audirs5_img1 = addslashes(file_get_contents('images/AudiRS51.jpg'));
    $audirs5_img2 = addslashes(file_get_contents('images/AudiRS52.jpg'));
    $audirs5_img3 = addslashes(file_get_contents('images/AudiRS53.jpg'));


    $bmw7_img1 = addslashes(file_get_contents('images/BMW7er1.jpg'));
    $bmw7_img2 = addslashes(file_get_contents('images/BMW7er2.jpg'));
    $bmw7_img3 = addslashes(file_get_contents('images/BMW7er3.jpg'));
    




    //Test query items. 

    $test_data_item = "INSERT INTO items (`itemName`, `sellerEmail`, `image1`, `image2`, `image3`, `description`, `categoryID`, `conditionOfUse`, `colour`, `gearbox`, `fuelType`, `initialReg`, `doors`, `seats`, `mileage`, `acceleration0to60mph`, `topSpeedMph`, `enginePowerBhp`, `startPrice`, `reservePrice`, `startDate`, `endDate`) 
    VALUES  ('Audi RS5', 'boris.johnson@uk.com', '$audirs5_img1', '$audirs5_img2', '$audirs5_img3', 'Finest German engineering!!', 2, 'New', 'Green', 'Manual', 'Electric', 2019, 4, 4, 0, '0.8s', 320, 1010, 145000, 180000, now(), '2021-01-29 14:59:00'),
    ('Mercedes G-Class', 'boris.johnson@uk.com', '$mercedes_img1', '$mercedes_img2', '$mercedes_img3', 'Rustical Mercedes G Class for sale!', 1, 'New', 'Brown', 'Automatic', 'Petrol', 2017, 4, 5, 0, '1.6s', 285, 560, 65000, 90000, now(), '2021-01-29 14:59:00'),
    ('BMW7 Series', 'boris.johnson@uk.com', '$bmw7_img1', '$bmw7_img2', '$bmw7_img3', 'Exclusive BMW7 Series offered! Rarely used!', 2, 'Used', 'Silver', 'Automatic', 'Petrol', 2018, 4, 5, 2450, '1.2s', 300, 800, 90000, 115000, now(), '2021-03-29 14:59:00');";
    $result = mysqli_query($conn, $test_data_item) or die('Error: Check insert into items query');





 
?>
