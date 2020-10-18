<?php ob_start();
session_start();

include("db.php");
include("functions.php");
// if ($query)
// {

// $_SESSION['now'] = date('i:s');

// $now =date('Y-m-d H:i:s');
// echo $now;
// $futureDate = $now+(60*1);

// $formatDate = date("Y-m-d H:i:s", $futureDate);

// if($_SESSION['now'] > $formatDate)
// {
//     $failureJson='{"error":"We are encountering some issue. Please try after some time."}';
//      print_r($failureJson);       

// }
// }
// if($con){
//     echo "it works, is connected!";
// }
?>