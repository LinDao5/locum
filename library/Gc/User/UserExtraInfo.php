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

class UserExtraInfo extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'user_extra_info';

	public function getUserExtraInfo($uid)
	{
		$query = "uid = '$uid'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $user = array();
        foreach ($rows as $row) {
            $user[] = Model::fromArray((array) $row);
        }
        return $user;
	}
}