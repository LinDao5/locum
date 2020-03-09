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
 * @subpackage User\Leaveuser
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Leaveuser;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Leaveuser Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Leaveuser
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_leavers_table';

    /**
     * Protected Leaveuser name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\leaveuser\Model
     */
    public static function fromArray(array $array)
    {
        $leaveuserTable = new Model();
        $leaveuserTable->setData($array);
        $leaveuserTable->setOrigData();

        return $leaveuserTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userleaveuserId User leaveuser id
     *
     * @return \Gc\User\leaveuser\Model
     */
    public static function fromId($userLeaveuserId)
    {
        $leaveuserTable = new Model();
        $row       = $leaveuserTable->fetchRow($leaveuserTable->select(array('id' => (int) $userLeaveuserId)));
        $leaveuserTable->events()->trigger(__CLASS__, 'before.load', $leaveuserTable);
        if (!empty($row)) {
            $leaveuserTable->setData((array) $row);
            $leaveuserTable->setOrigData();
            $leaveuserTable->events()->trigger(__CLASS__, 'after.load', $leaveuserTable);
            return $leaveuserTable;
        } else {
            $leaveuserTable->events()->trigger(__CLASS__, 'after.load.failed', $leaveuserTable);
            return false;
        }
    }
    
}
