<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Income;

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
    protected $name = 'finance_income_table';

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
  /*  public function save($arraySave) ---------not in use
    {		
		 $this->events()->trigger(__CLASS__, 'before.save', $this);
	try {
		$Id = $this->getId();
            if (empty($Id)) {
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
		$Id = $this->getId();
        $editid =  @$arraySave['id'] ? $arraySave['id'] : '' ;
        if (empty($Id) && !isset($arraySave['id']) && $editid == '') {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                unset($arraySave['id']);
                $this->update($arraySave, array('id' => $editid));
            }
             $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }		
    }

	//update bank status and bank data
	    public function inupdate_bank($id,$arraySave)
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


	//update Invoice status from income table
	    public function income_invoiceReq($id,$arraySave)
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


    public function deleteFinanceincome($id,$uid)
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
    

    /* Check if freelancer already submit expense for the current job  */

    public function expenseCheck($fid,$jobId,$eid)
    {
        $select = $this->select(
            function (Select $select) use ($fid,$jobId,$eid) {
                $select->where->equalTo('fre_id', $fid);
                $select->where->equalTo('emp_id', $eid);
                $select->where->equalTo('job_id', $jobId);
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
