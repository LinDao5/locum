<?php
/**
 * Develop By Suraj Wasnik( suraj.wasnik0126@gmail.com ) at FuduGo Solutions
 */

namespace Gc\User\Finance\Tax;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of FinanceTax Model
 *
 * @category   Gc
 * @FinanceTax    Library
 * @subFinanceTax User\FinanceTax
 */
class Collection extends AbstractTable
{
    /**
     * List of FinanceTaxs
     *
     * @var array
     */
    protected $financeTaxs;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance_tax_record';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getFinanceTaxes(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getFinanceTaxes($forceReload = false)
    {
        if (empty($this->financeTaxs) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );

            $financeTaxes = array();
            foreach ($rows as $row) {
                $financeTaxes[] = Model::fromArray((array) $row);
            }

            $this->financeTaxes = $financeTaxes;
        }

        return $this->financeTaxes;
    }    
}
