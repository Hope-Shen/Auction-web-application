<?php
//Prevent form resubmission upon hitting back button.
header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");
//Include PHP files referenced here 
include_once("header.php")
?>

<div class="container my-5">

<?php

// This function takes the form data and adds the new auction to the database.

/*  Connect to MySQL database (perhaps by requiring a file that
    already does this). */

    include 'database.php';
            
    if($conn->connect_error) {
        die("Connection failed: ".$conn->connect_error);
    }


//Check user inputs and throw custom error messages for selected features
function isDataValid()
{
    //List into an array all input features that are required and/or need to be validated in terms of input length
    $content_check = ['itemName' => ['postName' => 'itemName', 'UI name' => 'item name', 'length' => 50],
                'description' => ['postName' =>'description', 'UI name' => 'description', 'length' => 1500],
                'Colour' => ['postName' =>'Colour', 'UI name' => 'colour', 'length' => 30],
                'auctionStartPrice' => ['postName' =>'auctionStartPrice', 'UI name' => 'starting price', 'length' => 8],
                'auctionReservePrice' => ['postName' =>'auctionReservePrice', 'UI name' => 'reserve price', 'length' => 8],
                'auctionEndDate' => ['postName' => 'auctionEndDate', 'UI name' => 'end date', 'length' => 30],
                'mileage' => ['postName' => 'mileage', 'UI name' => 'mileage', 'length' => 10]];

    //Initialise $errorMessage variable
    $errorMessage = null;

    //Set the timezone and determine today's date. This is needed to check whether the user inputs an end date, which is in the past.
    $timezone = date_default_timezone_set('Europe/London');
    $today = date('Y-m-d h:i');
  

    //Check each element of the $content_check array for errors
    foreach ($content_check as $x => $val) {
        // Check, if all required inputs have been entered. Do not include columns that are optional.
        if (!in_array($x, ['description', 'Colour', 'auctionReservePrice'])) {
            if (!isset($_POST[$val['postName']]) or trim($_POST[$val['postName']]) == '') {
                $errorMessage .= 'You must enter <b>' . $val['UI name'] . '</b>. <br/>';

            }// If all required inputs have been input correctly, check if the end date is set correct as well.
            elseif(isset($_POST['auctionEndDate']) && $x == 'auctionEndDate' && $today > $_POST['auctionEndDate']) {
                $errorMessage .= 'Your <b>' .$val['UI name'] . '</b> lies in the past.<br>';
            }
            
        }
 
        // If the auction reserve price is set, check whether it is higher than the starting price.
        if ($x == 'auctionReservePrice' && isset($_POST['auctionReservePrice']) && $_POST['auctionReservePrice'] != "" && $_POST[$val['postName']] < $_POST['auctionStartPrice']) {
            $errorMessage .= 'Your <b>' . $val['UI name'] . '</b> must be higher than your starting price!<br/>';
        }


        // Validate the input length for the user inputs. This is important, as the maximum number of characters must not exceed the length of the attribute defined in the database.
        if (isset($_POST[$val['postName']]) && strlen($_POST[$val['postName']]) > $val['length']) {
            $errorMessage .= '<b>' . $val['UI name'] . ': </b> cannot be greater than '. $val['length'] .' characters <br/>';
            
        }
        
        
        
    }

    // Check, if all the 3 images were uploaded correctly.
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        //List all image files into an array. This will be needed to count the total number of images.
        foreach($_FILES['image']['name'] as $i => $value) {
            if (file_exists($_FILES['image']['tmp_name'][$i]) || is_uploaded_file($_FILES['image']['tmp_name'][$i])) {
                $img_array = $_FILES['image']['tmp_name'];
            }
        }   //Check, if no image was uploaded at all.

            if (empty($img_array)){
                $errorMessage .= 'Please upload 3 <b>images</b> before creating the auction.';

        }   //If an image was uploaded, check, if less than 3 images were uploaded.
            elseif (count($img_array) < 3 and count($img_array) > 0) {
                $errorMessage .= 'Please upload more images, <b> you need 3</b> to create an auction.';

        }  
            //Check, if images are all in appropriate size. They should be smaller than 2 MB.
            //If at least 3 images were uploaded, check, if they are in the correct format. They must be JPEG files.
            elseif(isset($_FILES['image']) and ($_FILES['image']['type'][0] !== 'image/jpeg' || $_FILES['image']['type'][1] !== 'image/jpeg' || $_FILES['image']['type'][2] !== 'image/jpeg')){
                $errorMessage .= 'At least one of the uploaded images is <b>invalid</b>! Please make sure you upload <b>JPEG</b> files only and do not exceed <b> 2 MB </b> per file!';
    

        }   //Check, if more than 3 images have been uploaded.
            elseif (count($img_array) > 3) {
                $errorMessage .= 'You have uploaded too many images. Please upload <b> 3 only</b>.';
        }
           
    }


    //If there are error messages, advise the user to check his inputs. Allow him to go to the previous page and correct his inputs.
    if ($errorMessage !== null) {
        echo <<<EOM
          <div class="container">
            <h1>Sorry, the information is incorrect. Pleace check again.</h1>
            <p><b>Error:</b> </br/>$errorMessage</p>
            <button type='submit' class='btn btn-primary' value='Back' onClick='history.back()');>Back to previous page</button>
          </div>
    EOM;
        return false;
    } else {
        return true;
    }
}


            
/*Get user inputs into the database.*/
function saveToDatabase($conn) 

{ 
    //Resize the images, if they are too large.
    function resize_image($file, $max_resolution) {
        for ($i = 0; $i <= 2; $i++) {

            if(file_exists($file)) {

                //Retrieve the original image, its original width and height.
                $original_image = imagecreatefromjpeg($file);
                $original_width = imagesx($original_image);
                $original_height = imagesy($original_image);

                //Determine new image properties to optimally resize the old image.
                $ratio = $max_resolution / $original_width;
                $new_width = $max_resolution;
                $new_height = $original_height * $ratio;

                //If the new height is larger than the maximum resolution, use this approach to resize the old image. 
                if($new_height > $max_resolution) {
                    $ratio = $max_resolution / $original_height;
                    $new_height = $max_resolution;
                    $new_width = $original_width * $ratio;
                }

                if($original_image) {

                    //Return an image identifier, specifiy the size of the new image by taking the new width and height as parameters. 
                    $new_image = imagecreatetruecolor($new_width, $new_height);

                    //Copy the original image into the new image by taking as parameters the old as well as new values of width and height. 
                    //The four 0 parameters are the x and y coordinates of the source and destination point of the copy.
                    imagecopyresampled($new_image, $original_image, 0,0,0,0, $new_width, $new_height, $original_width, $original_height);

                    //Create an optimized jpeg of quality '90' using the new image as resource and save it under $file.
                    imagejpeg($new_image, $file, 90);

                }
            }
        }
    }
    //Move the uploaded files to a specific directory inside the www folder. This makes sure that the uploaded images can actually be used, even if 
    //they are initially stored outside the www directory. 
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        //Specify image directory.
        $image_dir = 'images/';
        foreach($_FILES['image']['name'] as $i => $value) {
        }
            //Only call the functions if the images have been uploaded and are of jpeg format.
            if(isset($_FILES['image']) && $_FILES['image']['type'][$i] == 'image/jpeg') {
                for ($i = 0; $i <= 2; $i++) {
                    //Move all 3 images to the image directory.
                    move_uploaded_file(($_FILES['image']['tmp_name'][$i]), $image_dir.$_FILES['image']['name'][$i]);
                    $file = $_FILES['image']['name'];
                    //Assign the file names along with their locations to the respective elements of the $file array.
                    $file[0] = $image_dir.$file[0];
                    $file[1] = $image_dir.$file[1];
                    $file[2] = $image_dir.$file[2];

                    //Call the resize_image function for all 3 images and determine their maximum resolution.
                    resize_image($file[0], "1000");
                    resize_image($file[1], "1000");
                    resize_image($file[2], "1000");
                
            }
      }

    }
    
    

  

    //Convert all images into a blob structure and assign them to variables. This will make sure the images can be inserted into the database.
    $image_1 = addslashes(file_get_contents($file[0]));
    $image_2 = addslashes(file_get_contents($file[1]));
    $image_3 = addslashes(file_get_contents($file[2]));

 
    //Populate the 'category' table with the user's input selection of the category name. If the user selects a category which does not exist yet in the database
    //this query will insert the new category name into the category table and determine the category ID with auto increment.

    if (isset($_POST['Category'])) {
    $category = $_POST['Category'];
    $catsql = "INSERT INTO category (`categoryName`) SELECT * FROM (SELECT '$category') as tmp WHERE NOT EXISTS (SELECT `categoryName` FROM category WHERE `categoryName` = '$category');";
    $catresults = mysqli_query($conn, $catsql);


    //Select the category ID for the category input by the user. 
    $categoryID = "SELECT `categoryID` FROM category WHERE `categoryName` = '$category';";
    $result = mysqli_query($conn, $categoryID);
    
    //Retrieve the category ID from the previous select statement. This is necessary, as the category ID will be inserted into the items table later.
    $row = mysqli_fetch_assoc($result);
    $cid = $row['categoryID'];
    $categoryID = $cid;
    }


    //Get all item attributes from the user's input. Trim all string attributes.
    //Assign NULL to all optional integer values, if user decides to skip them. We must do this, as the SQL syntax is unable to insert empty spaces for integers. We must use null instead.
    if (isset($_POST['itemName'])) {
        $itemName = trim($_POST['itemName']);

    }
    
    if (isset($_SESSION['userID'])) {
        $sellerEmail = trim($_SESSION['userID']);
    }
    
    if (isset($_POST['description'])) {
        $description = trim($_POST['description']);
    }
    
    if (isset($_POST['Colour'])) {
        $colour = trim($_POST['Colour']);
    }
    
    if (isset($_POST['Gearbox'])) {
        $gearbox = trim($_POST['Gearbox']);
    }
    
    if (isset($_POST['FuelType'])) {
        $fueltype = trim($_POST['FuelType']);
    }

    if (isset($_POST['Condition'])) {
        $conditionOfUse = trim($_POST['Condition']);
    }
  
    if (isset($_POST['initialReg'])) {
        $initialreg = $_POST['initialReg'];
    }
    
    if (isset($_POST['numberDoors'])) {
        $doors = $_POST['numberDoors'];
        $doors = !empty($doors)? "$doors": "NULL";
    }

    if (isset($_POST['numberSeats'])) {
        $seats = $_POST['numberSeats'];
        $seats = !empty($seats)? "$seats": "NULL";
    }
   
    if (isset($_POST['mileage'])) {
        $mileage = $_POST['mileage'];
    }

    if (isset($_POST['accelaration'])) {
        $accelaration = $_POST['accelaration'];
        $accelaration = !empty($accelaration)? trim($accelaration."s"): NULL;
    }

    if (isset($_POST['topspeed'])) {
        $topspeed = $_POST['topspeed'];
        $topspeed = !empty($topspeed)? "$topspeed": "NULL";
    }
    
    if (isset($_POST['enginepwr'])) {
        $enginepwr = $_POST['enginepwr'];
        $enginepwr = !empty($enginepwr)? "$enginepwr": "NULL";
    }
   
    if (isset($_POST['auctionStartPrice'])) {
        $startPrice = $_POST['auctionStartPrice'];
    }

    if (isset($_POST['auctionEndDate'])) {
        $endDate = $_POST['auctionEndDate'];
    }
    
    if (isset($_POST['auctionReservePrice'])) {
        $reservePrice = $_POST['auctionReservePrice'];
        $reservePrice = !empty($reservePrice)? "$reservePrice": "$startPrice";
    }
  


    


 

/* If everything looks good, make the appropriate call to insert data into the database. */
    //The SQL function now() is used to determine the starting date of an auction.

    
    $sql = "INSERT INTO items (`itemName`, `sellerEmail`, `image1`, `image2`, `image3`, `description`, `categoryID`, `conditionOfUse`, `colour`, `gearbox`, `fuelType`, `initialReg`, `doors`, `seats`, `mileage`, `acceleration0to60mph`, `topSpeedMph`, `enginePowerBhp`, `startPrice`, `reservePrice`, `startDate`, `endDate`) 
    VALUES ('$itemName', '$sellerEmail', '$image_1', '$image_2', '$image_3', '$description', $categoryID, '$conditionOfUse', '$colour', '$gearbox', '$fueltype', $initialreg, $doors, $seats, $mileage, '$accelaration', $topspeed, $enginepwr, $startPrice, $reservePrice, now(), '$endDate');";
    $result = mysqli_query($conn, $sql) or die('Error making saveToDatabase query');
    
  
    
}

  

/*Call the isDataValid function, to validate the parsed data. If it is valid, call the saveToDatabase function to import the new data into the database.*/
if (isDataValid()) {
    if(isset($_POST['submit'])) {
    saveToDatabase($conn);
    //Used as reference to redirect user to a specific URL.
    $itemID = $conn -> insert_id;
    // If all is successful, let user know. The link will redirect him to the listing of the newly created item, by referencing its item ID.
    echo('<div class="text-center">Auction successfully created! <a href="listing.php?'.'item_id='.$itemID.'">View your new listing.</a></div>');
    } 
} 
    


?>

</div>


<?php include_once("footer.php")?>