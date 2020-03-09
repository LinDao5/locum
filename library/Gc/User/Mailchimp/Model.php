<?php
/**
 * Model class to store the live membership record in databse, develope by SURAJ WASNIK at FUDUGO
 */

namespace Gc\User\Mailchimp;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Mailchimp Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Mailchimp
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'subscribe_user';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Mailchimp
     *
     * @return integer
     */
    public function save($arraySave)
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        try {
            $this->insert($arraySave);
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
    public static function fromArray(array $array)
    {
        $subscriberTable = new Model();
        $subscriberTable->setData($array);
        $subscriberTable->setOrigData();

        return $subscriberTable;
    }

}
