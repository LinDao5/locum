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
use Gc\User\Package;
use GcConfig\Form\Package as PackageForm;

/**
 * Package controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class PackageController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'package');

    /**
     * List all packages
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $packageCollection = new Package\Collection();
        $packages          = array();
        foreach ($packageCollection->getPackages() as $package) {
            if ($package->getName() !== Package\Model::PROTECTED_NAME) {
                $packages[] = $package;
            }
        }

        return array('packages' => $packages);
    }

    /**
     * Create package
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new PackageForm();  
         //$form->initPackagepermissions();      
        $form->setAttribute('action', $this->url()->fromRoute('config/user/package/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid()) {
                $packageModel = new Package\Model();
                $packageModel->addData($form->getInputFilter()->getValues());
                $packageModel->save();
                $this->flashMessenger()->addSuccessMessage('Package saved!');
                return $this->redirect()->toRoute('config/user/package/edit', array('id' => $packageModel->getId()));
            }

            $this->flashMessenger()->addErrorMessage('Package can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }

    /**
     * Delete package
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {
        $packageModel = Package\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($packageModel) and $packageModel->getName() !== Package\Model::PROTECTED_NAME and $packageModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Package has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Package does not exists'));
    }

    /**
     * Edit package
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $packageId = $this->getRouteMatch()->getParam('id');

        $packageModel = Package\Model::fromId($packageId);
        if (empty($packageModel) or $packageModel->getName() === Package\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this Package");
            return $this->redirect()->toRoute('config/user/package');
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
        $form = new PackageForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/package/edit', array('id' => $packageId)));
        
        $form->loadValues($packageModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            $form->setData($post);
            if ($form->isValid()) {
                $packageModel->addData($form->getInputFilter()->getValues());
                $packageModel->save();

                $this->flashMessenger()->addSuccessMessage('Package saved!');
                return $this->redirect()->toRoute('config/user/package/edit', array('id' => $packageId));
            }

            $this->flashMessenger()->addErrorMessage('Package can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }
}
