<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Job
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Job;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Job Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Job
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_post';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Job
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $arraySave = array(
            'job_status' => $this->getJobChangeStatus(),
        );
        /*print_r($arraySave);
        echo $this->getJobId();
        exit();*/
        try {
            $jobId = $this->getJobId();
            if (empty($jobId)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('job_id' => $this->getJobId()));
            }
            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getJobId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete Job
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getJobId();
        if (!empty($id)) {
            parent::delete(array('job_id' => $id));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\Job\Model
     */
    public static function fromArray(array $array)
    {
        $jobTable = new Model();
        $jobTable->setData($array);
        $jobTable->setOrigData();

        return $jobTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userqusId User job id
     *
     * @return \Gc\User\job\Model
     */
    public static function fromId($userJobId)
    {
        $jobTable = new Model();
        $row       = $jobTable->fetchRow($jobTable->select(array('job_id' => (int) $userJobId)));
        $jobTable->events()->trigger(__CLASS__, 'before.load', $jobTable);
        if (!empty($row)) {
            $jobTable->setData((array) $row);
            $jobTable->setOrigData();
            $jobTable->events()->trigger(__CLASS__, 'after.load', $jobTable);
            return $jobTable;
        } else {
            $jobTable->events()->trigger(__CLASS__, 'after.load.failed', $jobTable);
            return false;
        }
    }

    public static function serach($jobId,$userId,$catId)
    {
        $jobTable = new Model();
        if ($jobId && $userId && $catId) {
            $searchArray = array(
                'job_id' => $jobId,
                'e_id' => $userId,
                'cat_id' => $catId,
            );
        }elseif($jobId && $userId){
            $searchArray = array(
                'job_id' => $jobId,
                'e_id' => $userId,
            );
        }elseif ($jobId && $catId) {
            $searchArray = array(
                'job_id' => $jobId,
                'cat_id' => $catId,
            );
        }elseif($userId && $catId){
            $searchArray = array(
                'e_id' => $userId,
                'cat_id' => $catId,
            );
        }elseif($jobId){
            $searchArray = array(
                'job_id' => $jobId,
            );
        }elseif($userId){
            $searchArray = array(
                'e_id' => $userId,
            );
        }elseif($catId){
            $searchArray = array(
                'cat_id' => $catId,
            );
        }else{
            $searchArray = array();
        }
        
        $row       = $jobTable->fetchAll($jobTable->select($searchArray));
        
        $jobTable->events()->trigger(__CLASS__, 'before.load', $jobTable);
        if (!empty($row)) {
            $jobTable->setData((array) $row);
            $jobTable->setOrigData();
            $jobTable->events()->trigger(__CLASS__, 'after.load', $jobTable);
            return $jobTable;
        } else {
            $jobTable->events()->trigger(__CLASS__, 'after.load.failed', $jobTable);
            return false;
        }
    }

    /* Update Status Freeze/Accept/Delete/Enable/Desable from frontend */

    public function jobStatusUpdate($job_id,$status)
    {

        $updateArray = array(
                'job_status' => $status,
                'job_update_date' => date('Y-m-d H:i:as'),
            );
        $conditionArray = array(
                'job_id' => $job_id,                
            );
        $this->update($updateArray, $conditionArray);
    }

    public function updateJobCron($job_id,$action)
    {
        $updateArray = array(
                'job_status' => $action,
            );
        $conditionArray = array(
                'job_status' => 6,              
            );
        $this->update($updateArray, $conditionArray);
    }

}
