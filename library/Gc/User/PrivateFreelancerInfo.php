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

class PrivateFreelancerInfo extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'private_user';

	public function getPrivateUserInfo($start_date = null, $end_date = null)
	{
        $end_date = ($end_date == null && $end_date == '') ? date('Y-m-d') : $end_date;
        if ($start_date != null && $end_date != null) {
              $select = "SELECT * FROM private_user WHERE status = 0 AND created_at  BETWEEN '$start_date' AND '$end_date' ORDER BY p_uid DESC";
        }else{
              $select = "SELECT * FROM private_user WHERE status = 0   ORDER BY p_uid DESC";
        }
        $rows = $this->fetchAll($select);
        
        $pUsers = array();
        foreach ($rows as $row) {
            $pUsers[] = Model::fromArray((array) $row);
        }
        return $pUsers;
    }
}