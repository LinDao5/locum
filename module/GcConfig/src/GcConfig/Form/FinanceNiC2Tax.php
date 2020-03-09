<?php
/**
 * Develop By Suraj Wasnik( suraj.wasnik0126@gmail.com ) at FuduGo Solutions
 */

namespace GcConfig\Form;

use Gc\Form\AbstractForm;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * FinanceTax form
 *
 * @category   Gc_Application
 * @FinanceTax    GcConfig
 * @subFinanceTax Form
 */
class FinanceNiC2Tax extends AbstractForm
{
    /**
     * Initialize FinanceTax form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'finance_year' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'c4_min_ammount_1' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c4_min_ammount_tax_1' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c4_min_ammount_2' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c4_min_ammount_tax_2' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c4_min_ammount_3' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c4_min_ammount_tax_3' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c2_min_amount' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),  
                'c2_tax' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ) 
                    
            )
        );

        $this->setInputFilter($inputFilter);

        $finance_year = new Element\Text('finance_year');
        $finance_year->setLabel('Financial Year')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',                    
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'finance_year');
        $this->add($finance_year);

        $c4_min_ammount_1 = new Element\Text('c4_min_ammount_1');
        $c4_min_ammount_1->setLabel('Class 4 amount One')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Min amount')
            ->setAttribute('id', 'c4_min_ammount_1');
        $this->add($c4_min_ammount_1);

        $c4_min_ammount_1_tax = new Element\Text('c4_min_ammount_tax_1');
        $c4_min_ammount_1_tax->setLabel('Class 4 amount One Tax %')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Tax %')
            ->setAttribute('id', 'c4_min_ammount_1_tax');
        $this->add($c4_min_ammount_1_tax);

        $c4_min_ammount_2_rate = new Element\Text('c4_min_ammount_2');
        $c4_min_ammount_2_rate->setLabel('Class 4 amount Two')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Max amount')
            ->setAttribute('id', 'c4_min_ammount_2');
        $this->add($c4_min_ammount_2_rate);

        $c4_min_ammount_2_rate_tax = new Element\Text('c4_min_ammount_tax_2');
        $c4_min_ammount_2_rate_tax->setLabel('Class 4 amount Two Tax %')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Tax %')
            ->setAttribute('id', 'c4_min_ammount_2_tax');
        $this->add($c4_min_ammount_2_rate_tax);

        $c4_min_ammount_3_rate = new Element\Text('c4_min_ammount_3');
        $c4_min_ammount_3_rate->setLabel('Class 4 amount Three')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Max amount')
            ->setAttribute('id', 'c4_min_ammount_3');
        $this->add($c4_min_ammount_3_rate);

        $c4_min_ammount_3_rate_tax = new Element\Text('c4_min_ammount_tax_3');
        $c4_min_ammount_3_rate_tax->setLabel('Class 4 amount Three Tax %')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Tax %')
            ->setAttribute('id', 'c4_min_ammount_3_tax');
        $this->add($c4_min_ammount_3_rate_tax);

        $c2_min_amount = new Element\Text('c2_min_amount');
        $c2_min_amount->setLabel('Class 2 amount')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Min amount')
            ->setAttribute('id', 'c2_min_amount');
        $this->add($c2_min_amount);

        $c2_tax = new Element\Text('c2_tax');
        $c2_tax->setLabel('Class 2 Tax %')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'Tax charge for whole year')
            ->setAttribute('id', 'c2_tax');
        $this->add($c2_tax);        
    }    
}
