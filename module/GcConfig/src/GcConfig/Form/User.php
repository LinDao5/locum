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
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Form;

use Gc\Form\AbstractForm;
use Gc\User\Role\Collection as RoleCollection;
use Gc\User\Professional\Collection as ProfessionalCollection;
use Gc\User\Package\Collection as PackageCollection;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\Session\Container as SessionContainer;

/**
 * User form
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Form
 */
class User extends AbstractForm
{
    /**
     * Initialize User form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'email' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array('name' => 'email_address'),
                    ),
                ),
                'login' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                        array(
                            'name' => 'db\\no_record_exists',
                            'options' => array(
                                'table' => 'user',
                                'field' => 'login',
                                'adapter' => $this->getAdapter(),
                            ),
                        ),
                    ),
                ),
                'lastname' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'firstname' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'user_acl_role_id' => array(
                    'required' => false,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'user_acl_profession_id' => array(
                    'required' => false,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'user_acl_package_id' => array(
                    'required' => false,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'active' => array(
                    'allow_empty' => true,
                ),
                'is_free' => array(
                    'allow_empty' => true,
                ),
                'view' => array(
                    'allow_empty' => true,
                ),
            )
        );

        $this->setInputFilter($inputFilter);

        $email = new Element\Text('email');
        $email->setLabel('Email')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'email');
        $this->add($email);

        $login = new Element\Text('login');
        $login->setLabel('Login')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'login');
        $this->add($login);

        $password = new Element\Text('password');
        $password->setLabel('Password')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('autocomplete', 'off')
            ->setAttribute('id', 'password');
        $this->add($password);

        $passwordConfirm = new Element\Text('password_confirm');
        $passwordConfirm->setLabel('Password Confirm')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('autocomplete', 'off')
            ->setAttribute('id', 'password_confirm');
        $this->add($passwordConfirm);

        $lastname = new Element\Text('lastname');
        $lastname->setLabel('Lastname')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'lastname');
        $this->add($lastname);

        $firstname = new Element\Text('firstname');
        $firstname->setLabel('Firstname')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'firstname');
        $this->add($firstname);

        $active = new Element\Select('active');
        $active->setLabel('User Status')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2'
                )
            );
        $selectOptions  = array();
        $selectOptions[0] = 'Disable'; 
        $selectOptions[1] = 'Active'; 
        $selectOptions[2] = 'Block'; 
        $active->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'active');
            
        $this->add($active);

        $is_free = new Element\Select('is_free');
        $is_free->setLabel('Allow one month free')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label col-lg-2'
                )
            );
        $selectOptions  = array();
        $selectOptions[0] = 'No'; 
        $selectOptions[1] = 'Yes'; 
        
        $is_free->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'is_free');
            
        $this->add($is_free);

        // by lindao
        $cet = new Element\Text('cet');
        $cet->setLabel('CET')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'cet');
        $this->add($cet);

        $view = new Element\Text('view');
        $view->setLabel('view')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'view');
        $this->add($view);

        // lindao end

        $role = new Element\Select('user_acl_role_id');
        $role->setLabel('Role')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
        $roleCollection = new RoleCollection();
        $rolesList      = $roleCollection->getRoles();
        $selectOptions  = array();

        $session   = $_SESSION['Zend_Auth_Backend']->storage;

        //if($session->getUserAclRoleId() == 1){
           foreach ($rolesList as $roleModel) {
               $selectOptions[$roleModel->getId()] = $roleModel->getName();
           }
        //}else{
            //$selectOptions[$roleModel->getId()] = $roleModel->getName();
        //}
        $role->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');

        $this->add($role);
/*========================================================================*/
        $profession = new Element\Select('user_acl_profession_id');
        $profession->setLabel('Category')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
        $professionCollection = new ProfessionalCollection();
        $professionsList      = $professionCollection->getProfessionals();
        $selectOptions  = array();
        foreach ($professionsList as $professionsModel) {
            $selectOptions[0] = '-- Select the category --';
            $selectOptions[$professionsModel->getId()] = $professionsModel->getName();
        }

        $profession->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');
        $this->add($profession);

        $package = new Element\Select('user_acl_package_id');
        $package->setLabel('Package')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
        $packageCollection = new PackageCollection();
        $packagesList      = $packageCollection->getPackages();
        $selectOptions  = array();
        foreach ($packagesList as $packagesModel) {
            $selectOptions[0] = '-- Select the package --';
            $selectOptions[$packagesModel->getId()] = $packagesModel->getName();
        }

        $package->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');


        $this->add($package);
    }

    /**
     * Set if yes or no password is required when user click on Save
     *
     * @return User
     */
    public function passwordRequired()
    {
        $filter = $this->getInputFilter();
        $filter->add(
            array(
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'password'
        );

        $filter->add(
            array(
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'password_confirm'
        );

        return $this;
    }
}
