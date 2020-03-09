<?php
/* * *********************************************************
  DoDirectPayment.php

  Submits a credit card transaction to PayPal using a
  DoDirectPayment request.

 * ********************************************************* */
session_start();
require_once('../PPBootStrap.php');

if (base64_decode($_POST['id']) == 1) {
    $p_price = 21.10;
    $p_currency = 'USD';
    $p_name = 'Tas Ransel Orange bag';
} else if (base64_decode($_POST['id']) == 2) {
    $p_price = 15.20;
    $p_currency = 'USD';
    $p_name = 'Tas Ransel Jeans bag';
} else if (base64_decode($_POST['id']) == 3) {
    $p_price = 11.10;
    $p_currency = 'USD';
    $p_name = 'Tas Ransel grey bag';
}
/**
 * Get required parameters from the web form for the request
 */
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];

/*
 * shipping adress
 */
$address = new AddressType();
$address->Name = "$firstName $lastName";
$address->Street1 = $_POST['address1'];
$address->Street2 = $_POST['address2'];
$address->CityName = $_POST['city'];
$address->StateOrProvince = $_POST['state'];
$address->PostalCode = $_POST['zip'];
$address->Country = $_POST['country'];
$address->Phone = $_POST['phone'];


$orderTotal = new BasicAmountType();
$orderTotal->currencyID = $p_currency;
$orderTotal->value = $p_price;

$paymentDetails = new PaymentDetailsType();
$paymentDetails->ShipToAddress = $address;

$itemDetails = new PaymentDetailsItemType();
$itemDetails->Name = $p_name;

$itemDetails->Amount = $orderTotal;
/*
 * Item quantity. This field is required when you pass a value for ItemCategory.
 */
$itemDetails->Quantity = '1';
/*
 * Indicates whether an item is digital or physical. 
 */
$itemDetails->ItemCategory = 'Physical';

$paymentDetails->PaymentDetailsItem[0] = $itemDetails;
$paymentDetails->OrderTotal = $orderTotal;
if (isset($_REQUEST['notifyURL'])) {
    $paymentDetails->NotifyURL = $_REQUEST['notifyURL'];
}

$personName = new PersonNameType();
$personName->FirstName = $firstName;
$personName->LastName = $lastName;

//information about the payer
$payer = new PayerInfoType();
$payer->PayerName = $personName;
$payer->Address = $address;
$payer->PayerCountry = $_POST['country'];

$cardDetails = new CreditCardDetailsType();
$cardDetails->CreditCardNumber = $_POST['creditCardNumber'];
/*
 *
  Type of credit card. For UK, only Maestro, MasterCard, Discover, and
  Visa are allowable. For Canada, only MasterCard and Visa are
  allowable and Interac debit cards are not supported. It is one of the
  following values:

 * Visa
 * MasterCard
 * Discover
 * Amex
 * Solo
 * Switch
 * Maestro: See note.
  `Note:
  If the credit card type is Maestro, you must set currencyId to GBP.
  In addition, you must specify either StartMonth and StartYear or
  IssueNumber.`
 */
$cardDetails->CreditCardType = $_POST['creditCardType'];
$cardDetails->ExpMonth = $_POST['expDateMonth'];
$cardDetails->ExpYear = $_POST['expDateYear'];
$cardDetails->CVV2 = $_POST['cvv2Number'];
$cardDetails->CardOwner = $payer;

$ddReqDetails = new DoDirectPaymentRequestDetailsType();
$ddReqDetails->CreditCard = $cardDetails;
$ddReqDetails->PaymentDetails = $paymentDetails;
$ddReqDetails->PaymentAction = $_REQUEST['paymentType'];

$doDirectPaymentReq = new DoDirectPaymentReq();
$doDirectPaymentReq->DoDirectPaymentRequest = new DoDirectPaymentRequestType($ddReqDetails);

/*
 * 		 ## Creating service wrapper object
  Creating service wrapper object to make API call and loading
  Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$paypalService = new PayPalAPIInterfaceServiceService(Configuration::getAcctAndConfig());
try {
    /* wrap API method calls on the service object with a try catch */
    $doDirectPaymentResponse = $paypalService->DoDirectPayment($doDirectPaymentReq);
} catch (Exception $ex) {
    include_once("../Error.php");
    exit;
}
if (isset($doDirectPaymentResponse)) {
    ?>
    <html>
        <head>
            <title>paypal payments via credit card</title>
            <link rel="stylesheet" type="text/css" href="css/style.css">
            <meta name="robots" content="noindex, nofollow">
            <script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', 'UA-43981329-1']);
                _gaq.push(['_trackPageview']);
                (function() {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                })();
            </script>
        </head>
        <body>

            <div id="main">
                <div class="success_main_heading"> 
                    <center><h1>PayPal Payments via Credit Card</h1></center>
                </div>
                <div id="return">
                    <h2>Payment Status</h2>
                    <hr/>
                    <?php
                    //Rechecking the product price and currency details
                    if ($doDirectPaymentResponse->Ack == 'Success') {
                        echo "<h3 id='success'>Payment Successful</h3>";
                        echo "<P>Amount -" . $doDirectPaymentResponse->Amount->value . " (" . $doDirectPaymentResponse->Amount->currencyID . ")</P>";
                        echo "<P>Transaction ID -" . $doDirectPaymentResponse->TransactionID . "</P>";
                        echo "<div class='back_btn'><a  href='index.php' id= 'btn'><< Back </a></div>";
                    } else {
                        echo "<h3 id='fail'>Payment - Failed</h3>";
                        echo '<p>Error msg - This transaction cannot be processed. <br>Please enter a valid credit card number and type.</p>';
                        echo "<div class='back_btn'><a  href='index.php' id= 'btn'><< Back </a></div>";
                    }
                    ?>
                </div>
                <!-- Right side div -->
                <div class="fr"id="formget">
                    <a href=http://www.formget.com/app><img style="margin-left: 12%;" src="images/formget.jpg" alt="Online Form Builder"/></a>
                </div>
            </div>
        </body>
    </html>
    <?php
}
?>
