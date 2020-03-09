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

namespace Gc\User\Lastlogin;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Leaveuser Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Leaveuser
 */
class Collection extends AbstractTable
{
    /**
     * List of Leaveusers
     *
     * @var array
     */
    protected $lastloginusers;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'last_login_user';

    /**
     * Initiliaze Leaveuser collection
     *
     * @return void
     */
    public function init()
    {
        //$this->getLastLoginUsers(true,$start_date = null,$end_date = null);
    }

    /**
     * Get Leaveusers
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Leaveuser\Model
     */
    public function getLastLoginUsers($forceReload = false,$start_date = null,$end_date = null)
    {
        if($end_date == null){$end_date = date('Y-m-d');}
        if($start_date != null || $start_date != ''){
                $rows = $this->fetchAll("SELECT * FROM last_login_user WHERE last_login_at BETWEEN '$start_date' AND '$end_date' ORDER BY last_login_at DESC");
        }else{
             if (empty($this->lastloginusers) or $forceReload === true) {
                 $rows = $this->fetchAll("SELECT * FROM last_login_user ORDER BY last_login_at DESC ");
                
             }           
        }
        $lastloginusers = array();
        foreach ($rows as $row) {
              $lastloginusers[] = Model::fromArray((array) $row);
        }
        $this->lastloginusers = $lastloginusers;
        return $this->lastloginusers;
    }
}
