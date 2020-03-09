<?php
/**
 * Develop by Suraj Wasnik (suraj.wasnik0126@gmail.com) In FUDUGO SOLUTIONS PVT. LTD.
 */

namespace GcConfig\Controller;

use Gc\Mvc\Controller\Action;
use Gc\User\Question;
use Gc\User\Professional;
use GcConfig\Form\Question as QuestionForm;

/**
 * Question controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class QuestionController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'question');

    /**
     * List all Questions
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
       
        $questionCollection = new Question\Collection();
        $questionsEmp  = array();
        $questionsFre  = array();   
        if (isset($_GET['c']) && $_GET['c'] != '') {
            $catId = $_GET['c'];       
        }else{
            $catId = 1;
        }   
        $professions = $this->getProfession();  
        foreach ($questionCollection->getQuestions() as $question) {

            if ($question->getName() !== Question\Model::PROTECTED_NAME) {
                if (!empty($question)) {
                    if ($question->getEquestion() && $question->getEquestion() != '') {
                        if($question->getCatId() == $catId) {
                            $questionsEmp[] = $question;
                        }
                    }
                    if($question->getFquestion() && $question->getFquestion() != ''){
                        if($question->getCatId() == $catId) {
                            $questionsFre[] = $question;      
                        }
                    }

                }
            }
        }
        return array('questionsEmp' => $questionsEmp ,'questionsFre' => $questionsFre, 'professions' => $professions);
    }

    /**
     * Create question
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new QuestionForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/question/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid() && $this->getRequest()->getPost('submit')) {
                $questionModel = new Question\Model();
                $questionModel->addData($form->getInputFilter()->getValues());
                $questionModel->save();
               
                $this->flashMessenger()->addSuccessMessage('Question saved!');
                return $this->redirect()->toRoute('config/user/question/edit', array('id' => $questionModel->getId()));
            }elseif($form->isValid() && $this->getRequest()->getPost('addNew')){
                $questionModel = new Question\Model();
                $questionModel->addData($form->getInputFilter()->getValues());
                $questionModel->save();               
                $this->flashMessenger()->addSuccessMessage('Question saved!');
                return $this->redirect()->toRoute('config/user/question/create');
            }

            $this->flashMessenger()->addErrorMessage('Question can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }

    /**
     * Delete question
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {
        $questionModel = Question\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($questionModel) and $questionModel->getName() !== Question\Model::PROTECTED_NAME and $questionModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Question has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Question does not exists'));
    }

    /**
     * Edit question
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $questionId = $this->getRouteMatch()->getParam('id');

        $questionModel = Question\Model::fromId($questionId);
        if (empty($questionModel) or $questionModel->getName() === Question\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this question");
            return $this->redirect()->toRoute('config/user/question');
        }
        /*echo "<pre>";
        print_r($questionModel);
        echo "</pre>";
        foreach ($questionModel as $key => $value) {
            echo "<pre>";
            print_r($value);
            echo "</pre>";
        }
        exit();*/
        $form = new QuestionForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/question/edit', array('id' => $questionId)));
        /*print_r($questionModel);
        exit();*/
        $form->loadValues($questionModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();

            $form->setData($post);

            /*echo $form->isValid();
            echo "<pre>";
            print_r($post);
            echo "</pre>";*/
            

            if ($form->isValid()) {
                $questionModel->addData($form->getInputFilter()->getValues());
                $questionModel->save();

                $this->flashMessenger()->addSuccessMessage('Question saved!');
                return $this->redirect()->toRoute('config/user/question/edit', array('id' => $questionId));
            }

            $this->flashMessenger()->addErrorMessage('question can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }

    public function getProfession()
    {
        $professionCollections = new Professional\Collection();
        $professions = array();
        foreach ($professionCollections->getProfessionals() as $profession) {
            $professions[] = $profession;
        }
        return   $professions;
    }
}