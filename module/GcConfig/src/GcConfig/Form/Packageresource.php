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
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * package form
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Form
 */
class Packageresource extends AbstractForm
{
    /**
     * Initialize package form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'resource_key' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                
                'resource_value' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),                   
                ), 

                'resource_allow_count' => array(
                    'required' => false,
                ),
                             
            )
        );

        $this->setInputFilter($inputFilter);

        $resource_key = new Element\Text('resource_key');
        $resource_key->setLabel('Privilege Key')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'name');
        $this->add($resource_key);

        $resource_value = new Element\Text('resource_value');
        $resource_value->setLabel('Privilege')
            ->setLabelAttributes(
                array(
                    'class' => 'optional control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'resource_value');
        $this->add($resource_value);

        $resource_allow_count = new Element\Number('resource_allow_count');
        $resource_allow_count->setLabel('Privilege value')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'name');
        $this->add($resource_allow_count);

    }

    
}
