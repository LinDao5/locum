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
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Controller;
use Gc\User;
use Gc\Mvc\Controller\Action;
use Gc\User\Paymenthistory;
use Gc\User\Leaveuser;
use Gc\User\Model;
use Zend\Db\Sql\Select;

/**
 * Paymenthistory controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class PaymenthistoryController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'paymentHistory');

    /**
     * List all Paymenthistorys
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $paymenthistoryCollection = new Paymenthistory\Collection();
        $paymenthistories          = array();
        foreach ($paymenthistoryCollection->getPaymenthistories() as $paymenthistory) {
            if ($paymenthistory->getName() !== Paymenthistory\Model::PROTECTED_NAME) {
                $paymenthistories[] = $paymenthistory;
            }
        }

        return array('paymenthistories' => $paymenthistories);
    }
    public function fetchUser($id,$pyamentId){
        $userTable = new Model();
        $rows = $userTable->fetchRow($userTable->select(array('id' =>$id)));    
        if($rows['firstname'] != '' && $rows['email'] != ''){    
            return $rows;
        }else{
            $leaveUser = $this->getDeletedUserInfo($id);
            /*echo "<pre>";
            echo $id;
            print_r($leaveUser);
            echo "</pre>";*/
            $rows = '';
            if(!empty($leaveUser)){            
                $rows = array(
                        'user_name'=>$leaveUser['user_name'],
                        'email'=>$leaveUser['user_email'],
                    );            
                return $rows;
            }/*else{
                $paymenthistoryModel = Paymenthistory\Model::fromId($pyamentId);  
echo "<pre>";
            echo $id;
            print_r($paymenthistoryModel);
            echo "</pre>";
                if (!empty($paymenthistoryModel) and $paymenthistoryModel->getName() !== Paymenthistory\Model::PROTECTED_NAME and $paymenthistoryModel->delete()) {
                     //return $this->returnJson(array('success' => true, 'message' => 'Payment Info has been deleted'));
                }         
            }*/
        }
    }
   
    public function deleteAction()
    {
        $paymenthistoryModel = Paymenthistory\Model::fromId($this->getRouteMatch()->getParam('id'));

        if (!empty($paymenthistoryModel) and $paymenthistoryModel->getName() !== Paymenthistory\Model::PROTECTED_NAME and $paymenthistoryModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Payment Info has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Payment Info does not exists'));
    }
    public function updatePaidAction()
    {
       $paymenthistoryModel = Paymenthistory\Model::fromId($this->getRouteMatch()->getParam('id'));
       
        if (!empty($paymenthistoryModel)) {
            $paymenthistoryModel->updatePaid($this->getRouteMatch()->getParam('id'));
            $this->flashMessenger()->addSuccessMessage("Payment status has been updated");
            return $this->redirect()->toRoute('config/user/paymenthistory');
        }
    }
    public function updatePendingAction()
    {
        $paymenthistoryModel = Paymenthistory\Model::fromId($this->getRouteMatch()->getParam('id'));
       
        if (!empty($paymenthistoryModel)) {
            $paymenthistoryModel->updatePending($this->getRouteMatch()->getParam('id'));
            $this->flashMessenger()->addSuccessMessage("Payment status has been updated");
            return $this->redirect()->toRoute('config/user/paymenthistory');
        }
    }

    public function getDeletedUserInfo($uid)
    {
        $leaveUserTable = new Leaveuser\Model();
        $rows = $leaveUserTable->fetchRow($leaveUserTable->select(array('uid' =>$uid)));  
        return $rows;
    }
}
