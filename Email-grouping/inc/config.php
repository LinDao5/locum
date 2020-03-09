<?php 
/* Start Mailing Config */
/*
              _ _                     ______
     /\      | | |                   |___  /
    /  \   __| | |__   __ _ _ __ ___    / / 
   / /\ \ / _` | '_ \ / _` | '_ ` _ \  / /  
  / ____ \ (_| | | | | (_| | | | | | |/ /__ 
 /_/    \_\__,_|_| |_|\__,_|_| |_| |_/_____|
                                            
                                            
*/

/* Connection Information */

$serveraddress = "localhost";
$dbuser = "umairc65_mailing";
$dbname = "umairc65_mailing";
$dbpassword = "Basildon10";
$title= "LocumKit"; 
$site_url = "http:/ec2-18-163-113-25.ap-east-1.compute.amazonaws.com/Email-grouping";
$hostname = 'localhost.com';
$hostmail = 'bookings@localhost.com';
$hostmailPass = 'Optometry10';
$SenderName = 'Locumkit';
$conn = mysqli_connect($serveraddress, $dbuser, $dbpassword , $dbname) or die(mysqli_connect_error());
?>