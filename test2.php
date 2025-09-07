<?php
// header('Access-Control-Allow-Origin: *');

// header('Access-Control-Allow-Methods: GET, POST');

// header("Access-Control-Allow-Headers: X-Requested-With");


if(isset($_GET["amount"]) && isset($_GET["reference"]))
{
   echo "Transaction amount is: ". $_GET['amount']. "<br />";
   echo "Refernce no. is: ". $_GET['reference'];

   exit();
}

?>