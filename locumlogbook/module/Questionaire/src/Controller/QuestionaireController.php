<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Questionaire\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\TableIdentifier;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Session;

class QuestionaireController extends AbstractActionController
{

    public function getAdapter(){
        return new Adapter(array(
            'driver' => 'Mysqli',
            'database' => 'umairc65_locumkit_qs',
            'username' => 'umairc65_logbook',
            'password' => 'Logbook10'
        ));
    }

    public function indexAction()
    {
    	return new ViewModel();
    }

    
    //quesitonaire1
    public function add1Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";
        
        return new ViewModel(array(
            'user_id' => $user_id
        ));
    }

    public function list1Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        
        session_start();
        if (isset($_SESSION['user_id'])){
            if ($user_id != $_SESSION['user_id']) return "error";
        }
        else $_SESSION['user_id'] = $user_id;
        
        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_1')->where(array('user_id' => $user_id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_lists' => $resultSet,
                'user_id' => $user_id
            ));
        }
    }

    public function add1postAction() {
        $data = $_POST['data'];
        $user_id = $this->params()->fromRoute('user_id');
        
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->insert()->into('tbl_questionaire_1')->values(array(
                'user_id' => $user_id,
                'practice_name' => $data['practice_name'],
                'date' => $data['date'] == "" ? NULL : $data['date'],
                'patient_id' => $data['patient_id'],
                'referred_to' => $data['referred_to'],
                'issue_hand' => $data['issue_hand'],
                'action_required' => $data['action_required'],
                'reminder_datetime' => $data['reminder_date'] == "" ? NULL : $data['reminder_date'],
                'notes' => $data['notes'],
                'completed_tick' => $data['completed_tick']
            ));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            $response = $this->getResponse();
            $response->setStatusCode(200);
            $result['success'] = '1';
            $response->setContent(json_encode($result));

            $headers = $response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');

            return $response;
        }

    }

    public function edit1postAction() {
        

        $data = $_POST['data'];
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            
            //get user id
            $update_array = array(
                'practice_name' => $data['practice_name'],
                'date' => trim($data['date']) == "" ? 'NULL' : "'".$data['date']."'",
                'patient_id' => $data['patient_id'],
                'referred_to' => $data['referred_to'],
                'issue_hand' => $data['issue_hand'],
                'action_required' => $data['action_required'],
                'reminder_datetime' => trim($data['reminder_date']) == "" ? 'NULL' : "'".$data['reminder_date']."'",
                'notes' => $data['notes'],
                'completed_tick' => $data['completed_tick']
            );
            try{
                $adapter->createStatement("update tbl_questionaire_1 set practice_name='".$data['practice_name']."', date=".$update_array['date'].", patient_id='".$data['patient_id']."', referred_to='".$data['referred_to']."', issue_hand='".$data['issue_hand']."', action_required='".$data['action_required']."', reminder_datetime=".$update_array['reminder_datetime'].", notes='".$data['notes']."', completed_tick='".$data['completed_tick']."' where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }
    public function delete1postAction() {
        
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            try{
                $adapter->createStatement("delete from tbl_questionaire_1 where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }

    public function edit1Action()
    {
        $id = $this->params()->fromRoute('id');
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_1')->where(array('id' => $id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_data' => $resultSet->current(),
                'editid' => $id,
                'user_id' => $user_id
            ));
        }
        
    }

    //questioniare2
    public function add2Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";
        
        return new ViewModel(array(
            'user_id' => $user_id
        ));
    }

    public function list2Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";
        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_2')->where(array('user_id' => $user_id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_lists' => $resultSet,
                'user_id' => $user_id
            ));
        }
    }

    public function add2postAction() {
        $data = $_POST['data'];
        
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->insert()->into('tbl_questionaire_2')->values(array(
                'user_id' => $user_id,
                'practice_name' => $data['practice_name'],
                'date' => $data['date'] == "" ? NULL : $data['date'],
                'issue' => $data['issue'],
                'notes' => $data['notes'],
                'reminder_datetime' => $data['reminder_date'] == "" ? NULL : $data['reminder_date'],
                'completed_tick' => $data['completed_tick']
            ));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $response = $this->getResponse();
            $response->setStatusCode(200);
            $result['success'] = '1';
            $response->setContent(json_encode($result));

            $headers = $response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');

            return $response;
        }

    }

    public function edit2Action()
    {
        $id = $this->params()->fromRoute('id');
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";
        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_2')->where(array('id' => $id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_data' => $resultSet->current(),
                'editid' => $id,
                'user_id' => $user_id
            ));
        }
        
    }

    public function edit2postAction() {
        

        $data = $_POST['data'];
        $id = $_POST['id'];

        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";


        $adapter = $this->getAdapter();
        if ($adapter != null){
            
            //get user id
            $update_array = array(
                'practice_name' => $data['practice_name'],
                'date' => trim($data['date']) == "" ? 'NULL' : "'".$data['date']."'",
                'issue' => $data['issue'],
                'notes' => $data['notes'],
                'reminder_datetime' => trim($data['reminder_date']) == "" ? 'NULL' : "'".$data['reminder_date']."'",
                'completed_tick' => $data['completed_tick']
            );
            try{
                $adapter->createStatement("update tbl_questionaire_2 set practice_name='".$data['practice_name']."', date=".$update_array['date'].", issue='".$data['issue']."', notes='".$data['notes']."', reminder_datetime=".$update_array['reminder_datetime'].", completed_tick='".$data['completed_tick']."' where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }
    public function delete2postAction() {
        
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            try{
                $adapter->createStatement("delete from tbl_questionaire_2 where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }


    
    //questionaire3

    public function add3Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        
        if ($user_id != $_SESSION['user_id']) return "error";
        return new ViewModel(array(
            'user_id' => $user_id
        ));
    }

    public function list3Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_3')->where(array('user_id' => $user_id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_lists' => $resultSet,
                'user_id' => $user_id
            ));
        }
    }

    public function add3postAction() {
        $data = $_POST['data'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";
        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->insert()->into('tbl_questionaire_3')->values(array(
                'user_id' => $user_id,
                'practice_name' => $data['practice_name'],
                'date' => $data['date'] == "" ? NULL : $data['date'],
                'issue' => $data['issue'],
                'notes' => $data['notes'],
                'action_required' => $data['action_required'],
                'reminder_datetime' => $data['reminder_date'] == "" ? NULL : $data['reminder_date'],
                'completed_tick' => $data['completed_tick']
            ));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $response = $this->getResponse();
            $response->setStatusCode(200);
            $result['success'] = '1';
            $response->setContent(json_encode($result));

            $headers = $response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');

            return $response;
        }

    }

    public function edit3Action()
    {
        $id = $this->params()->fromRoute('id');
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_3')->where(array('id' => $id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_data' => $resultSet->current(),
                'editid' => $id,
                'user_id' => $user_id
            ));
        }
        
    }

    public function edit3postAction() {
        

        $data = $_POST['data'];
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            
            //get user id
            $update_array = array(
                'practice_name' => $data['practice_name'],
                'date' => trim($data['date']) == "" ? 'NULL' : "'".$data['date']."'",
                'issue' => $data['issue'],
                'notes' => $data['notes'],
                'action_required' => $data['action_required'],
                'reminder_datetime' => trim($data['reminder_date']) == "" ? 'NULL' : "'".$data['reminder_date']."'",
                'completed_tick' => $data['completed_tick']
            );
            try{
                $adapter->createStatement("update tbl_questionaire_3 set practice_name='".$data['practice_name']."', date=".$update_array['date'].", issue='".$data['issue']."', notes='".$data['notes']."', action_required='".$data['action_required']."', reminder_datetime=".$update_array['reminder_datetime'].", completed_tick='".$data['completed_tick']."' where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }
    public function delete3postAction() {
        
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            try{
                $adapter->createStatement("delete from tbl_questionaire_3 where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }








    //questionaire4

    public function add4Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        return new ViewModel(array(
            'user_id' => $user_id
        ));
    }


    public function add4postAction() {
        $data = $_POST['data'];

        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->insert()->into('tbl_questionaire_4')->values(array(
                'user_id' => $user_id,
                'area' => $data['area'],
                'extended_services' => $data['extended_services'],
                'emergency_department' => $data['emergency_department'],
                'routine_referrals' => $data['routine_referrals']
            ));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $response = $this->getResponse();
            $response->setStatusCode(200);
            $result['success'] = '1';
            $response->setContent(json_encode($result));

            $headers = $response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');

            return $response;
        }
    }

    public function list4Action()
    {
        $adapter = $this->getAdapter();
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_4')->where(array('user_id' => $user_id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_lists' => $resultSet,
                'user_id' => $user_id
            ));
        }
    }

    public function edit4Action()
    {
        $id = $this->params()->fromRoute('id');

        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_4')->where(array('id' => $id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_data' => $resultSet->current(),
                'editid' => $id,
                'user_id' => $user_id
            ));
        }
        
    }

    public function edit4postAction() {
        

        $data = $_POST['data'];
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";


        $adapter = $this->getAdapter();
        if ($adapter != null){
            
            //get user id
            $update_array = array(
                'area' => $data['area'],
                'extended_services' => $data['extended_services'],
                'emergency_department' => $data['emergency_department'],
                'routine_referrals' => $data['routine_referrals']
            );
            $user_id = 1;
            try{
                $adapter->createStatement("update tbl_questionaire_4 set area='".$data['area']."', extended_services='".$data['extended_services']."', emergency_department='".$data['emergency_department']."', routine_referrals='".$data['routine_referrals']."' where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }
    public function delete4postAction() {
        
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            try{
                $adapter->createStatement("delete from tbl_questionaire_4 where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }

    

    //questionaire5
    public function add5Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        return new ViewModel(array(
            'user_id' => $user_id
        ));
    }

    public function list5Action()
    {
        $page_num = $this->params()->fromRoute('id');
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            
            $select = $sql->select()->from('tbl_questionaire_5')->where(array('user_id' => $user_id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            $questionaire_pagenum = 0;
            foreach ($resultSet as $item) {
                $questionaire_pagenum++;
            }
            
            $all_page_num = (int)($questionaire_pagenum / 4);
            if ($questionaire_pagenum % 4 != 0) $all_page_num++;

            $select = $sql->select()->from('tbl_questionaire_5')->where(array('user_id' => $user_id))->order('id DESC')->offset(($page_num - 1) * 4)->limit(4);

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);

            return new ViewModel(array(
                'q_lists' => $resultSet,
                'page_num' => $page_num,
                'all_page_num' => $all_page_num,
                'user_id' => $user_id
            ));
        }
    }

    public function edit5Action()
    {   
        $id = $this->params()->fromRoute('id');
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";    

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_5')->where(array('id' => $id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_data' => $resultSet->current(),
                'editid' => $id,
                'user_id' => $user_id
            ));
        }
        
    }

    public function add5postAction() {
        $data = $_POST['data'];
        $adapter = $this->getAdapter();
        
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->insert()->into('tbl_questionaire_5')->values(array(
                'user_id' => $user_id,
                'practice_name' => $data['practice_name'],
                'appointment_time_slots' => $data['appointment_time_slots'],
                'record_keeping' => $data['record_keeping'],
                'trial_set' => $data['trial_set'],
                'phoropter' => $data['phoropeter'],
                'test_chat_type' => $data['test_chart_type'],
                'visualfield_machinetype' => $data['visualfield_machinetype'],
                'funds_camera' => $data['funds_camera'],
                'oct' => $data['oct'],
                'slit_lamp_type' => $data['slit_lamp_type'],
                'reading_chart' => $data['reading_chart'],
                'stereo_test_type' => $data['stereo_test_type'],
                'colour_vision_type' => $data['colour_vision_type'],
                'pre_screening_procdure' => $data['pre_screening_procdure'],
                'is_there_do' => $data['is_there_do'],
                'contact_lenses' => $data['contact_lenses'],
                'handover_procdure' => $data['handover_procdure'],
                'any_patient_leaflets' => $data['any_patient_leaflets'],
                'primary_care_services' => $data['primary_care_services'],
                'shop_floor_staff_members' => $data['shop_floor_staff_members'],
                'no_of_clinics_running' => $data['no_of_clinics_running']
            ));



            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $response = $this->getResponse();
            $response->setStatusCode(200);
            $result['success'] = '1';
            $response->setContent(json_encode($result));

            $headers = $response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');

            return $response;
        }

    }

    public function edit5postAction() {
        

        $data = $_POST['data'];
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            
            //get user id
            $update_array = array(
                'practice_name' => $data['practice_name'],
                'appointment_time_slots' => $data['appointment_time_slots'],
                'record_keeping' => $data['record_keeping'],
                'trial_set' => $data['trial_set'],
                'phoropter' => $data['phoropeter'],
                'test_chat_type' => $data['test_chart_type'],
                'visualfield_machinetype' => $data['visualfield_machinetype'],
                'funds_camera' => $data['funds_camera'],
                'oct' => $data['oct'],
                'slit_lamp_type' => $data['slit_lamp_type'],
                'reading_chart' => $data['reading_chart'],
                'stereo_test_type' => $data['stereo_test_type'],
                'colour_vision_type' => $data['colour_vision_type'],
                'pre_screening_procdure' => $data['pre_screening_procdure'],
                'is_there_do' => $data['is_there_do'],
                'contact_lenses' => $data['contact_lenses'],
                'handover_procdure' => $data['handover_procdure'],
                'any_patient_leaflets' => $data['any_patient_leaflets'],
                'primary_care_services' => $data['primary_care_services'],
                'shop_floor_staff_members' => $data['shop_floor_staff_members'],
                'no_of_clinics_running' => $data['no_of_clinics_running']
            );
            try{
                $adapter->createStatement("update tbl_questionaire_5 set practice_name='".$data['practice_name']."', appointment_time_slots='".$data['appointment_time_slots']."', record_keeping='".$data['record_keeping']."', trial_set='".$data['trial_set']."', phoropter='".$data['phoropeter']."', test_chat_type='".$data['test_chart_type']."', visualfield_machinetype='".$data['visualfield_machinetype']."', funds_camera='".$data['funds_camera']."', oct='".$data['oct']."', slit_lamp_type='".$data['slit_lamp_type']."', reading_chart='".$data['reading_chart']."', stereo_test_type='".$data['stereo_test_type']."', colour_vision_type='".$data['colour_vision_type']."', is_there_do='".$data['is_there_do']."', contact_lenses='".$data['contact_lenses']."', handover_procdure='".$data['handover_procdure']."', any_patient_leaflets='".$data['any_patient_leaflets']."', primary_care_services='".$data['primary_care_services']."', shop_floor_staff_members='".$data['shop_floor_staff_members']."', no_of_clinics_running='".$data['no_of_clinics_running']."' where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }
    public function delete5postAction() {
        
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";


        $adapter = $this->getAdapter();
        if ($adapter != null){
            try{
                $adapter->createStatement("delete from tbl_questionaire_5 where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }


    //questionaire6
    public function add6Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        return new ViewModel(array(
            'user_id' => $user_id
        ));
    }
    
    public function list6Action()
    {
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_6')->where(array('user_id' => $user_id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_lists' => $resultSet,
                'user_id' => $user_id
            ));
        }
    }

    public function add6postAction() {
        $data = $_POST['data'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";

        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->insert()->into('tbl_questionaire_6')->values(array(
                'user_id' => $user_id,
                'practice_name' => $data['practice_name'],
                'date' => $data['date'] == "" ? NULL : $data['date'],
                'patient_id' => $data['patient_id'],
                'notes' => $data['notes'],
                'action_required' => $data['action_required'],
                'reminder_datetime' => $data['reminder_date'] == "" ? NULL : $data['reminder_date'],
                'completed_tick' => $data['completed_tick']
            ));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $response = $this->getResponse();
            $response->setStatusCode(200);
            $result['success'] = '1';
            $response->setContent(json_encode($result));

            $headers = $response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');

            return $response;
        }

    }

    public function edit6Action()
    {
        $id = $this->params()->fromRoute('id');
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";
        $adapter = $this->getAdapter();
        if ($adapter != null){
            $sql = new sql($adapter);
            //get user id
            $select = $sql->select()->from('tbl_questionaire_6')->where(array('id' => $id));

            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();

            $resultSet = new ResultSet;
            $resultSet->initialize($results);
            
            return new ViewModel(array(
                'q_data' => $resultSet->current(),
                'editid' => $id,
                'user_id' => $user_id
            ));
        }
        
    }

    public function edit6postAction() {
        

        $data = $_POST['data'];
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";      


        $adapter = $this->getAdapter();
        if ($adapter != null){
            
            //get user id
            $update_array = array(
                'practice_name' => $data['practice_name'],
                'date' => trim($data['date']) == "" ? 'NULL' : "'".$data['date']."'",
                'patient_id' => $data['patient_id'],
                'notes' => $data['notes'],
                'action_required' => $data['action_required'],
                'reminder_datetime' => trim($data['reminder_date']) == "" ? 'NULL' : "'".$data['reminder_date']."'",
                'completed_tick' => $data['completed_tick']
            );
            try{
                $adapter->createStatement("update tbl_questionaire_6 set practice_name='".$data['practice_name']."', date=".$update_array['date'].", patient_id='".$data['patient_id']."', notes='".$data['notes']."', action_required='".$data['action_required']."', reminder_datetime=".$update_array['reminder_datetime'].", completed_tick='".$data['completed_tick']."' where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }
    public function delete6postAction() {
        
        $id = $_POST['id'];
        $user_id = $this->params()->fromRoute('user_id');
        session_start();
        if ($user_id != $_SESSION['user_id']) return "error";       

        $adapter = $this->getAdapter();
        if ($adapter != null){
            try{
                $adapter->createStatement("delete from tbl_questionaire_6 where id=".$id)->execute();

                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "1";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
            catch(\Exception $e){
                $response = $this->getResponse();
                $response->setStatusCode(200);
                $result['success'] = "0";
                $response->setContent(json_encode($result));

                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/json');

                return $response;
            }
        }

    }



}
