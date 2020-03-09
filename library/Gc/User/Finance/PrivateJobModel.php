<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Finance Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Finance
 */
class PrivateJobModel extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'freelancer_private_finance';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Finance
     *
     * @return integer
     */
    public function save($arraySave)
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);       
        /*print_r($arraySave);
        echo $this->getFinanceId();
        exit();*/
        try {
            //print_r($arraySave);
            $financeId = $this->getJobId();
            $jobId = $this->getJobId();

            if (!$this->isJobFinanceAdded($arraySave['job_id'] , $arraySave['fre_id'])) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
                return 1;
            } else {
                $this->update($arraySave, array('job_id' => $arraySave['job_id'],'fre_id' => $arraySave['fre_id']));                
            }
            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete Finance
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getFinanceId();
        if (!empty($id)) {
            parent::delete(array('id' => $id));
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
     * @return \Gc\User\Finance\Model
     */
    public static function fromArray(array $array)
    {
        $FinanceTable = new Model();
        $FinanceTable->setData($array);
        $FinanceTable->setOrigData();

        return $FinanceTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userqusId User Finance id
     *
     * @return \Gc\User\Finance\Model
     */
    public static function fromId($userFinanceId)
    {
        $financeTable = new Model();
        $row       = $financeTable->fetchRow($financeTable->select(array('id' => (int) $userFinanceId)));
        $financeTable->events()->trigger(__CLASS__, 'before.load', $financeTable);
        if (!empty($row)) {
            $financeTable->setData((array) $row);
            $financeTable->setOrigData();
            $financeTable->events()->trigger(__CLASS__, 'after.load', $financeTable);
            return $financeTable;
        } else {
            $financeTable->events()->trigger(__CLASS__, 'after.load.failed', $financeTable);
            return false;
        }
    }
    

    /* Update Status Freeze/Accept/Delete/Enable/Desable from frontend */

    public function FinanceStatusUpdate($Finance_id,$status)
    {
        $updateArray = array(
                'Fstatus' => $status,
                'update_at' => date('Y-m-d H:i:as'),
            );
        $conditionArray = array(
                'id' => $Finance_id,                
            );
        $this->update($updateArray, $conditionArray);
    }

    public function updateFinanceCron($Finance_id,$action)
    {
        $updateArray = array(
                'status' => $action,
            );
        $conditionArray = array(
                'status' => 6,              
            );
        $this->update($updateArray, $conditionArray);
    }

    /* Update finance with expense cost */
    public function updateFinanceWithExpense($checkFinanceModel,$updateFinanceModel)
    {
        $this->update($updateFinanceModel, $checkFinanceModel);
    }

    /* Check if job finance already exist */
    private function isJobFinanceAdded($jobId , $freId)
    {
        $query = "job_id = '$jobId' AND fre_id = '$freId' ";
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
        if (!empty($jobs)) {
            return true;
        }else{
            return false;
        }
    }

    /* Check if job expense already exist */
    public function expenseCheck($freId,$job_id)
    {
        $query = "job_id = '$job_id' AND fre_id = '$freId' AND job_expense != '' ";
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

        if (!empty($jobs)) {
            return true;
        }else{
            return false;
        }
    }
}
