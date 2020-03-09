<?php


namespace Gc\User\Package;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Gc\User\PackageRateReport;
/**
 * package Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\package
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_acl_package';

    /**
     * Protected package name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save package
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        /*print_r($this->getUserAclPackageResourcesId());
        exit();*/
        $pResource = serialize($this->getUserAclPackageResourcesId());
        $arraySave = array(
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'description' => $this->getDescription(),
            'user_acl_package_resources_id' => $pResource,
        );

        try {
            $packageId = $this->getId();
            if (empty($packageId)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
            }

            $packageRateReport = new PackageRateReport();
            $packageRateReport->addIntoPackageReport($this->getId(),$this->getPrice());


            $permissions = $this->getPermissions();
            if (!empty($permissions)) {
                $aclTable = new TableGateway('user_acl', $this->getAdapter());
                $aclTable->delete(array('user_acl_package_id' => $this->getId()));

                foreach ($permissions as $permissionId => $value) {
                    if (!empty($value)) {
                        $aclTable->insert(
                            array(
                                'user_acl_package_id' => $this->getId(),
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
     * Delete package
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
     * @return \Gc\User\package\Model
     */
    public static function fromArray(array $array)
    {
        $packageTable = new Model();
        $packageTable->setData($array);
        $packageTable->setOrigData();

        return $packageTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userpackageId User package id
     *
     * @return \Gc\User\package\Model
     */
    public static function fromId($userPackageId)
    {
        $packageTable = new Model();
        $row       = $packageTable->fetchRow($packageTable->select(array('id' => (int) $userPackageId)));
        $packageTable->events()->trigger(__CLASS__, 'before.load', $packageTable);
        if (!empty($row)) {
            $packageTable->setData((array) $row);
            $packageTable->setOrigData();
            $packageTable->events()->trigger(__CLASS__, 'after.load', $packageTable);
            return $packageTable;
        } else {
            $packageTable->events()->trigger(__CLASS__, 'after.load.failed', $packageTable);
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
                $select->from('user_acl_package')
                    ->join(
                        'user_acl',
                        'user_acl.user_acl_package_id = user_acl_package.id',
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
                $select->where->equalTo('user_acl_package.id', $this->getId());
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
