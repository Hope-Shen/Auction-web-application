<?php 
//Include PHP files referenced here
include_once("header.php")
?> 


<?php
// If user is not logged in or not a seller, they should not be able to
// use this page.
  if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'seller') {
    header('Location: browse.php');
  }
?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!--The user can use this form to enter item features while creating an auction-->
      <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
        <div class="form-group row">
          <label for="itemName" class="col-sm-2 col-form-label text-right">Item name</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="itemName" name="itemName" placeholder="e.g. Porsche Cayenne">
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>
        <div class="form-group row">
            <label for="image" class="col-sm-2 col-form-label text-right"> Image </label>
            <div class="col-sm-10">
              <input type="file" name="image[]" id="image" multiple/>
              <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Please upload 3 images. Only allowed format is jpeg. Size per image must not exceed 2 MB.</small>
            </div>
          </div>
        <div class="form-group row">
          <label for="description" class="col-sm-2 col-form-label text-right">Description</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            <small id="descriptionHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="Colour" class="col-sm-2 col-form-label text-right">Colour</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="Colour" name="Colour" placeholder="e.g. navy blue">
          </div>
        </div>
        <div class="form-group row">
          <label for="Category" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" id="Category" name ="Category">
                <option>Coupe</option>
                <option>Convertible</option>
                <option>Estate</option>
                <option>Hatchback</option>
                <option>MPV</option>
                <option>Pickup</opton>
                <option>SUV</option>
                <option>Saloon</option>
                <option>Other</option>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Category of this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="GearBox" class="col-sm-2 col-form-label text-right">Gearbox</label>
          <div class="col-sm-10">
            <select class="form-control" id="GearBox" name ="Gearbox">
                <option>Automatic</option>
                <option>Manual</option>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Gearbox.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="FuelType" class="col-sm-2 col-form-label text-right">Fuel Type</label>
          <div class="col-sm-10">
            <select class="form-control" id="FuelType" name ="FuelType">
                <option>Petrol</option>
                <option>Diesel</option>
                <option>Electric</option>
                <option>Hybrid - Diesel/Electric</option>
                <option>Hybrid - Petro/Electric</option>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Fuel type.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="CondOfUse" class="col-sm-2 col-form-label text-right">Condition</label>
          <div class="col-sm-10">
            <select class="form-control" id="Condition" name="Condition">
              <option>New</option>
              <option>Used</option>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Condition of use.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="initialReg" class="col-sm-2 col-form-label text-right">Initial registration</label>
          <div class="col-sm-10">
            <select class="form-control" id="initialReg" name="initialReg">
              <!-- This for loop enables users to select the initial registration year of the item from a range of years. (1960-now) -->
              <?php $y=(int)date("Y"); ?>
              <option value="<?php echo $y; ?>" selected="true"><?php echo $y; ?></option>
              <?php $y--;
              for(; $y>"1960"; $y--){ ?>
              <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
              <?php } ?>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial registration year.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="numberDoors" class="col-sm-2 col-form-label text-right">Doors</label>
          <div class="col-sm-10">
	        <div class="input-group">
            <input type="number" min="1" max="10" class="form-control" id="numberDoors" name="numberDoors">
          </div>
          <small id="categoryHelp" class="form-text text-muted">Number of doors.</small>
        </div>
        </div>
        <div class="form-group row">
          <label for="numberSeats" class="col-sm-2 col-form-label text-right">Seats</label>
          <div class="col-sm-10">
	        <div class="input-group">
            <input type="number" min="1" max="10" class="form-control" id="numberSeats" name="numberSeats">
          </div>
          <small id="categoryHelp" class="form-text text-muted">Number of seats.</small>
        </div>
        </div>
        <div class="form-group row">
          <label for="mileage" class="col-sm-2 col-form-label text-right">Mileage</label>
          <div class="col-sm-10">
          <div class="input-group">
            <input type="number" min="0" class="form-control" id="mileage" name="mileage">
            <div class="input-group-append">
              <span class="input-group-text">miles</span>
            </div>
          </div>
            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Current mileage.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="acceleration" class="col-sm-2 col-form-label text-right">Accelaration <break> (0-60mph) </break> </label>
          <div class="col-sm-10">
          <div class="input-group">
            <input type="number" min="0" max="200" step=".01" class="form-control" id="accelaration" name="accelaration">
            <div class="input-group-append">
              <span class="input-group-text">sec</span>
            </div>
          </div>
            <small id="startBidHelp" class="form-text text-muted">Accelaration from 0 to 60 mph (in seconds).</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="speed" class="col-sm-2 col-form-label text-right">Top speed</label>
          <div class="col-sm-10">
          <div class="input-group">
            <input type="number" min="0" max="400" class="form-control" id="topspeed" name="topspeed">
            <div class="input-group-append">
              <span class="input-group-text">mph</span>
            </div>
          </div>
            <small id="startBidHelp" class="form-text text-muted"> Maximum speed.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="eningepwr" class="col-sm-2 col-form-label text-right">Engine Power</label>
          <div class="col-sm-10">
          <div class="input-group">
            <input type="number" min="0" max="1500" class="form-control" id="enginepwr" name="enginepwr">
            <div class="input-group-append">
              <span class="input-group-text">bhp</span>
            </div>
          </div>
            <small id="startBidHelp" class="form-text text-muted">Brake horsepower of item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
	        <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" min="0" max="999999" class="form-control" id="auctionStartPrice" name="auctionStartPrice">
            </div>
            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" min="0" max="999999" class="form-control" id="auctionReservePrice" name="auctionReservePrice">
            </div>
            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" id="auctionEndDate" name="auctionEndDate">
            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Day for the auction to end.</small>
          </div>
        </div>
        </div>
        <button type="submit" name="submit" class="btn btn-primary form-control">Create Auction</button>
      </form>
    </div>
  </div>
</div>

</div>


<?php include_once("footer.php")?>