<?php
	/**
	*  Package upgrade controller develope by SURAJ WASNIK at FUDUGO
	*/
	namespace GcFrontend\Controller;
	use Gc\Mvc\Controller\Action;
	use Gc\User\Collection as UserCollection;
	use Zend\Db\Sql\Sql;
  	use Zend\Db\TableGateway\TableGateway;
	use Gc\Registry;
	use GcFrontend\Controller\JobmailController as MailController;
	
	class PackageUpgradeController extends Action
	{
		
		/* Insert Payment info with zero (pending) status */
		public function insertPaymentInfo($uid,$pkgId,$pkgPrice,$token,$adapter){
			$currentDate = date("Y-m-d H:i:s");
			$sqlPaymentInfo = "INSERT INTO user_payment_info (uid,payment_type,payment_data,price,payment_status,created_date) VALUES ('$uid','paypal','$token','$pkgPrice','0','$currentDate')";
	        $paymentInfo = $adapter->query($sqlPaymentInfo, $adapter::QUERY_MODE_EXECUTE);
	        $_SESSION['last_payment_insert_id'] = $paymentInfo->getGeneratedValue();
		}

		/* Update payment status to 1 after payment done */
		public function updatePaymentInfo($pid,$adapter)
		{
			$currentDate = date("Y-m-d H:i:s");
			$sqlPaymentInfo = "UPDATE user_payment_info SET payment_status = 1 WHERE pid = '$pid'";
	        $paymentInfo = $adapter->query($sqlPaymentInfo, $adapter::QUERY_MODE_EXECUTE);
		}

		
		public function getExpiryDate($uid,$pid,$adapter){
			$sqlPkg = "SELECT package_expire_date FROM user_package_details WHERE user_id = '$uid'  ORDER BY pid DESC";
	        $pkgDetails = $adapter->query($sqlPkg, $adapter::QUERY_MODE_EXECUTE); //print_r($storeDetails);
        	return $pkgRecord = (array)$pkgDetails->current();
		}
		
		/* Insert package details */
		public function insertPkgDetails($uid,$pkgId,$activeDate,$expireDate,$adapter)
		{
			$sqlPkgInfo = "INSERT INTO user_package_details (user_id,package_id,package_active_date,package_expire_date,package_status) VALUES ('$uid','$pkgId','$activeDate','$expireDate','0')";
	        $pkgInfo = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
	        $_SESSION['last_pkg_details_info_id'] = $pkgInfo->getGeneratedValue();
		}
		/* Update payment status to 1 after payment done */
		public function updatePackageInfo($pid,$adapter)
		{
			$currentDate = date("Y-m-d");
			$sqlPkgExpired = "SELECT package_expire_date FROM user_package_details WHERE pid = '$pid' ORDER BY pid DESC";
	        $pkgExpired = $adapter->query($sqlPkgExpired, $adapter::QUERY_MODE_EXECUTE); //print_r($storeDetails);
        	$pkgExpiredRecord = (array)$pkgExpired->current();
        	if ($currentDate <= $pkgExpiredRecord['package_expire_date']) {
        		echo "I am here";
        		$sqlPkgInfo = "UPDATE user_package_details SET package_status = 2 WHERE pid = '$pid'";
	        	$paymentInfo = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
        	}else{
        		$sqlPkgInfo = "UPDATE user_package_details SET package_status = 1 WHERE pid = '$pid' AND package_active_date < $currentDate ";
	        	$paymentInfo = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
        	}
			
		}
                /* Update payment status to 1 after payment done for package change */
		public function updateChangePackageInfo($pid,$adapter)
		{
			$currentDate = date("Y-m-d");
			$sqlPkgExpired = "SELECT package_expire_date,package_id,user_id FROM user_package_details WHERE pid = '$pid' ORDER BY pid DESC";
	        $pkgExpired = $adapter->query($sqlPkgExpired, $adapter::QUERY_MODE_EXECUTE); //print_r($storeDetails);
        	$pkgExpiredRecord = (array)$pkgExpired->current();
			$package_id = $pkgExpiredRecord['package_id'];
			$user_id = $pkgExpiredRecord['user_id'];
        	if ($currentDate < $pkgExpiredRecord['package_expire_date']) {
        		$sqlPkgInfo = "UPDATE user_package_details SET package_status = 1 WHERE pid = '$pid'";
	        	$paymentInfo = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
				
			// update user table for new package id
				$sqlUserInfo = "UPDATE user SET user_acl_package_id = '$package_id' WHERE id = '$user_id'";
	        	$userInfo = $adapter->query($sqlUserInfo, $adapter::QUERY_MODE_EXECUTE);
     			$_SESSION['user_package_id'] = $package_id ; // from 20/06/17
			//print_r($userInfo); die();
        	}
			
		}
		/* Upadete package on active date via cron */
		public function updatePackageDetailsStatusOnCron($currentDate,$adapter){
			$mailController = new MailController();

			$getExpiryDate = date('Y-m-d', strtotime('-1 day', strtotime($currentDate)));
			$sqlPkgInfo = "SELECT pid,user_id,package_id FROM user_package_details WHERE package_status = 2 AND package_active_date = '$currentDate'";
	        $pkgExpired = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE); 
	        $pkgExpiredRecord = $pkgExpired->toArray();
	        if (!empty($pkgExpiredRecord)) {
	        	foreach ($pkgExpiredRecord as $key => $value) {
					$sqlPkgInfoExpired = "UPDATE user_package_details SET package_status = 3 WHERE user_id = '".$value['user_id']."' AND package_expire_date = '$getExpiryDate' AND package_id = '".$value['package_id']."'";
		        	$paymentInfoExpired = $adapter->query($sqlPkgInfoExpired, $adapter::QUERY_MODE_EXECUTE);
					$sqlPkgInfoActive = "UPDATE user_package_details SET package_status = 1 WHERE pid = '".$value['pid']."'";
		        	$paymentInfoActive = $adapter->query($sqlPkgInfoActive, $adapter::QUERY_MODE_EXECUTE);

				}
	        }

	        $packageExpiredDate = date('Y-m-d', strtotime('-365 day', strtotime($currentDate)));
	        $sqlPkgInfo = "SELECT pid,user_id,package_id FROM user_package_details WHERE package_status != 3 AND package_expire_date = '$getExpiryDate'";
	        $pkgExpired = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE); 
	        $pkgExpiredRecord = $pkgExpired->toArray();

	       

	        if (!empty($pkgExpiredRecord)) {
	        	foreach ($pkgExpiredRecord as $key => $value) {
					$sqlPkgInfoExpired = "UPDATE user_package_details SET package_status = 3 WHERE pid = '".$value['pid']."'";
		        	$paymentInfoExpired = $adapter->query($sqlPkgInfoExpired, $adapter::QUERY_MODE_EXECUTE);					
		        	/* Send profile suspention mail */
		        	$mailController->sendMembershipExpired($value['user_id'],$value['package_id'],$adapter);
		        	/*Set user as expired user in user table*/
		        	$sqlUserExpired = "UPDATE user SET active = 4 WHERE id = '".$value['user_id']."'";
		        	$userExpired = $adapter->query($sqlUserExpired, $adapter::QUERY_MODE_EXECUTE);	
				}
	        }
		}
	}