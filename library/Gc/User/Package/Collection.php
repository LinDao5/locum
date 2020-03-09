<?php


namespace Gc\User\Package;

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
    protected $packages;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_acl_package';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getPackages(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getPackages($forceReload = false)
    {
        if (empty($this->packages) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id ASC');
                    }
                )
            );

            $packages = array();
            foreach ($rows as $row) {
                $packages[] = Model::fromArray((array) $row);
            }

            $this->packages = $packages;
        }

        return $this->packages;
    }

    /* Get package name by id */
    public function getPackageName($id)
    {

       $select = $this->select(
            function (Select $select) {
                $select->where("id = '$id'");                
            }
        );

        $rows  = $this->fetchAll($select);
        $pkg = array();
        foreach ($rows as $row) {
            $pkg[] = Model::fromArray((array) $row);
        }
        return $pkg;
    }
}
