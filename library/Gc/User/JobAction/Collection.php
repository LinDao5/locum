<?php

namespace Gc\User\JobAction;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of JobAction Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobAction
 */
class Collection extends AbstractTable
{
    /**
     * List of JobActions
     *
     * @var array
     */
    protected $jobActions;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_action';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getJobActions(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getJobActions($forceReload = false)
    {
        if (empty($this->jobActions) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('a_id');
                    }
                )
            );

            $jobActions = array();
            foreach ($rows as $row) {
                $jobActions[] = Model::fromArray((array) $row);
            }

            $this->jobActions = $jobActions;
        }

        return $this->jobActions;
    }


 /* Count how much job applied by freelancer */
    public function getAppliedJobCount($uid, $startdate = null, $enddate = null)
    {
        if($enddate == null || $enddate == ''){ $enddate = date('Y-m-d'); }
        if($startdate != null && $startdate != ''){
            $select = "SELECT * FROM job_action WHERE f_id = '$uid' AND (action = 2 OR action = 3 OR action = 4 ) AND create_date BETWEEN '$startdate' AND '$enddate'";
        }else{
            $query = "f_id = '$uid' AND (action = 2 OR action = 3 OR action = 4 )";
            $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        }
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }
    /* Count how much job applied by freelancer is accepted  */
    public function getAcceptedJobCount($uid, $startdate = null, $enddate = null)
    {
        if($enddate == null || $enddate == ''){ $enddate = date('Y-m-d'); }
        if($startdate != null && $startdate != ''){
            $select = "SELECT * FROM job_action WHERE f_id = '$uid' AND ( action = 3 OR action = 4 ) AND create_date BETWEEN '$startdate' AND '$enddate'";
        }else{
            $query = "f_id = '$uid' AND ( action = 3 OR action = 4 )";
            $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        }
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }

    /* Count how much job Freeze by freelancer */
    public function getFreezeJobCount($uid, $startdate = null, $enddate = null)
    {
        if($enddate == null || $enddate == ''){ $enddate = date('Y-m-d'); }
        if($startdate != null && $startdate != ''){
            $select = "SELECT * FROM job_action WHERE f_id = '$uid' AND f_notification = 1 AND create_date BETWEEN '$startdate' AND '$enddate'";
        }else{
            $query = "f_id = '$uid' AND f_notification = 1";
            $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        }
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }
    /* Count how much job freeze by freelancer is accepted  */
    public function getAcceptedFreezeJobCount($uid, $startdate = null, $enddate = null)
    {
        if($enddate == null || $enddate == ''){ $enddate = date('Y-m-d'); }
        if($startdate != null && $startdate != ''){
            $select = "SELECT * FROM job_action WHERE f_id = '$uid' AND (action = 3 OR action = 4) AND f_notification = 1 AND create_date BETWEEN '$startdate' AND '$enddate'";
        }else{
            $query = "f_id = '$uid' AND (action = 3 OR action = 4) AND f_notification = 1";
            $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        }
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }

    /* Get the id of all freelancer who apply job for selected month */
    public function getMonthFreelancer($startdate,$enddate)
    {
        $query = "create_date BETWEEN '$startdate' AND '$enddate' ";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = $jobs;
        return $noOfJobs;
    }

    /* Count how much job applied by freelancer between dates*/
    public function getAppliedJobCountByMonth($uid,$startdate,$enddate)
    {
        $query = "SELECT * FROM job_action WHERE f_id = '$uid' AND (action = 2 OR action = 3 OR action = 4 ) AND (create_date BETWEEN '$startdate' AND '$enddate')";        
        $rows = $this->fetchAll($query);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }
    
    /* Count how much job accepted by employer for which freelancer is applied in selected month */
    public function getAcceptedJobCountByMonth($uid,$startdate,$enddate)
    {
        $query = "f_id = '$uid' AND (action = 3 OR action = 4 ) AND (create_date BETWEEN '$startdate' AND '$enddate')";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }
    
    /* Count how much job freeze by freelancer between dates*/
    public function getCountFreezeJobsByMonth($uid,$startdate,$enddate)
    {
        $query = "f_id = '$uid' AND f_notification = 1 AND (create_date BETWEEN '$startdate' AND '$enddate')";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }
    
    /* Count how much job accepted by freelancer from Freeze list in selected month */
    public function getcountAcceptedFreezeJobsByMonth($uid,$startdate,$enddate)
    {
        $query = "f_id = '$uid' AND (action = 3 OR action = 4 ) AND f_notification = 1 AND (create_date BETWEEN '$startdate' AND '$enddate')";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $jobs = array();
        foreach ($rows as $row) {
            $jobs[] = Model::fromArray((array) $row);
        }

        $noOfJobs = count($jobs);
        return $noOfJobs;
    }
    
}

