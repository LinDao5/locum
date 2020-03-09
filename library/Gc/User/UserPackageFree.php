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

class UserPackageFree extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'user_package_details';

	public function allowOneMonthFree($uid)
	{
		$newExpiryDate = $this->getExpiryDate($uid);
		if ($newExpiryDate) {
			$arraySave = array(
					'package_expire_date' => $newExpiryDate
				);
			$this->update($arraySave, array('user_id' => $uid ));
		}

	}

	public function getExpiryDate($uid)
	{
		echo $uid;  
		$whereArray = "user_id = $uid AND package_status = 1";       
        $select = $this->select($whereArray,
            function (Select $select, $whereArray) {
                $select->where($whereArray);                
            }
        );
        $rows  = $this->fetchAll($select);
        foreach ($rows as $key => $value) {
        	$expiryDate = $value['package_expire_date'];
        }
        $newExpiryDate = date('Y-m-d', strtotime('+1 month', strtotime($expiryDate)));
        return $newExpiryDate;
	}
}