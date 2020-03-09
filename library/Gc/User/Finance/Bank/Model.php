<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Bank;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Finance Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Finance
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_bank_detail';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Finance
     *
     * @return integer
     */
    public function save($arraySave)
    {
		
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        try {		
            $supplierId = $this->getSupplierId();
		    $id = @$arraySave['bank_id'] ? $arraySave['bank_id'] : '';
			
            if (empty($supplierId) && $id == '' ) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
				unset($arraySave['bank_id']);				
                $this->update($arraySave, array('bank_id' => $id));
            }
            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getSupplierId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
   

}
