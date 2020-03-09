<?php
	/**
	*  Paypal action controller develope by SURAJ WASNIK at FUDUGO
	*/
	namespace GcFrontend\Controller;
	use Gc\Mvc\Controller\Action;
	use Gc\User\Collection as UserCollection;
	use Zend\Db\Sql\Sql;
  	use Zend\Db\TableGateway\TableGateway;
	use Gc\Registry;
	
	class PaypalFormController extends Action
	{
		public function accountUpgradePaypalForm($uid,$pkgPrice,$pkgId,$adapter)
		{
			$paymentAmount = $package_final = $pkgPrice;
			$package_val = $this->getPkgInfo($pkgId,$adapter);
			$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
			$config = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
			$payment_mode = $config->get('payment_mode');
			$payment_mode = $config->get('payment_mode');
			$payment_email = $config->get('payment_email');
			$payment_api_user_name = $config->get('payment_api_user_name');
			$payment_api_pass = $config->get('payment_api_pass');
			$payment_api_key = $config->get('payment_api_key');	
			$token = $this->getToken();	
			$_SESSION['upgrade_pkg_token'] = $token;	
			if ($payment_mode == 'sandbox') {
				$action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			}else{
				$action = 'https://www.paypal.com/cgi-bin/webscr';
			}
			/* Get user info */
			
			$user = $this->getUserInfo($uid,$adapter);
			$fname = $user['firstname'];
			$lname = $user['lastname'];
			$email = $user['email'];
			$address = '';
			$city = '';
			$zip = '';
			$mobile = '';
			$form = '';
			$form .= '<form action="'.$action.'" method="post" id="paypal-form">';
			$form .= '<input type="hidden" name="cmd" value="_xclick">';
			$form .= '<INPUT TYPE="hidden" NAME="return" value="'.$serverUrl().'/paypal-process?token='.$token.'&p='.$paymentAmount.'&t=upgrade">';
			$form .= '<input type="hidden" name="cancel_return" value="'. $serverUrl().'/paypal-cancel">  ';
			$form .= '<INPUT TYPE="hidden" NAME="currency_code" value="GBP">';
			$form .= '<input type="hidden" name="business" value="'. $payment_email.'">';
			$form .= '<input type="hidden" name="item_name" value="'.$package_val.' Package">';
			$form .= '<input type="hidden" name="item_number" value="#locumkit'. $package_val.'">';
			$form .= '<input type="hidden" name="amount" value="'.$package_final.'">';
			$form .= '<input type="hidden" name="first_name" value="'.$fname.'">';
			$form .= '<input type="hidden" name="last_name" value="'.$lname .'">';
			$form .= '<input type="hidden" name="email" value="'.$email.'">';			
			$form .= '<h3><img src="'.$serverUrl().'/public/frontend/locumkit-template/img/loader.gif"> Please wait! redirecting to payapl..</h3>';
			$form .= '</form>';
			return $form;
		}
                public function accountChangePaypalForm($uid,$pkgPrice,$pkgId,$adapter)
		{
			$paymentAmount = $package_final = $pkgPrice;
			$package_val = $this->getPkgInfo($pkgId,$adapter);
			$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
			$config = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
			$payment_mode = $config->get('payment_mode');
			$payment_mode = $config->get('payment_mode');
			$payment_email = $config->get('payment_email');
			$payment_api_user_name = $config->get('payment_api_user_name');
			$payment_api_pass = $config->get('payment_api_pass');
			$payment_api_key = $config->get('payment_api_key');	
			$token = $this->getToken();	
			$_SESSION['change_pkg_token'] = $token;	
			if ($payment_mode == 'sandbox') {
				$action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			}else{
				$action = 'https://www.paypal.com/cgi-bin/webscr';
			}
			/* Get user info */
			
			$user = $this->getUserInfo($uid,$adapter);
			$fname = $user['firstname'];
			$lname = $user['lastname'];
			$email = $user['email'];
			$address = '';
			$city = '';
			$zip = '';
			$mobile = '';
			$form = '';
			$form .= '<form action="'.$action.'" method="post" id="paypal-form">';
			$form .= '<input type="hidden" name="cmd" value="_xclick">';
			$form .= '<INPUT TYPE="hidden" NAME="return" value="'.$serverUrl().'/paypal-process?token='.$token.'&p='.$paymentAmount.'&t=change">';
			$form .= '<input type="hidden" name="cancel_return" value="'. $serverUrl().'/paypal-cancel">  ';
			$form .= '<INPUT TYPE="hidden" NAME="currency_code" value="GBP">';
			$form .= '<input type="hidden" name="business" value="'. $payment_email.'">';
			$form .= '<input type="hidden" name="item_name" value="'.$package_val.' Package">';
			$form .= '<input type="hidden" name="item_number" value="#locumkit'. $package_val.'">';
			$form .= '<input type="hidden" name="amount" value="'.$package_final.'">';
			$form .= '<input type="hidden" name="first_name" value="'.$fname.'">';
			$form .= '<input type="hidden" name="last_name" value="'.$lname .'">';
			$form .= '<input type="hidden" name="email" value="'.$email.'">';			
			$form .= '<h3><img src="'.$serverUrl().'/public/frontend/locumkit-template/img/loader.gif"> Please wait! Redirecting to payapl..</h3>';
			$form .= '</form>';
			return $form;
		}
		public function getToken($length=32){
		    $token = "";
		    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		    $codeAlphabet.= "0123456789";
		    for($i=0;$i<$length;$i++){
		        $token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
		    }
		    return $token;
		}
		public function crypto_rand_secure($min, $max) {
	        $range = $max - $min;
	        if ($range < 0) return $min; // not so random...
	        $log = log($range, 2);
	        $bytes = (int) ($log / 8) + 1; // length in bytes
	        $bits = (int) $log + 1; // length in bits
	        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	        do {
	            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
	            $rnd = $rnd & $filter; // discard irrelevant bits
	        } while ($rnd >= $range);
	        return $min + $rnd;
		}
		
		/* Get specific user info from user id */
	    public function getUserInfo($uid,$adapter)
	    {
	    	$sqlUser = "SELECT * FROM user WHERE id = '$uid'";
	        $userDetails = $adapter->query($sqlUser, $adapter::QUERY_MODE_EXECUTE); //print_r($storeDetails);
        	$userRecord = (array)$userDetails->current();
        	return $userRecord;
	    }

	    /* Get specific package info from package id */
	    public function getPkgInfo($pid,$adapter)
	    {
	    	$sqlPkg = "SELECT name FROM user_acl_package WHERE id = '$pid'";
	        $pkgDetails = $adapter->query($sqlPkg, $adapter::QUERY_MODE_EXECUTE); //print_r($storeDetails);
        	$pkgRecord = (array)$pkgDetails->current();
        	foreach ($pkgRecord as $key => $value) {
        		$pkgName = $pkgRecord['name'];
        	}
        	return $pkgName;
	    }
	}