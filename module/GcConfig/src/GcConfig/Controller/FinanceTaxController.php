<?php
/**
 * Develop By Suraj Wasnik( suraj.wasnik0126@gmail.com ) at FuduGo Solutions
 */

namespace GcConfig\Controller;
use Gc\Mvc\Controller\Action;
use Gc\User\Finance\Tax as FinanceTax;
use Gc\User\Finance\Tax\Ni as FinanceNiTax;
use GcConfig\Form\FinanceTax as FinanceTaxForm;
use GcConfig\Form\FinanceNiC2Tax as FinanceNiC2Tax;
/**
 * Finance Tax controller
 *
 * @category   Gc_Application
 * @FinanceTax    GcConfig
 * @subFinanceTax Controller
 */
class FinanceTaxController extends Action
{
    /**
     * Contains information about acl resourcez
     *
     * @var array
     */
    protected $aclResource = 'Settings';

    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'tax');

    /**
     * List all Tax Records
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    static public function taxListAction()
    {
        $financeTaxCollection = new FinanceTax\Collection();
        $taxes          = array();
        foreach ($financeTaxCollection->getFinanceTaxes() as $tax) {
            $taxes[] = $tax;            
        }
        return array('financeTax' => $taxes);
    } 

    /* Edit Tax Records */
    public function taxEditAction()
    {
        $financeTaxId = $this->getRouteMatch()->getParam('id');

        $financeTaxModel = FinanceTax\Model::fromId($financeTaxId);
        if (empty($financeTaxModel) or $financeTaxModel->getName() === FinanceTax\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this Finance Tax");
            return $this->redirect()->toRoute('config/user/finance/tax');
        }
        
        $form = new FinanceTaxForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/finance/tax/edit', array('id' => $financeTaxId)));
        
        $form->loadValues($financeTaxModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            $form->setData($post);
            if ($form->isValid()) {
                $financeTaxModel->addData($form->getInputFilter()->getValues());
                $financeTaxModel->save();

                $this->flashMessenger()->addSuccessMessage('Finance Tax Record saved!');
                return $this->redirect()->toRoute('config/user/finance/tax/edit', array('id' => $financeTaxId));
            }

            $this->flashMessenger()->addErrorMessage('Finance Tax can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);        
    }   

    /* Add Tax Records */
    public function taxAddAction()
    {
        $form = new FinanceTaxForm();  
         //$form->initFinanceTaxpermissions();      
        $form->setAttribute('action', $this->url()->fromRoute('config/user/finance/tax/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid()) {
                $financeTaxModel = new FinanceTax\Model();
                $financeTaxModel->addData($form->getInputFilter()->getValues());
                $financeTaxModel->save();
                $this->flashMessenger()->addSuccessMessage('Record saved!');
                return $this->redirect()->toRoute('config/user/finance/tax/edit', array('id' => $financeTaxModel->getId()));
            }

            $this->flashMessenger()->addErrorMessage('Record can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }    

    /* Delete Tax Record */
    public function taxDeleteAction()
    {
        $financeTaxModel = FinanceTax\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($financeTaxModel) and $financeTaxModel->getName() !== FinanceTax\Model::PROTECTED_NAME and $financeTaxModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Record has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'FinanceTax does not exists'));
    }
    
    public function taxListByinyear($finyear= '2016-2017')
    {
        $financeTaxYear = FinanceTax\Model::fromFinyear($finyear);        
        return $financeTaxYear;
    }

    public function niTaxListAction()
    {
        $financeTaxCollection = new FinanceNiTax\Collection();
        $taxes          = array();
        foreach ($financeTaxCollection->getFinanceTaxes() as $tax) {
            $taxes[] = $tax;            
        }
        return array('niFinanceTax' => $taxes);        
    } 
    public function niTaxAddAction()
    {
        $form = new FinanceNiC2Tax();  
         //$form->initFinanceTaxpermissions();      
        $form->setAttribute('action', $this->url()->fromRoute('config/user/finance/ni-tax/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray(); 
            $form->setData($post);
            if ($form->isValid()) {                
                $financeTaxModel = new FinanceNiTax\Model();
                $financeTaxModel->addData($form->getInputFilter()->getValues());
                $financeTaxModel->save($form->getInputFilter()->getValues());
                $this->flashMessenger()->addSuccessMessage('Record saved!');
                return $this->redirect()->toRoute('config/user/finance/ni-tax/edit', array('id' => $financeTaxModel->getNiId()));
            }

            $this->flashMessenger()->addErrorMessage('Record can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    } 
    public function niTaxEditAction()
    {
        $financeTaxId = $this->getRouteMatch()->getParam('id');

        $financeTaxModel = FinanceNiTax\Model::fromId($financeTaxId);
        if (empty($financeTaxModel) or $financeTaxModel->getName() === FinanceNiTax\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this Finance Tax");
            return $this->redirect()->toRoute('config/user/finance/ni-tax');
        }
        
        $form = new FinanceNiC2Tax();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/finance/ni-tax/edit', array('id' => $financeTaxId)));
        
        $form->loadValues($financeTaxModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            $form->setData($post);
            if ($form->isValid()) {
                $financeTaxModel->addData($form->getInputFilter()->getValues());
                $financeTaxModel->save($form->getInputFilter()->getValues());

                $this->flashMessenger()->addSuccessMessage('Finance Tax Record saved!');
                return $this->redirect()->toRoute('config/user/finance/ni-tax/edit', array('id' => $financeTaxId));
            }

            $this->flashMessenger()->addErrorMessage('Finance Tax can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);    
    } 
    public function niTaxDeleteAction()
    {
        $financeTaxModel = FinanceNiTax\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($financeTaxModel) and $financeTaxModel->getName() !== FinanceNiTax\Model::PROTECTED_NAME and $financeTaxModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Record has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Ni FinanceTax does not exists'));
    }  
    
}