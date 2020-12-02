<?php
  $servername = "localhost";
  $username = "admin";
  $password = "p@ssw0rd!";
  $dbname = "auction";
  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  // Check connection
  if (mysqli_connect_errno()) {
      echo 'Failed to connect to the MySQL server: '. mysqli_connect_error();
  }
