<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Employertrans;

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
    protected $name = 'finance_employer';

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


    public function deleteFinance($id,$uid)
    {

        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        if (!empty($id)) {
            parent::delete(array('id' => $id , 'emp_id' => $uid));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);
            return true;
        }
        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);
        return false;
    }
    
    
    public function deleteFinanceByjobid($empid,$job_id,$fre_id)
    {

        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        if (!empty($job_id)) {
            parent::delete(array('emp_id'=> $empid,'job_id' => $job_id , 'fre_id' => $fre_id));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);
            return true;
        }
        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);
        return false;
    }

    //update bank status and bank data
    public function update_bank($id,$arraySave)
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

}
