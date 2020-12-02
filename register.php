<?php
//Include PHP files referenced here
include_once("header.php")?>

<script language="Javascript">
    function removeError(value) {
      $(".error").remove();
      $("#" + value).css('border-color', '#495057');
    }

    function fieldValid(){
      var flag = true;
      if(!IsEmpty()){
        flag = false;
      }
      if(!checkEmail()){
        flag = false;
      }
      if(!checkPasswordMatch()){
        flag = false;
      }
      return flag;
    }
    function IsEmpty() {
      var flag = true;
      $.each([ 'registerEmail', 'inputPassword', 'passwordConfirmation', 'firstName', 'lastName' ], function( index, value ) {
        removeError(value);
        if ($("#" + value).val().trim() == "") {
          $("#" + value).css('border-color', 'red');
          flag = false;
        }
      });
      return flag;
    }

    function checkEmail(){
      var flag = true;
      removeError('registerEmail');
      if ($("#registerEmail").val().trim() != "") {
        var regEx = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var validEmail = regEx.test($("#registerEmail").val().trim());
        if (!validEmail) {
          $("#registerEmail").after('<span class="error" style="color: #dc3545">This is is not a valid email address</span>');
          $("#registerEmail").css('border-color', 'red');
          flag = false;
        }
      }
      return flag;
    }

    function checkPasswordMatch(){
      var flag = true;
      removeError('passwordConfirmation');
      if ($("#inputPassword").val().trim() != $("#passwordConfirmation").val().trim()){
        $("#passwordConfirmation").after('<span class="error" style="color: #dc3545">Password doesn\'t match</span>');
        $("#passwordConfirmation").css('border-color', 'red');
        flag = false;
      }
      return flag;
    }
</script>

<div class="container">
<h2 class="my-3">Register new account</h2>

<!-- Create register form -->
<form name="registForm" method="POST" onsubmit="return fieldValid()" action="process_registration.php">
  <div class="form-group row">
    <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
	<div class="col-sm-10">
	  <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
      <label class="form-check-label" for="accountBuyer">Buyer</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="accountType" id="accountSeller" value="seller">
      <label class="form-check-label" for="accountSeller">Seller</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="accountType" id="accountBoth" value="both">
      <label class="form-check-label" for="accountBoth">Both</label>
    </div>
    <small id="accountTypeHelp" class="form-text-inline text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="registerEmail" class="col-sm-2 col-form-label text-right">Email</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="registerEmail" name="registerEmail" placeholder="Email" onblur="return checkEmail()">
      <small id="registerEmailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label text-right">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="inputPassword" name="inputPassword" placeholder="Password">
      <small id="inputPasswordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Enter password again" onblur="return checkPasswordMatch()">
      <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="firstName" class="col-sm-2 col-form-label text-right">First Name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name">
      <small id="firstNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="lastName" class="col-sm-2 col-form-label text-right">Last Name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name">
      <small id="lastNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <!-- Optional input info start  -->
  <div class="form-group row">
    <label for="phoneNo" class="col-sm-2 col-form-label text-right">Phone Number</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="Phone Number">
    </div>
  </div>
  <div class="form-group row">
    <label for="address" class="col-sm-2 col-form-label text-right">Address</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="street" name="street" placeholder="Street">
      <input type="text" class="form-control" id="city" name="city" placeholder="City">
      <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Postcode">
    </div>
  </div>
  <!--  Optinal input info end-->
  <div class="form-group row">
    <button type="submit" class="btn btn-primary form-control" >Register</button>
  </div>
</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>

<?php include_once("footer.php")?>
