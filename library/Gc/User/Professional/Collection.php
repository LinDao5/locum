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
 * @subpackage User\Professional
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Professional;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Professional Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Professional
 */
class Collection extends AbstractTable
{
    /**
     * List of Professionals
     *
     * @var array
     */
    protected $professionals;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_acl_professional';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getProfessionals(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getProfessionals($forceReload = false)
    {
        if (empty($this->professionals) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('name');
                    }
                )
            );

            $professionals = array();
            foreach ($rows as $row) {
                $professionals[] = Model::fromArray((array) $row);
            }

            $this->professionals = $professionals;
        }

        return $this->professionals;
    }

    public function getProfessionalById($professionId)
    {
       $query = "id = '$professionId'";
       $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
       $rows = $this->fetchAll($select);

       $professionals = array();
       foreach ($rows as $row) {
            $professionals[] = Model::fromArray((array) $row);
       }
       $this->professionals = $professionals;
       return $this->professionals;
    }
}
