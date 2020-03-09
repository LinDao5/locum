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
 * @subpackage User\package
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Packageresource;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Package Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Package
 */
class Collection extends AbstractTable
{
    /**
     * List of Packages
     *
     * @var array
     */
    protected $Packageresources;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_acl_package_resources';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getPackageResources(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getPackageResources($forceReload = false)
    {
        if (empty($this->packageResources) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );

            $packageResources = array();
            foreach ($rows as $row) {
                $packageResources[] = Model::fromArray((array) $row);
            }

            $this->packageResources = $packageResources;
        }

        return $this->packageResources;
    }
    public function getCheckUniqKey($checkkey)
    {
        $select = new Select();
        $select->from('user_acl_package_resources');
        $select->where->equalTo('resource_key',$checkkey);
        $rows = $this->fetchAll($select);
        
        if (!empty($rows)) {
            return 0;
        }else{
            return 1;
        }
        /*echo $checkkey;
        exit();*/
    }
    public function getPrivilegeValue($id)
    {
        $select = new Select();
        $select->from('user_acl_package_resources');
        $select->where->equalTo('id',$id);
        $rows = $this->fetchAll($select);
        return $rows;
    }
}
