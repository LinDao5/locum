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
 * @subpackage User\question
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Question;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Question Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Question
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_question';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Question
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this); 
        $allTypeValueArray = $this->getTypeValue();
        $filterArray = array_filter($allTypeValueArray);  
        $index = 0;
        $newAllValuArray = array();
        foreach ($filterArray as $key => $value) {
            $newAllValuArray[$index++]=$value;
        } 

            



       $allTypeValueArray = serialize($newAllValuArray);    

      
        $arraySave = array(            
            'cat_id' => $this->getUserAclProfessionId(),
            'fquestion' => $this->getFquestion(),
            'equestion' => $this->getEquestion(),
            'type_key' => $this->getTypeKey(),
            'sort_order' => $this->getSortOrder(),
            'type_value' => $allTypeValueArray,
            'range_type_unit' => $this->getTypeValueRangeUnit(),
            'range_type_condition' => $this->getTypeValueRangeType(),
            'required_status' => $this->getRequiredStatus(),
        );


 
        try {
            $qusId = $this->getId();
            if (empty($qusId)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $qusId));
                $qusId = $this->getId();
            }


            $permissions = $this->getPermissions();
            if (!empty($permissions)) {
                $aclTable = new TableGateway('user_acl', $this->getAdapter());
                $aclTable->delete(array('question_id' => $this->getId()));

                foreach ($permissions as $permissionId => $value) {
                    if (!empty($value)) {
                        $aclTable->insert(
                            array(
                                'question_id' => $this->getId(),
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
     * Delete Question
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
     * @return \Gc\User\Question\Model
     */
    public static function fromArray(array $array)
    {
        $questionTable = new Model();
        $questionTable->setData($array);
        $questionTable->setOrigData();

        return $questionTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userqusId User question id
     *
     * @return \Gc\User\question\Model
     */
    public static function fromId($userQusId)
    {
        $questionTable = new Model();
        $row       = $questionTable->fetchRow($questionTable->select(array('id' => (int) $userQusId)));
        $questionTable->events()->trigger(__CLASS__, 'before.load', $questionTable);
        if (!empty($row)) {
            $questionTable->setData((array) $row);
            $questionTable->setOrigData();
            $questionTable->events()->trigger(__CLASS__, 'after.load', $questionTable);
            return $questionTable;
        } else {
            $questionTable->events()->trigger(__CLASS__, 'after.load.failed', $questionTable);
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
                $select->from('user_question')
                    ->join(
                        'user_acl',
                        'user_acl.question_id = question.id',
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
                $select->where->equalTo('question.id', $this->getId());
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
