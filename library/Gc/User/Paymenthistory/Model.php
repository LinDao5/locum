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
 * @subpackage User\Paymenthistory
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Paymenthistory;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Paymenthistory Paymenthistory
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Paymenthistory
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_payment_info';

    /**
     * Protected Paymenthistory name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\Paymenthistory\Model
     */
    public static function fromArray(array $array)
    {
        $paymenthistoryTable = new Model();
        $paymenthistoryTable->setData($array);
        $paymenthistoryTable->setOrigData();

        return $paymenthistoryTable;
    }
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getPid();
        if (!empty($id)) {
            try {
                parent::delete(array('pid' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    public function updatePaid($id)
    {
       
        if (!empty($id)) {
            try {
                 parent::update(array('payment_status' => 1), array('pid' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }
    public function updatePending($id)
    {
       
        if (!empty($id)) {
            try {
                $this->update(array('payment_status' => 0), array('pid' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    public static function fromId($userPaymentId)
    {
        $paymentTable = new Model();
        $row       = $paymentTable->fetchRow($paymentTable->select(array('pid' =>$userPaymentId)));        
        $paymentTable->events()->trigger(__CLASS__, 'before.load', $paymentTable);
        if (!empty($row)) {
            $paymentTable->setData((array) $row);
            $paymentTable->setOrigData();
            $paymentTable->events()->trigger(__CLASS__, 'after.load', $paymentTable);
            return $paymentTable;
        } else {
            $paymentTable->events()->trigger(__CLASS__, 'after.load.failed', $paymentTable);
            return false;
        }
    }
    
}
