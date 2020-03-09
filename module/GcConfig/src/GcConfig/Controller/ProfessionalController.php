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
use Gc\User\Professional;
use GcConfig\Form\Professional as ProfessionalForm;

/**
 * Professional controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class ProfessionalController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'profession');

    /**
     * List all professionals
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $professionalCollection = new Professional\Collection();
        $professionals          = array();
        foreach ($professionalCollection->getProfessionals() as $professional) {
            if ($professional->getName() !== Professional\Model::PROTECTED_NAME) {
                $professionals[] = $professional;
            }
        }

        return array('professionals' => $professionals);
    }

    /**
     * Create professional
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new ProfessionalForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/professional/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid() && $this->getRequest()->getPost('submit')) {
                $professionalModel = new Professional\Model();
                $professionalModel->addData($form->getInputFilter()->getValues());
                $professionalModel->save();
                $this->flashMessenger()->addSuccessMessage('Professional saved!');
                return $this->redirect()->toRoute('config/user/professional/edit', array('id' => $professionalModel->getId()));
            }elseif($form->isValid() && $this->getRequest()->getPost('addNew')){
                $professionalModel = new Professional\Model();
                $professionalModel->addData($form->getInputFilter()->getValues());
                $professionalModel->save();
                $this->flashMessenger()->addSuccessMessage('Professional saved!');
                return $this->redirect()->toRoute('config/user/professional/create');
            }

            $this->flashMessenger()->addErrorMessage('Professional can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }

    /**
     * Delete professional
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {
        $professionalModel = Professional\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($professionalModel) and $professionalModel->getName() !== Professional\Model::PROTECTED_NAME and $professionalModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Professional has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Professional does not exists'));
    }

    /**
     * Edit professional
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $professionalId = $this->getRouteMatch()->getParam('id');

        $professionalModel = Professional\Model::fromId($professionalId);
        if (empty($professionalModel) or $professionalModel->getName() === Professional\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this professional");
            return $this->redirect()->toRoute('config/user/professional');
        }
        /*echo "<pre>";
        print_r($professionalModel);
        echo "</pre>";
        foreach ($professionalModel as $key => $value) {
            echo "<pre>";
            print_r($value);
            echo "</pre>";
        }
        exit();*/
        $form = new ProfessionalForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/professional/edit', array('id' => $professionalId)));
        
        $form->loadValues($professionalModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            $form->setData($post);
            if ($form->isValid() && $this->getRequest()->getPost('submit')) {
                $professionalModel->addData($form->getInputFilter()->getValues());
                $professionalModel->save();

                $this->flashMessenger()->addSuccessMessage('Professional saved!');
                return $this->redirect()->toRoute('config/user/professional/edit', array('id' => $professionalId));
            }elseif($form->isValid() && $this->getRequest()->getPost('addNew')){
                $professionalModel->addData($form->getInputFilter()->getValues());
                $professionalModel->save();

                $this->flashMessenger()->addSuccessMessage('Professional saved!');
                return $this->redirect()->toRoute('config/user/professional/create');
            }

            $this->flashMessenger()->addErrorMessage('Professional can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }
}
