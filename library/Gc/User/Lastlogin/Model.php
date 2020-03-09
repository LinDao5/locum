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
 * @subpackage User\lastloginusers
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Lastlogin;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * lastloginusers Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\lastloginusers
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'last_login_user';

    /**
     * Protected lastloginusers name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\lastloginusers\Model
     */
    public static function fromArray(array $array)
    {
        $lastloginusersTable = new Model();
        $lastloginusersTable->setData($array);
        $lastloginusersTable->setOrigData();

        return $lastloginusersTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userlastloginusersId User lastloginusers id
     *
     * @return \Gc\User\lastloginusers\Model
     */
    public static function fromId($userlastloginusersId)
    {
        $lastloginusersTable = new Model();
        $row       = $lastloginusersTable->fetchRow($lastloginusersTable->select(array('id' => (int) $userlastloginusersId)));
        $lastloginusersTable->events()->trigger(__CLASS__, 'before.load', $lastloginusersTable);
        if (!empty($row)) {
            $lastloginusersTable->setData((array) $row);
            $lastloginusersTable->setOrigData();
            $lastloginusersTable->events()->trigger(__CLASS__, 'after.load', $lastloginusersTable);
            return $lastloginusersTable;
        } else {
            $lastloginusersTable->events()->trigger(__CLASS__, 'after.load.failed', $lastloginusersTable);
            return false;
        }
    }
    
}
