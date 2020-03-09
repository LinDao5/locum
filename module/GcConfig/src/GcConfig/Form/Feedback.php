<?php
/**
 * This source file is part of FUDUGO. *
 * 
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 * @author     Suraj Wasnik (suraj.wasnik0126@gmail.com)
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.fudugo.com
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
class Feedback extends AbstractForm
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
                
                'fd_qus_fre' => array(
                    'required' => false,
                ),
                'fd_qus_emp' => array(
                    'required' => false,
                ),
                'fd_qus_sort_order' => array(
                    'required' => false,
                ), 
                'fd_qus_cat_id' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'fd_qus_status' => array(
                    'required' => false,
                ),  
                              
            )
        );

        $this->setInputFilter($inputFilter);



        $profession = new Element\Select('fd_qus_cat_id');
        $profession->setLabel('Category')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                    'id' => 'fd_qus_cat_id',
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
            ->setAttribute('id', 'fd_qus_cat_id');
        $this->add($profession);


        $fquestion = new Element\Text('fd_qus_fre');
        $fquestion->setLabel('Question For Freelancer')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'fd_qus_fre');
        $this->add($fquestion);

        $equestion = new Element\Text('fd_qus_emp');
        $equestion->setLabel('Question For Employer')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'fd_qus_emp');
        $this->add($equestion);

        $fd_qus_status = new Element\Select('fd_qus_status');
        $fd_qus_status->setLabel('Question Status')
            ->setLabelAttributes(
                array(
                    'class' => 'control-label col-lg-2',
                )
            );       
        $selectOptions  = array();
        $selectOptions[0] = 'Deactive';
        $selectOptions[1] = 'Active';

        $fd_qus_status->setValueOptions($selectOptions)
            ->setAttribute('class', 'form-control');             
        $this->add($fd_qus_status);        

        $sort_order = new Element\Number('fd_qus_sort_order');
        $sort_order->setLabel('Sort Order')
            ->setLabelAttributes(
                array(
                    'class' => 'required control-label col-lg-2',
                )
            )
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'fd_qus_sort_order');
        $this->add($sort_order);
        
    }

}
