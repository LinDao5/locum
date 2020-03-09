<?php 

$contactname=$_POST["contactname"];
$email=$_POST["email"];
  $intRef=$_POST["intRef"];
  $date=$_POST["date"];
  $rate=$_POST["rate"];
  $store=$_POST["store"];
  $open=$_POST["open"];
  $close=$_POST["close"];
  $break=$_POST["break"];
  $testTime=$_POST["testTime"];
  @$speReq=$_POST["speReq"];
 if($intRef == "" || $intRef == NULL){echo "<script>alert('Please Enter Internal Reference');</script>";}
elseif($contactname== "" || $contactname== NULL){echo "<script>alert('Please Enter Contact Name');</script>";}
elseif($email == "" || $email== NULL){echo "<script>alert('Please Enter Your Email');</script>";}
elseif($date == "" || $date == NULL){echo "<script>alert('Please Enter Date');</script>";}
 elseif($date == "" || $date == NULL){echo "<script>alert('Please Enter Date');</script>";}
 elseif($rate == "" || $rate == NULL){echo "<script>alert('Please Enter Rate');</script>";}
 elseif($store == "" || $store == NULL){echo "<script>alert('Please Enter Store Name And Address');</script>";}
 elseif($open == "" || $open == NULL){echo "<script>alert('Please Select Opening Timing');</script>";}
 elseif($close == "" || $close == NULL){echo "<script>alert('Please Select Closing Timing');</script>";}
 elseif($break == "" || $break == NULL){echo "<script>alert('Please Select Lunch Break');</script>";}
 elseif($testTime == "" || $testTime == NULL){echo "<script>alert('Please Enter Testing Time');</script>";}
 elseif($speReq == "" || $speReq == NULL){echo "<script>alert('Please type none if you do not have any special requests');</script>";}
 else{
 //$receiver="musman5264@gmail.com";
 $receiver="bookings@localhost.com";
 $subject="Sightcare booking request";

$message = "
<html>
<head>
<title>Locum Contact Details</title>
</head>
<body>
<table width='50%' border='0' align='center' cellpadding='0' cellspacing='0'>
  <tr>
    <td colspan='2' align='center' valign='top'><img src='http://demo.jmobiles.site/test/img/logo.png' width='150'></td>
  </tr>
  <tr>
    <td colspan='2' align='center' valign='center' style='font-size: 30px'>$store</td>
  </tr>
  <tr>
    <td width='50%' align='right' style='font-size: 20px'>&nbsp;</td>
    <td align='left' style='font-size: 30px'>&nbsp;</td>
  </tr>
 
 <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Contact Name:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$contactname."</td>
  </tr>
 <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Email:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$email."</td>
  </tr>
  <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Internal Reference:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$intRef."</td>
  </tr>
  <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Date</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$date."</td>
  </tr>

 <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Rate:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>£".$rate."</td>
  </tr>
  <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Store Name And Address:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$store."</td>
  </tr>
  <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Store Timing</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>
			Opening Time: <b>".$open."</b><hr>
			Closing Time: <b>".$close."</b><hr>
			Lunch Break: <b>".$break."</b>
	</td>
  </tr>
  <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Testing Time:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$testTime."</td>
  </tr>
  <tr>
    <td align='right' valign='top' style='border-top:1px solid #dfdfdf; border-bottom:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Special Request:</td>
    <td align='left' valign='top' style='border-top:1px solid #dfdfdf; border-bottom:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$speReq."</td>
  </tr>
  <tr>
    <td colspan='2' align='center' valign='center' style='font-size: 10px'><center>E-Mail Powered By J-Solutions | All Rights Reserved ".date('Y')." | <a href='http://usman.jmobiles.pk'>http://usman.jmobiles.pk</a> | +92 334 5266444</center></td>
  </tr>
</table>

</body>
</html>
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <no-reply@localhost.com>';

   //if(mail($receiver,$subject,$message,$headers))  
   if(mail($receiver,$subject,$message,$headers))  
   {
      header("Location: http:/ec2-18-163-113-25.ap-east-1.compute.amazonaws.com/locumform/thanks.html");
      echo "The message has been sent!";
      echo '<script>swal("Request Sent!","Thank You for submitting your request! We will contact you soon.","success");</script>';
   }
   else
   {
       print_r(error_get_last());
      echo "The message could not been sent!";
      
   }
}

?>
