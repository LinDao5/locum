<?php

namespace Gc\User;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of User Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user';

    /**
     * Initiliaze User collection
     *
     * @return void
     */
    public function init()
    {
        $this->setUsers();
    }

    /**
     * Get users
     *
     * @return array Gc\User\Model
     */
    public function getUsers()
    {
        $orderQuery = 'id DESC';
        if (isset($_SESSION['setUserNameOrder']) && $_SESSION['setUserNameOrder'] == 1) {
            $orderQuery = 'login DESC';
        }elseif(isset($_SESSION['setUserNameOrder']) && $_SESSION['setUserNameOrder'] == 2){
            $orderQuery = 'login ASC';
        }
        if (isset($_SESSION['setUserFNameOrder']) && $_SESSION['setUserFNameOrder'] == 1) {
            $orderQuery = 'firstname DESC';
        }elseif(isset($_SESSION['setUserFNameOrder']) && $_SESSION['setUserFNameOrder'] == 2){
            $orderQuery = 'firstname ASC';
        }
        if (isset($_SESSION['setUserLNameOrder']) && $_SESSION['setUserLNameOrder'] == 1) {
            $orderQuery = 'lastname DESC';
        }elseif(isset($_SESSION['setUserLNameOrder']) && $_SESSION['setUserLNameOrder'] == 2){
            $orderQuery = 'lastname ASC';
        }
        if (isset($_SESSION['setUserEmailOrder']) && $_SESSION['setUserEmailOrder'] == 1) {
            $orderQuery = 'email DESC';
        }elseif(isset($_SESSION['setUserEmailOrder']) && $_SESSION['setUserEmailOrder'] == 2){
            $orderQuery = 'email ASC';
        }
        if (empty($this->users) or $forceReload === true) {
            $selectQuery = 'SELECT * FROM user LEFT JOIN user_extra_info On user.id = user_extra_info.uid order by '.$orderQuery;
            $rows = $this->fetchAll($selectQuery);           
            $users = array();
            foreach ($rows as $row) {
                $users[] = Model::fromArray((array) $row);
            }
            $this->users = $users;
        }

        return $this->users;
        //return $this->getData('users');
    }
    public function getBlockUsers($start_date = null, $end_date= null)
    {
if($end_date== null || $end_date== ''){$end_date= date('Y-m-d');}
        if($start_date != null && $end_date != null){
            $select = "SELECT * FROM user WHERE active = '2' AND updated_at BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC";
        }else{
            $select = $this->select(
                function (Select $select) {
                    $select->where("active = '2'");
                    $select->order('id DESC');
                }
            );
        }
        

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }
        return $users;
    }

    public function getActiveUsers()
    {

       $select = $this->select(
            function (Select $select) {
                $select->where("active < '3'");
                $select->order('id DESC');
            }
        );

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }
        return $users;
    }
    public function getFreelancerUsers($start_date = null, $end_date= null)
    {
        if($start_date != null && $end_date != null){
            $select = "SELECT * FROM user WHERE user_acl_role_id = '2' AND user_acl_package_id > 0 AND created_at BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC";
        }else{
            $select = $this->select(
                function (Select $select) {
                    $select->where("user_acl_role_id = '2' AND user_acl_package_id > 0"); 
                    $select->order('id DESC');              
                }
            );
        }

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }
        return $users;
    }

    public function getFreelancerUsersByPkgId($pkgId = null)
    {

        if ($pkgId != null) {
            $select = "SELECT * FROM user WHERE user_acl_role_id = '2' AND user_acl_package_id = '$pkgId' ORDER BY id DESC";
        }else{
            $select = $this->select(
                function (Select $select) {
                    $select->where("user_acl_role_id = '2' AND user_acl_package_id > 0 ");        
                    $select->order('id DESC');         
                }
            ); 
        }
        

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }
        return $users;
    }

    /**
     * Set users collection
     *
     * @return void
     */
    protected function setUsers()
    {
        $select = $this->select(
            function (Select $select) {
                $select->order('lastname');
            }
        );

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }

        $this->setData('users', $users);
    }

    public function getUserById($id)
    {
       $query = "id = '$id'";
       $select = $this->select($query, 
            function (Select $select,  $query) {
                $select->where($query);
                $select->order('id DESC');
            }
        );
        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }

        return $users;
    }

   public function getEmployerUsers($start_date = null, $end_date= null)
    {
if($end_date == null || $end_date == ''){ $end_date = date('Y-m-d'); }
        if ($start_date != null && $end_date != null) {
            $select = "SELECT * FROM user WHERE user_acl_role_id = '3' AND created_at BETWEEN '$start_date' AND '$end_date' ORDER BY id DESC";
        }else{
            $select = $this->select(
                function (Select $select) {
                    $select->where("user_acl_role_id = '3'"); 
                    $select->order('id DESC');              
                }
            );
        }
        

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }
        return $users;
    }

   /* Get user record by package ID */
    public function getFreelancerOfCurrentPkg($pkgId)
    {
        $query = "user_acl_role_id = '2' AND user_acl_package_id = '$pkgId'";
        $select = $this->select($query,
            function (Select $select,$query) {
                $select->where($query);  
                $select->order('id DESC');               
            }
        );
        $rows  = $this->fetchAll($select);
        $user = array();
        foreach ($rows as $row) {
            $user[] = Model::fromArray((array) $row);
        }
        return count($user);
    }

    /* Get user record by package ID  in between dates*/
    public function getFreelancerOfCurrentPkgOfSelectedDate($pkgId,$startdate,$enddate)
    {
        $query = "user_acl_role_id = '2' AND user_acl_package_id = '$pkgId' AND (created_at BETWEEN '$startdate' AND '$enddate')";
        $select = $this->select($query,
            function (Select $select,$query) {
                $select->where($query);                
            }
        );
        $rows  = $this->fetchAll($select);
        $user = array();
        foreach ($rows as $row) {
            $user[] = Model::fromArray((array) $row);
        }
        return count($user);
    }
    public function getActiveNewUsers($start_date = null,  $end_date = null)
    {
if($end_date == null || $end_date == ''){ $end_date = date('Y-m-d'); }
        if ($start_date != null && $end_date != null) {
            $select = "SELECT * FROM user WHERE active < '3' AND ( user_acl_role_id = '2' OR user_acl_role_id = '3' ) AND created_at BETWEEN '$start_date' AND '$end_date' ORDER BY  id DESC";            
        }else{
            $select = $this->select(
                function (Select $select) {
                    $select->where("active < '3' AND ( user_acl_role_id = '2' OR user_acl_role_id = '3' ) ");
                    $select->order('id DESC');
                }
            );    
        }
        

        $rows  = $this->fetchAll($select);
        $users = array();
        foreach ($rows as $row) {
            $users[] = Model::fromArray((array) $row);
        }
        return $users;
    }
    public function getFinanceUsers($userid = null )
    {
        $con = '';
        if($userid != null && is_numeric($userid)){
            $con = " and id = '$userid' "  ;
        }
        $orderQuery = 'id DESC';
        if (isset($_SESSION['setUserNameOrder']) && $_SESSION['setUserNameOrder'] == 1) {
            $orderQuery = 'login DESC';
        }elseif(isset($_SESSION['setUserNameOrder']) && $_SESSION['setUserNameOrder'] == 2){
            $orderQuery = 'login ASC';
        }
        if (isset($_SESSION['setUserFNameOrder']) && $_SESSION['setUserFNameOrder'] == 1) {
            $orderQuery = 'firstname DESC';
        }elseif(isset($_SESSION['setUserFNameOrder']) && $_SESSION['setUserFNameOrder'] == 2){
            $orderQuery = 'firstname ASC';
        }
        if (isset($_SESSION['setUserLNameOrder']) && $_SESSION['setUserLNameOrder'] == 1) {
            $orderQuery = 'lastname DESC';
        }elseif(isset($_SESSION['setUserLNameOrder']) && $_SESSION['setUserLNameOrder'] == 2){
            $orderQuery = 'lastname ASC';
        }
        if (isset($_SESSION['setUserEmailOrder']) && $_SESSION['setUserEmailOrder'] == 1) {
            $orderQuery = 'email DESC';
        }elseif(isset($_SESSION['setUserEmailOrder']) && $_SESSION['setUserEmailOrder'] == 2){
            $orderQuery = 'email ASC';
        }



        if (empty($this->users) or $forceReload === true) {

            /*$selectQuery = $this->select($orderQuery,
                function (Select $select, $orderQuery) {
                    $select->order('login DESC');
                }
            );*/
            $selectQuery = 'SELECT * FROM user where user_acl_role_id = 2 and active = 1 '.$con.'  order by '.$orderQuery;
            $rows = $this->fetchAll($selectQuery);

            //
            $rows = $this->fetchAll($selectQuery);


            $users = array();
            foreach ($rows as $row) {
                $users[] = Model::fromArray((array) $row);
            }

            $this->users = $users;
        }

        return $this->users;
        //return $this->getData('users');
    }


}
