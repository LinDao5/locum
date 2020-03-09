<?php
/**
 * Develop by Suraj Wasnik (suraj.wasnik0126@gmail.com) at FuduGo Solutions.
 */

namespace Gc\User\Finance\Tax\Ni;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Role Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Role
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance_ni_tax_record';

    /**
     * Protected role name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Role
     *
     * @return integer
     */
    public function save($arraySave)
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        try {
            $financeTaxId = $this->getNiId();
            if (empty($financeTaxId)) {
                $this->insert($arraySave);
                $this->setNiId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('ni_id' => $this->getNiId()));
            }

            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getNiId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete Role
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getNiId();
        if (!empty($id)) {
            parent::delete(array('ni_id' => $id));
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
     * @return \Gc\User\Role\Model
     */
    public static function fromArray(array $array)
    {
        $financeTaxTable = new Model();
        $financeTaxTable->setData($array);
        $financeTaxTable->setOrigData();

        return $financeTaxTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userfinanceTaxId User role id
     *
     * @return \Gc\User\Role\Model
     */
    public static function fromId($userfinanceTaxId)
    {
        $financeTaxTable = new Model();
        $row       = $financeTaxTable->fetchRow($financeTaxTable->select(array('ni_id' => (int) $userfinanceTaxId)));
        $financeTaxTable->events()->trigger(__CLASS__, 'before.load', $financeTaxTable);
        if (!empty($row)) {
            $financeTaxTable->setData((array) $row);
            $financeTaxTable->setOrigData();
            $financeTaxTable->events()->trigger(__CLASS__, 'after.load', $financeTaxTable);
            return $financeTaxTable;
        } else {
            $financeTaxTable->events()->trigger(__CLASS__, 'after.load.failed', $financeTaxTable);
            return false;
        }
    }
    
    
    public static function fromFinyear($userfinanceYear)
    {
        $financeTaxTable = new Model();
        $row       = $financeTaxTable->fetchRow($financeTaxTable->select(array('finance_year' => $userfinanceYear)));
        $financeTaxTable->events()->trigger(__CLASS__, 'before.load', $financeTaxTable);
        if (!empty($row)) {
            $financeTaxTable->setData((array) $row);
            $financeTaxTable->setOrigData();
            $financeTaxTable->events()->trigger(__CLASS__, 'after.load', $financeTaxTable);
            return $financeTaxTable;
        } else {
            $financeTaxTable->events()->trigger(__CLASS__, 'after.load.failed', $financeTaxTable);
            return false;
        }
    }
    
    
}
