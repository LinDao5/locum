<?php
/**
 * This source file is part of FUDUGO. *
 * 
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 * @author     Suraj Wasnik (suraj.wasnik0126@gmail.com)
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.fudugo.com
 */


namespace Gc\User\Feedback\Dispute;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Feedback Dispute Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback\Dispute
 */
class Collection extends AbstractTable
{
    /**
     * List of Dispute Feedbacks 
     *
     * @var array
     */
    protected $feedbacks;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_feedback_dispute';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getDisputeFeedbacks(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getDisputeFeedbacks($forceReload = false)
    {
        if (empty($this->feedbacks) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id ASC');
                    }
                )
            );

            $feedbacks = array();
            foreach ($rows as $row) {
                $feedbacks[] = Model::fromArray((array) $row);
            }

            $this->feedbacks = $feedbacks;
        }

        return $this->feedbacks;
    }
}
