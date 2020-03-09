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

namespace Gc\User\Feedback\Frontend;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Feedback Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 */
class Collection extends AbstractTable
{
    /**
     * List of Feedbacks
     *
     * @var array
     */
    protected $feedbacks;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'feedback_qus';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getFeedbacks(true);
    }

    /**
     * Get Feedback Qustions
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getFeedbackQus($forceReload = false)
    {
        if (empty($this->feedbacks) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('fd_qus_sort_order ASC');
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

    /* == Feedback Question for freelancer == */
    public function getFreelancerQus( $catId )
    {
        $conditionalString = "fd_qus_cat_id = '$catId' AND fd_qus_fre != '' AND fd_qus_status = '1' ORDER BY fd_qus_sort_order ASC";
        $select = $this->select($conditionalString,
            function (Select $select) {                
                $select->where($conditionalString); 
            }
        );        
        $rows  = $this->fetchAll($select);
        $freQus = array();
        foreach ($rows as $row) {
            $freQus[] = Model::fromArray((array) $row);
        }
        return $freQus;
    }

    /* == Feedback Question for employer == */
    public function getEmployerQus( $catId )
    {
        $conditionalString = "fd_qus_cat_id = '$catId' AND fd_qus_emp != '' AND fd_qus_status = '1' ORDER BY fd_qus_sort_order ASC";
        $select = $this->select($conditionalString, 
            function (Select $select) {                
                $select->where($conditionalString);             
            }
        );

        $rows  = $this->fetchAll($select);
        $empQus = array();
        foreach ($rows as $row) {
            $empQus[] = Model::fromArray((array) $row);
        }
        return $empQus;
    }


    /* == Feedback Question By Id == */
    public function getQusById( $qusId , $uType)
    {
        $conditionalString = "fd_qus_cat_id = '$qusId' ";
        $select = $this->select($conditionalString, 
            function (Select $select) {                
                $select->where($conditionalString);             
            }
        );

        $rows  = $this->fetchAll($select);
        $qus = array();
        foreach ($rows as $row) {

            $qus[] = Model::fromArray((array) $row);
        }
        return $qus;
    }
}
