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
 * @subpackage User\professional
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Professional;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Professional Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Professional
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_acl_professional';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Professional
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);            
        $arraySave = array(
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        );

        try {
            $professionalId = $this->getId();
            if (empty($professionalId)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
            }

            $permissions = $this->getPermissions();
            if (!empty($permissions)) {
                $aclTable = new TableGateway('user_acl', $this->getAdapter());
                $aclTable->delete(array('user_acl_professional_id' => $this->getId()));

                foreach ($permissions as $permissionId => $value) {
                    if (!empty($value)) {
                        $aclTable->insert(
                            array(
                                'user_acl_professional_id' => $this->getId(),
                                'user_acl_permission_id' => $permissionId
                            )
                        );
                    }
                }
            }

            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete professional
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getId();
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
     * @return \Gc\User\professional\Model
     */
    public static function fromArray(array $array)
    {
        $professionalTable = new Model();
        $professionalTable->setData($array);
        $professionalTable->setOrigData();

        return $professionalTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userprofessionalId User professional id
     *
     * @return \Gc\User\professional\Model
     */
    public static function fromId($userProfessionalId)
    {
        $professionalTable = new Model();
        $row       = $professionalTable->fetchRow($professionalTable->select(array('id' => (int) $userProfessionalId)));
        $professionalTable->events()->trigger(__CLASS__, 'before.load', $professionalTable);
        if (!empty($row)) {
            $professionalTable->setData((array) $row);
            $professionalTable->setOrigData();
            $professionalTable->events()->trigger(__CLASS__, 'after.load', $professionalTable);
            return $professionalTable;
        } else {
            $professionalTable->events()->trigger(__CLASS__, 'after.load.failed', $professionalTable);
            return false;
        }
    }

    /**
     * Get User permissions
     *
     * @param boolean $forceReload Force reload permissions
     *
     * @return array
     */
    public function getUserPermissions($forceReload = false)
    {
        $userPermissions = $this->getData('user_permissions');
        if (empty($userPermissions) or $forceReload) {
            $select = new Select();
            if ($this->getName() === self::PROTECTED_NAME) {
                $select->from('user_acl_resource')
                    ->join(
                        'user_acl_permission',
                        'user_acl_resource.id = user_acl_permission.user_acl_resource_id',
                        array(
                            'userPermissionId' => 'id',
                            'permission'
                        )
                    );
            } else {
                $select->from('user_acl_professional')
                    ->join(
                        'user_acl',
                        'user_acl.user_acl_professional_id = user_acl_professional.id',
                        array()
                    )->join(
                        'user_acl_permission',
                        'user_acl_permission.id = user_acl.user_acl_permission_id',
                        array(
                            'userPermissionId' => 'id',
                            'permission'
                        )
                    )->join(
                        'user_acl_resource',
                        'user_acl_resource.id = user_acl_permission.user_acl_resource_id',
                        array('resource')
                    );
                $select->where->equalTo('user_acl_professional.id', $this->getId());
            }

            $permissions     = $this->fetchAll($select);
            $userPermissions = array();
            foreach ($permissions as $permission) {
                if (empty($userPermissions[$permission['resource']])) {
                    $userPermissions[$permission['resource']] = array();
                }

                $userPermissions[$permission['resource']][$permission['userPermissionId']] = $permission['permission'];
            }

            $this->setData('user_permissions', $userPermissions);
        }

        return $userPermissions;
    }
}
