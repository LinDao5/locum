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
use Gc\User\Permission;
use Gc\User\Role\Collection as RoleCollection;
use Gc\User\Professional\Collection as ProfessionalCollection;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Question form
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Form
 */
class Question extends AbstractForm
{
    /**
     * Initialize Question form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(                
                'user_acl_profession_id' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'fquestion' => array(
                    'required' => false,
                ),
                'equestion' => array(
                    'required' => false,
                ),
                'sort_order' => array(
                    'required' => false,
                ),   
                'required_status' => array(
                    'required' => false,
                ), 
                'type_key' => array(
                    'required' => false,
                ),
                'type_value' => array(
                    'required' => false,
                ),
                'type_value_range_unit' => array(
                    'required' => false,
                ),
                'type_value_range_type' => array(
                    'required' => false,
                ),
                              
            )
        );

        $this->setInputFilter($inputFilter);


        /*$role = new Element\Select('user_acl_role_id');
        $role->setLabel('Role')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
        $roleCollection = new RoleCollection();
        $rolesList      = $roleCollection->getRoles();
        $selectOptions  = array();
        foreach ($rolesList as $roleModel) {
            if($roleModel->getId() == 2 || $roleModel->getId() == 3){
                $selectOptions[$roleModel->getId()] = $roleModel->getName();
            }
        }

        $role->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'user_acl_role_id');
        $this->add($role);*/


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
            $selectOptions[$professionsModel->getId()] = $professionsModel->getName();
        }

        $profession->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'user_acl_profession_id');
        $this->add($profession);


        $fquestion = new Element\Text('fquestion');
        $fquestion->setLabel('Question For Freelancer')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'fquestion');
        $this->add($fquestion);

        $equestion = new Element\Text('equestion');
        $equestion->setLabel('Question For Employer')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'equestion');
        $this->add($equestion);

        $required_status = new Element\Select('required_status');
        $required_status->setLabel('Is required')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label col-lg-2',
                )
            );       
        $selectOptions  = array();
        $selectOptions[0] = 'No';
        $selectOptions[1] = 'Yes';
        $required_status->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');             
        $this->add($required_status);

        $type_key = new Element\Select('type_key');
        $type_key->setLabel('Answer Type')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            );
       
        $selectOptions  = array();
        $selectOptions[1] = 'Text Field';
        $selectOptions[2] = 'Select Option';
        $selectOptions[3] = 'Multi Select Option';
        $selectOptions[4] = 'Comparative Option';
        $selectOptions[5] = 'Range Option';
        $selectOptions[6] = 'Yes/No Option With Yes';
        

        $type_key->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');
             
        $this->add($type_key);
        

        

        $type_value = new Element\Text('type_value[]');
        $type_value->setLabel('Option Value')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'type_value');
        $this->add($type_value);

        $type_value_range_unit = new Element\Text('type_value_range_unit');
        $type_value_range_unit->setLabel('Range Unit ')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'type_value_range_unit')
            ->setAttribute('placeholder', 'Enter Range Unit');
        $this->add($type_value_range_unit);

        $type_value_range_type = new Element\Select('type_value_range_type');
        $type_value_range_type->setLabel('Range Type')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label col-lg-2',
                )
            );       
        $selectOptions  = array();
        $selectOptions[0] = '--Select--';
        $selectOptions[1] = 'Greater than';
        $selectOptions[2] = 'Greater than OR equal to';
        $selectOptions[3] = 'Less than ';
        $selectOptions[4] = 'Less than OR equal to';
        $selectOptions[5] = 'Equal to';
        $type_value_range_type->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control')             
            ->setAttribute('id', 'type_value_range_type');             
        $this->add($type_value_range_type);

        $sort_order = new Element\Number('sort_order');
        $sort_order->setLabel('Sort Order')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'sort_order');
        $this->add($sort_order);
        
    }

}
