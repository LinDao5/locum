<?php


namespace Gc\User\Job;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Job Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Job
 */
class Collection extends AbstractTable
{
    /**
     * List of Jobs
     *
     * @var array
     */
    protected $jobs;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_post';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getJobs(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getJobs($forceReload = false)
    {
        if (empty($this->jobs) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('job_id DESC');
                    }
                )
            );

            $jobs = array();
            foreach ($rows as $row) {
                $jobs[] = Model::fromArray((array) $row);
            }

            $this->jobs = $jobs;
        }

        return $this->jobs;
    }
/* Get Job Details By Job Id*/
    public function getJobDetailsByJobId($jobId='')
    {
        $query = "job_id = '$jobId'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $job = array();
        foreach ($rows as $row) {
            $job[] = Model::fromArray((array) $row);
        }        
        return $job;
    }
    /* Count how much job post by employer */
    public function getPostJobCount($uid,$startdate=null,$enddate=null)
    {

        if($enddate == null){ $enddate = date('Y-m-d');}
        if($startdate != null || $startdate != ''){
            $select = "SELECT * FROM job_post WHERE e_id = '$uid' AND job_create_date BETWEEN '$startdate' AND '$enddate'";
        }else{
            $query = "e_id = '$uid'";
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
    /* Count how much job post by employer is accepted  */
    public function getAcceptedJobCount($uid,$startdate=null,$enddate=null)
    {
        if($enddate == null){ $enddate = date('Y-m-d');}
        if($startdate != null || $startdate != ''){
            $select = "SELECT * FROM job_post WHERE e_id = '$uid' AND (job_status = 4 OR job_status = 5 ) AND job_create_date BETWEEN '$startdate' AND '$enddate'";
        }else{
            $query = "e_id = '$uid' AND (job_status = 4 OR job_status = 5 )";
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

    /* Get the id of all employer who post job for selected month */
    public function getMonthEmployer($startdate,$enddate)
    {
        $query = "job_create_date BETWEEN '$startdate' AND '$enddate' ";
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

    /* Count how much job post by employer between dates*/
    public function getMonthPostJobCount($uid,$startdate,$enddate)
    {
        $query = "e_id = '$uid' AND (job_create_date BETWEEN '$startdate' AND '$enddate')";
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
    
    /* Count how much job post by employer is accepted between dates */
    public function getMonthAcceptedJobCount($uid,$startdate,$enddate)
    {
        $query = "e_id = '$uid' AND (job_status = 4 OR job_status = 5 ) AND (job_create_date BETWEEN '$startdate' AND '$enddate')";
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
