<?php
/**
* Model to update the Block user on employer action, dvelope by SURAJ 
* WASNIK at FUDUGO
*/
namespace Gc\User;

use Gc\Db\AbstractTable;
use Gc\Mail;
use Gc\Registry;
use Gc\User\Collection as UserCollection;
use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;
use Zend\Validator\EmailAddress;
use Zend\Db\TableGateway\TableGateway;

class BlockFreelancer extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'block_user';

	public function getBlockFreelancerByEmpId($uid)
	{
		$query = "emp_id = '$uid'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $bUser = array();
        foreach ($rows as $row) {
            $bUser[] = Model::fromArray((array) $row);
        }
        
        return $bUser;
	}

    /* update block Freelancer */
    public function updateBlockFreelancer($uid)
    {
        $aclTable = new TableGateway('block_user', $this->getAdapter());
        $aclTable->delete(array('frelan_id' => $uid));
        return true;
    }
}