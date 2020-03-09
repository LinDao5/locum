<?php
/**
 * Design & Developer by Suraj Wasnik (suraj.wasnik0126@gmail.com) at Fudugo Solutions
 */

namespace Gc\User\Paymenthistory;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Paymenthistory Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Paymenthistory
 */
class Collection extends AbstractTable
{
    /**
     * List of Paymenthistory
     *
     * @var array
     */
    protected $paymenthistories;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_payment_info';

    /**
     * Initiliaze Paymenthistory collection
     *
     * @return void
     */
    public function init()
    {
        $this->getPaymenthistories(true);
    }

    /**
     * Get Paymenthistory
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Paymenthistory\Model
     */
    public function getPaymenthistories($forceReload = false)
    {
        if (empty($this->paymenthistories) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('pid DESC');
                    }
                )
            );

            $paymenthistories = array();
            foreach ($rows as $row) {
                $paymenthistories[] = Model::fromArray((array) $row);
            }

            $this->paymenthistories = $paymenthistories;
        }

        return $this->paymenthistories;
    }
}
