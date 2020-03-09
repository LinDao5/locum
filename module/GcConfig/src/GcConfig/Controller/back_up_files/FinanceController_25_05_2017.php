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

use GcConfig\Form\UserLogin;
use GcConfig\Form\Finance as FinanceForm;
use GcConfig\Form\UserForgotPassword as UserForgotForm;
use Gc\Mvc\Controller\Action;
use Gc\User;
use Gc\User\Role;
use Gc\User\Professional;
use Zend\View\Model\ViewModel;
use Zend\Validator\Identical;
use GcFrontend\Controller\JobmailController as MailController;
use GcFrontend\Helper\FinanceHelper as FinanceHelper;

/**
 * User controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class FinanceController extends Action
{
    /**
     * Contains information about acl resource
     *
     * @var array
     */
    protected $aclResource = 'Settings';

    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'finance');

    /**
     * List all roles
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $financeHelper = new FinanceHelper();
        $inUser = $financeHelper->getfinanceIncomeUser();
        $fre_id = array();
        foreach($inUser as $user){
            $fre_id[] = $user['fre_id'];
        }
        $userCollection = new User\Collection();
        $userFre  = array();
        if (isset($_GET['c']) && $_GET['c'] != '') {
            $catId = $_GET['c'];
        }else{
            $catId = 1;
        }
        $professions = $this->getProfession();
        $userFre1 = $userCollection->getFinanceUsers();
        foreach ($userFre1 as $user) {
            if(in_array($user->getId(),$fre_id)){
            if($user->getUserAclProfessionId() == $catId) {
                $userFre[] = $user;
            }
            }
        }
        return array( 'userFre'=>$userFre, 'professions' => $professions);
    }
    
    /**
     * balancesheet
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function balancesheetAction()
    {
        $userId   = $this->getRouteMatch()->getParam('id');
        $rec = $this->profitlossdata($userId);
        $user     = $rec['udata'][0];
        $revenue   = $rec['revenue'][0];
        $taxcalculation = $this->taxclaculation($revenue);
        return  array( 'userData' => $user ,
            'revenue1'=> $revenue ,
            'totaltax' => $taxcalculation
        );
    }

   /**
     * profitloss
     *
     * @return \Zend\View\Model\ViewModel|array
     */

    public function profitlossAction()
    {
        $userId    = $this->getRouteMatch()->getParam('id');
        $rec = $this->profitlossdata($userId);
        $user       = $rec['udata'][0];
        $revenue    = $rec['revenue'][0];
        $cos        = $rec['cos'][0];
        $othercost  = $rec['othercost'][0];
        $taxcalculation = $this->taxclaculation($revenue);
        return  array( 'userData' => $user ,
            'revenue1'=> $revenue ,
            'cos1' =>$cos ,
            'othercost1' => $othercost ,
            'totaltax' => $taxcalculation
        );
    }

    public function profitlossdata($userid = null)
    {
        $userCollection = new User\Collection();
        $financeHelper = new FinanceHelper();
        $udata = $revenue = $cos = $othercost  = array();

        $userdata = $userCollection->getFinanceUsers($userid);
        foreach ($userdata as $user) {
            $overallincome = $financeHelper->getIncomeByuser($userid);
            $overallexpence = $financeHelper->getExpenceByuser($userid);
            $expenceLunchtraval = $financeHelper->getExpenceLunchtravel($userid);

            $udata[]      = $user;
            $revenue[]    = $overallincome['job_rate'];
            $cos[]        = $expenceLunchtraval['cost'];
            $othercost[]  =  $overallexpence['cost'] - $expenceLunchtraval['cost'];
        }
        return array('udata'=> $udata, 'revenue' => $revenue , 'cos' => $cos, 'othercost' => $othercost);
    }
    

    public function taxclaculation($amount = 0)
    {
        if('11000' >= $amount){ // 0%
            return $totaltax = 0 ;
        }elseif('11000' < $amount && $amount <= '44500'){ // 20%
            $val_44500 = $amount - 11000 ; // 20%
            $val_44500_per =  $val_44500 * 20 /100 ;
            return  $totaltax = $val_44500_per ;
        }elseif('44500' < $amount && $amount <= '150000'){  // 40%
            $val_44500 = 44500 - 11000 ;    // 20%
            $val_150000 = $amount - 44500 ; // 40%
            $val_44500_per =  $val_44500 * 20 /100 ;
            $val_150000_per =  $val_150000 * 40 /100 ;
            return  $totaltax = $val_44500_per+$val_150000_per ;
        }elseif('150000' < $amount){ // 45%
            $val_44500 = 44500 - 11000 ; // 20%
            $val_150000 = 150000 - 44500 ; // 40%
            $val_150000_above = $amount - 150000;
            $val_44500_per =  $val_44500 * 20 /100 ;
            $val_150000_per =  $val_150000 * 40 /100 ;
            $val_150000_above_per = $val_150000_above * 45 /1000 ;
            return  $totaltax = $val_44500_per+$val_150000_per+$val_150000_above_per ;
        }
    }
    
        /**
     * This action is used when user has no access to display one page
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forbiddenAction()
    {
        $this->getResponse()->setStatusCode(403);
        $this->getResponse()->isForbidden(true);
        $this->layout()->setVariable('module', null);
    }
    public function getProfession()
    {
        $professionCollections = new Professional\Collection();
        $professions = array();
        foreach ($professionCollections->getProfessionals() as $profession) {
            $professions[] = $profession;
        }
        return   $professions;
    }
   
}