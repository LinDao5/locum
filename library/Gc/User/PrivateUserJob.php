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

class PrivateUserJob extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'private_user_job_action';

	public function getPrivateUserRequestSendCount($uid,$startdate = null,$enddate  = null)
	{
        if($enddate == null){ $enddate = date('Y-m-d');}
        if($startdate != null || $startdate != ''){
            $select = "SELECT * FROM private_user_job_action WHERE emp_id = '$uid' AND (created_at BETWEEN '$startdate' AND '$enddate')";
        }else{
		$query = "emp_id = '$uid'";
        	$select = $this->select($query,
                	function (Select $select, $query) {
                    		$select->where($query);
                	}
            	);
        }
        $rows = $this->fetchAll($select);
        
        $pUser = array();
        foreach ($rows as $row) {
            $pUser[] = Model::fromArray((array) $row);
        }
        
        $noOfPUser = count($pUser);
        return $noOfPUser;
	}

    /* Get private user request send count by month */
    public function getMonthPrivateUserRequestSendCount($uid, $startdate = null,$enddate  = null)
    {
        if($enddate == null){ $enddate = date('Y-m-d');}
        if($startdate != null || $startdate != ''){
            $select = "SELECT * FROM private_user_job_action WHERE emp_id = '$uid' AND (created_at BETWEEN '$startdate' AND '$enddate')";
        }else{
            $query = "emp_id = '$uid'";
            $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        }

        $rows = $this->fetchAll($select);
        
        $pUser = array();
        foreach ($rows as $row) {
            $pUser[] = Model::fromArray((array) $row);
        }
        
        $noOfPUser = count($pUser);
        return $noOfPUser;
    }
}