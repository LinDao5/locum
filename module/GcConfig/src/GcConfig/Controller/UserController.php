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
use GcConfig\Form\User as UserForm;
use GcConfig\Form\UserForgotPassword as UserForgotForm;
use Gc\Mvc\Controller\Action;
use Gc\User;
use Gc\User\Role;
use Gc\User\Professional;
use Zend\View\Model\ViewModel;
use Zend\Validator\Identical;
use GcFrontend\Controller\JobmailController as MailController;
use GcFrontend\Controller\FunctionsController as FunctionsController;


/**
 * User controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */

class UserController extends Action
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
    protected $aclPage = array('resource' => 'settings', 'permission' => 'user');

    /**
     * List all roles
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $userCollection = new User\Collection();
        $userEmp = array();
        $userFre = array();
        $userAdm = array();
        if (isset($_GET['c']) && $_GET['c'] != '') {
            $catId = $_GET['c'];
        } else {
            $catId = 1;
        }
        $professions = $this->getProfession();
        foreach ($userCollection->getUsers() as $user) {
            if ($user->getUserAclRoleId() == 2) {
                if ($user->getUserAclProfessionId() == $catId) {
                    $userFre[] = $user;
                }
            } elseif ($user->getUserAclRoleId() == 3) {
                if ($user->getUserAclProfessionId() == $catId) {
                    $userEmp[] = $user;
                }
            } else {
                $userAdm[] = $user;
            }
        }

        //print_r($userOptFre);
        return array('userFre' => $userFre, 'userEmp' => $userEmp, 'userAdm' => $userAdm, 'professions' => $professions);
    }

    /**
     * Login user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function loginAction()
    {
        if ($this->getServiceLocator()->get('Auth')->hasIdentity()) {
            $redirect = $this->getRouteMatch()->getParam('redirect');
            if (!empty($redirect)) {
                return $this->redirect()->toUrl(base64_decode($redirect));
            }

            return $this->redirect()->toRoute('admin');
        }

        $this->layout()->setTemplate('layouts/one-page.phtml');
        $loginForm = new UserLogin();

        $post = $this->getRequest()->getPost();

        if ($this->getRequest()->isPost() and $loginForm->setData($post->toArray()) and $loginForm->isValid()) {
            $userModel = new User\Model();
            $redirect = $loginForm->getValue('redirect');

            if ($userModel->authenticate($post->get('login'), $post->get('password'))) {
                if (!empty($redirect)) {
                    return $this->redirect()->toUrl(base64_decode($redirect));
                }

                return $this->redirect()->toRoute('admin');
            }

            $this->flashMessenger()->addErrorMessage('Can not connect');
            //return $this->redirect()->toRoute('config/user/login', array('redirect' => $redirect));
        }

        $loginForm->get('redirect')->setValue($this->getRouteMatch()->getParam('redirect'));

        return array('form' => $loginForm);
    }

    /**
     * Forgot password action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forgotPasswordAction()
    {
        $this->layout()->setTemplate('layouts/one-page.phtml');
        $forgotPasswordForm = new UserForgotForm();
        $id = $this->getRouteMatch()->getParam('id');
        $key = $this->getRouteMatch()->getParam('key');
        if (!empty($id) and !empty($key)) {
            $userModel = User\Model::fromId($id);
            if ($userModel->getRetrievePasswordKey() == $key
                and strtotime('-1 hour') < strtotime($userModel->getRetrieveUpdatedAt())) {
                $forgotPasswordForm->setAttribute(
                    'action',
                    $this->url()->fromRoute(
                        'config/user/forgot-password-key',
                        array(
                            'id' => $id,
                            'key' => $key
                        )
                    )
                );

                $forgotPasswordForm->initResetForm();
                if ($this->getRequest()->isPost()) {
                    $post = $this->getRequest()->getPost();
                    $forgotPasswordForm->getInputFilter()
                        ->get('password_confirm')
                        ->getValidatorChain()
                        ->addValidator(new Identical($post['password']));
                    $forgotPasswordForm->setData($post->toArray());
                    if ($forgotPasswordForm->isValid()) {
                        $userModel->setPassword($forgotPasswordForm->getValue('password'));
                        $userModel->setRetrievePasswordKey(null);
                        $userModel->setRetrieveUpdatedAt(null);
                        $userModel->save();
                        $this->flashMessenger()->addSuccessMessage('Password changed!');
                    }

                    return $this->redirect()->toRoute('config/user/login');
                }

                return array('form' => $forgotPasswordForm);
            }

            return $this->redirect()->toRoute('admin');
        } else {
            $forgotPasswordForm->setAttribute('action', $this->url()->fromRoute('config/user/forgot-password'));
            $forgotPasswordForm->initEmail();
            if ($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                $forgotPasswordForm->setData($post->toArray());
                if ($forgotPasswordForm->isValid()) {
                    $userModel = new User\Model();
                    if ($userModel->sendForgotPasswordEmail($forgotPasswordForm->getValue('email'))) {
                        $this->flashMessenger()->addSuccessMessage(
                            'Message sent, you have one hour to change your password!'
                        );
                        return $this->redirect()->toRoute('config/user/login');
                    }
                }
            }
        }

        return array('form' => $forgotPasswordForm);
    }

    /**
     * Logout action
     *
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $this->getServiceLocator()->get('Auth')->clearIdentity();
        return $this->redirect()->toRoute('admin');
    }

    /**
     * Create user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('config/user/create'));
        $form->passwordRequired();
        $post = $this->getRequest()->getPost()->toArray();
        if ($this->getRequest()->isPost()) {
            $form->setData($post);
            $form->getInputFilter()
                ->get('password_confirm')
                ->getValidatorChain()
                ->addValidator(new Identical(empty($post['password']) ? null : $post['password']));

            if ($form->isValid() && $this->getRequest()->getPost('submit')) {
                $userModel = new User\Model();
                $userModel->setData($post);
                $userModel->setPassword($post['password']);
                $userModel->save();
                /* Set user to use 1 onth free service */
                $UserPackage = new User\UserPackage();
                $UserPackage->insertPackageInfo($userModel->getId(), $post['user_acl_package_id']);

                /*send email to activate user*/
                if ($post['active'] == 1 && $post['user_acl_role_id'] == 3) {
                    $mailController = new MailController();
                    $mailController->sendActivationNotification($post['email'], $post['firstname'], $post['lastname'], $post['login']);

                }

                $this->flashMessenger()->addSuccessMessage('User saved!');

                return $this->redirect()->toRoute('config/user/edit', array('id' => $userModel->getId()));
            } elseif ($form->isValid() && $this->getRequest()->getPost('addNew')) {
                $userModel = new User\Model();
                $userModel->setData($post);
                $userModel->setPassword($post['password']);
                $userModel->save();
                /* Set user to use 1 month free service */
                $UserPackage = new User\UserPackage();
                $UserPackage->insertPackageInfo($userModel->getId(), $post['user_acl_package_id']);
                $this->flashMessenger()->addSuccessMessage('User saved!');

                return $this->redirect()->toRoute('config/user/create');
            } elseif ($form->isValid() && $this->getRequest()->getPost('returnToList')) {
                $userModel = new User\Model();
                $userModel->setData($post);
                $userModel->setPassword($post['password']);
                $userModel->save();
                /* Set user to use 1 month free service */
                $UserPackage = new User\UserPackage();
                $UserPackage->insertPackageInfo($userModel->getId(), $post['user_acl_package_id']);
                $this->flashMessenger()->addSuccessMessage('User saved!');
                return $this->redirect()->toRoute('config/user');
            }

            $this->useFlashMessenger();
            $this->flashMessenger()->addErrorMessage('User can not be saved');
        }

        return array('form' => $form);
    }

    /**
     * Delete user
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {


        $userid = $this->getRouteMatch()->getParam('id');
        $functionsController = new FunctionsController();
        $functionsController->insertdeleteduser($userid);


        $user = User\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($user) and $user->getRole()->getName() !== Role\Model::PROTECTED_NAME and $user->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'The user has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'User does not exists'));
    }

    /**
     * Edit user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {

        $userId = $this->getRouteMatch()->getParam('id');
//        $userModel = User\Model::fromId($userId);
        $userModel = new User\Model();
        $userModel = $userModel->fromId_i($userId);

//        echo '<pre>';
//        print_r($userModel);
//        echo '</pre>';


        if (empty($userModel)) {
            $this->flashMessenger()->addErrorMessage("Can't edit this user");
            return $this->redirect()->toRoute('config/user');
        }

        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('config/user/edit', array('id' => $userId)));
        $form->loadValues($userModel);
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost()->toArray();

            $userModelObj = new User\Model();
            $checkEmail = $userModelObj->checkEmailexist($post['email'], $userId);

            if (empty($checkEmail)) {

                if (!empty($post['password'])) {
                    $form->passwordRequired();
                    $form->getInputFilter()
                        ->get('password_confirm')
                        ->getValidatorChain()
                        ->addValidator(new Identical($post['password']));
                }

                $form->setData($post);

                if ($form->isValid() && $this->getRequest()->getPost('submit')) {

                    $userModel->addData($post);

//                    var_dump($post);
//                    exit;
                    $userModel->setActive(empty($post['active']) ? false : $post['active']);

                    if (!empty($post['password'])) {
                        $userModel->setPassword($post['password']);

                    }
                    $userModel->save();

                    /*send email to activate user*/
                    if ($post['active'] == 1 && $post['user_acl_role_id'] == 3) {
                        $mailController = new MailController();
                        $mailController->sendActivationNotification($post['email'], $post['firstname'], $post['lastname'], $post['login']);
                    }

                    /* Set user to use 1 onth free service */
                    if ($post['is_free'] == 1) {
                        $UserPackage = new User\UserPackage();
                        $UserPackage->allowOneMonthFree($userModel->getId());
                    }
                    $this->flashMessenger()->addSuccessMessage('This user has been saved.');
                    return $this->redirect()->toRoute('config/user/edit', array('id' => $userId));
                } elseif ($form->isValid() && $this->getRequest()->getPost('addNew')) {
                    $userModel->addData($post);
                    $userModel->setActive(
                        empty($post['active']) ?
                            false :
                            $post['active']
                    );

                    if (!empty($post['password'])) {
                        $userModel->setPassword($post['password']);
                    }

                    $userModel->save();
                    $this->flashMessenger()->addSuccessMessage('This user has been saved');
                    return $this->redirect()->toRoute('config/user/create');
                } elseif ($form->isValid() && $this->getRequest()->getPost('returnToList')) {
                    $userModel->addData($post);
                    $userModel->setActive(
                        empty($post['active']) ?
                            false :
                            $post['active']
                    );

                    if (!empty($post['password'])) {
                        $userModel->setPassword($post['password']);
                    }

                    $userModel->save();
                    $this->flashMessenger()->addSuccessMessage($post['firstname'] . ' ' . $post['lastname'] . '  user update has been saved');
                    return $this->redirect()->toRoute('config/user');
                }
                $this->flashMessenger()->addErrorMessage('User can not be saved');
            } else {
                $this->flashMessenger()->addErrorMessage('User can not be saved');
                return $this->redirect()->toRoute('config/user/edit', array('id' => $userId));

            }

        }

        return array('form' => $form);
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
        return $professions;
    }
}