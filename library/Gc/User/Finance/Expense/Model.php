<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Expense;

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
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance_expense_table';

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
     
   /* not in use
   public function save($arraySave) 
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        try {
            $expenseId = $this->getId();
            $jobId = $this->getJobId();
            if (empty($expenseId) && $jobId != $arraySave['job_id']) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
            }
            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }*/
    
        public function save($arraySave)
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        try {
            $expenseId = $this->getId();
            $editid =  @$arraySave['id'] ? $arraySave['id'] : '' ;
            if (empty($expenseId) & !isset($arraySave['id']) && $editid == '') {
                $this->insert($arraySave);
                //$this->setId($this->getLastInsertId());
                return $this->getLastInsertId();
            } else {
                unset($arraySave['id']);
                $this->update($arraySave, array('id' => $editid));
            }
            $this->events()->trigger(__CLASS__, 'after.save', $this);

        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
	
    
    
	
	//update bank status and bank data
	    public function exupdate_bank($id,$arraySave)
    {		
		 $this->events()->trigger(__CLASS__, 'before.save', $this);
	try {	
			//$Id = $this->getId();	

            $this->update($arraySave, array('id' => $id));
            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }		
    }

    public function deleteFinanceexpence($id,$uid)
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        if (!empty($id)) {
            parent::delete(array('id' => $id , 'fre_id' => $uid));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);
            return true;
        }
        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);
        return false;
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
        $financeExpenseTable = new Model();
        $financeExpenseTable->setData($array);
        $financeExpenseTable->setOrigData();

        return $financeExpenseTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userqusId User Finance id
     *
     * @return \Gc\User\Finance\Model
     */
    public static function fromId($userFinanceExpenseId)
    {
        $financeExpenseTable = new Model();
        $row       = $financeExpenseTable->fetchRow($financeExpenseTable->select(array('id' => (int) $userFinanceExpenseId)));
        $financeExpenseTable->events()->trigger(__CLASS__, 'before.load', $financeExpenseTable);
        if (!empty($row)) {
            $financeExpenseTable->setData((array) $row);
            $financeExpenseTable->setOrigData();
            $financeExpenseTable->events()->trigger(__CLASS__, 'after.load', $financeExpenseTable);
            return $financeExpenseTable;
        } else {
            $financeExpenseTable->events()->trigger(__CLASS__, 'after.load.failed', $financeExpenseTable);
            return false;
        }
    }
    

    /* Check if freelancer already submit expense for the current job  */

    public function expenseCheck($fid,$jobId,$typeid,$jobtype)
    {
        $select = $this->select(
            function (Select $select) use ($fid,$jobId,$typeid,$jobtype) {
                $select->where->equalTo('fre_id', $fid);
               $select->where->equalTo('expense_type_id', $typeid);
                $select->where->equalTo('job_id', $jobId);
                $select->where->equalTo('job_type', $jobtype);
            }
        );
        $row = $this->fetchRow($select);
        if (empty($row)) {
            return true;
        }else{
            return false;
        }
    }

}
