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

use Gc\Mvc\Controller\Action;
use Gc\User\Packageresource;
use GcConfig\Form\Packageresource as PackageResourceForm;

/**
 * Package controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class PackageresourceController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'packageResource');

    /**
     * List all packages
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $packageResourceCollection = new Packageresource\Collection();
        $packageResources          = array();
        foreach ($packageResourceCollection->getPackageResources() as $packageResource) {
            if ($packageResource->getName() !== Packageresource\Model::PROTECTED_NAME) {
                $packageResources[] = $packageResource;
            }
        }

        return array('packageResources' => $packageResources);
    }

    /**
     * Create package
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new PackageResourceForm(); 
        $form->setAttribute('action', $this->url()->fromRoute('config/user/packageresource/create'));

        if ($this->getRequest()->isPost()) {
            $unique = $this->checkUniqueResourceKeyAction($this->getRequest()->getPost('resource_key'));
            if ($unique) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid() && $this->getRequest()->getPost('submit')) {
                $packageResourceModel = new Packageresource\Model();
                $packageResourceModel->addData($form->getInputFilter()->getValues());
                $packageResourceModel->save();
                $this->flashMessenger()->addSuccessMessage('Package Resource saved!');
                return $this->redirect()->toRoute('config/user/packageresource/edit', array('id' => $packageResourceModel->getId()));
            }elseif($form->isValid() && $this->getRequest()->getPost('addNew')){
                $packageResourceModel = new Packageresource\Model();
                $packageResourceModel->addData($form->getInputFilter()->getValues());
                $packageResourceModel->save();
                $this->flashMessenger()->addSuccessMessage('Package Resource saved!');
                return $this->redirect()->toRoute('config/user/packageresource/create');
            }
            }else{
                $this->flashMessenger()->addErrorMessage('Privilages key is already exist please use unique key');
            }

            $this->flashMessenger()->addErrorMessage('Package Resource can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }

    /**
     * Delete packageResource
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {
        $packageResourceModel = Packageresource\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($packageResourceModel) and $packageResourceModel->getName() !== Packageresource\Model::PROTECTED_NAME and $packageResourceModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Package Resource has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Package Resource does not exists'));
    }

    /**
     * Edit packageResource
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $packageResourceId = $this->getRouteMatch()->getParam('id');

        $packageResourceModel = Packageresource\Model::fromId($packageResourceId);
        if (empty($packageResourceModel) or $packageResourceModel->getName() === Packageresource\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this Package Resource");
            return $this->redirect()->toRoute('config/user/packageresource');
        }
        /*echo "<pre>";
        print_r($packageModel);
        echo "</pre>";
        foreach ($packageModel as $key => $value) {
            echo "<pre>";
            print_r($value);
            echo "</pre>";
        }
        exit();*/
        $form = new PackageResourceForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/packageresource/edit', array('id' => $packageResourceId)));
        
        $form->loadValues($packageResourceModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            $form->setData($post);
            if ($form->isValid() && $this->getRequest()->getPost('submit')) {
                $packageResourceModel->addData($form->getInputFilter()->getValues());
                $packageResourceModel->save();

                $this->flashMessenger()->addSuccessMessage('Package Resource saved!');
                return $this->redirect()->toRoute('config/user/packageresource/edit', array('id' => $packageResourceId));
            }elseif($form->isValid() && $this->getRequest()->getPost('addNew')){
                $packageResourceModel->addData($form->getInputFilter()->getValues());
                $packageResourceModel->save();

                $this->flashMessenger()->addSuccessMessage('Package Resource saved!');
                return $this->redirect()->toRoute('config/user/packageresource/create');
            }

            $this->flashMessenger()->addErrorMessage('Package Resource can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }
    public function checkUniqueResourceKeyAction($uniquekey){

        $packageResourceCollection = new Packageresource\Collection();
        $check = $packageResourceCollection->getCheckUniqKey($uniquekey);
        
        if ($check) {
            return 1;
        }else{
            return 0;
        }
    }
}
