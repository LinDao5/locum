<?php
/**
* Model to update the user package on admin action, dvelope by SURAJ 
* WASNIK at FUDUGO
*/
namespace Gc\User;

use Gc\Db\AbstractTable;
use Gc\Mail;
use Gc\Registry;
use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;
use Zend\Validator\EmailAddress;

class UserPackage extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'user_package_details';

	public function insertPackageInfo($uid,$pid)
	{
		$currentDate = date("Y-m-d");
		$expiryDate = date('Y-m-d', strtotime('+1 month', strtotime($currentDate)));
		$arraySave = array(
				'user_id' => $uid,
				'package_id' => $pid,
				'package_active_date' => $currentDate,
				'package_expire_date' => $expiryDate,
				'package_status' => 1,
			);
		$this->insert($arraySave);
	}

	public function allowOneMonthFree($uid)
	{
		$newExpiryDate = $this->getExpiryDate($uid);
		if ($newExpiryDate) {
			$arraySave = array(
					'package_expire_date' => $newExpiryDate,
					'package_status' => 1
				);
			$this->update($arraySave, array('user_id' => $uid ));
		}
	}

	public function getExpiryDate($uid)
	{  
		$whereArray = "user_id = $uid AND package_status = 1";       
        $select = $this->select($whereArray,
            function (Select $select, $whereArray) {
                $select->where($whereArray);                
            }
        );
        $rows  = $this->fetchAll($select);
        if (!empty($rows)) {
        	foreach ($rows as $key => $value) {
	        	$expiryDate = $value['package_expire_date'];
	        }
	        $newExpiryDate = date('Y-m-d', strtotime('+1 month', strtotime($expiryDate)));
	        
        }else{
        	$whereArray = "user_id = $uid";       
	        $select = $this->select($whereArray,
	            function (Select $select, $whereArray) {
	                $select->where($whereArray);                
	            }
	        );
	        $rows  = $this->fetchAll($select);
	        foreach ($rows as $key => $value) {
	        	$expiryDate = $value['package_active_date'];
	        }
	        $newExpiryDate = date('Y-m-d', strtotime('+1 month', strtotime($expiryDate)));
        }
        return $newExpiryDate;
	}
}