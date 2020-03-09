<?php
	/**
	* Develop by Rizwana Ansari(rizwanawork786@gmail.com)
	*/
	namespace FudugoApp\Controller\Job;
	use Gc\Mail;
	use Gc\Registry;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use FudugoApp\Controller\Store\StoreController as StoreController;
	use GcFrontend\Controller\JobmailController as MailController;
	use GcFrontend\Controller\FunctionsController as FunctionsController;
	use GcFrontend\Controller\EndecryptController as Endecrypt;
	use GcFrontend\Helper\FinanceHelper as FinanceHelper;
	use Gc\User\Finance\Model as FinanceModel;
	use Gc\User\Finance\PrivateJobModel as PrivateJobFinanceModel; 
	use Gc\User\Finance\Income\Model as IncomeModel;
	use GcFrontend\Controller\PackagePrivilegesController;
	Class PrivateJobController
	{
		public function manage_private_job($user_data)
		{
			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			//call  Package Privileges Controller to check eligibility of package resources
			$packagePrivilegesController = new PackagePrivilegesController();	
	
			$uid = isset($user_data['uid']) ? $user_data['uid'] : '';

			if($user_data['type']=='addNew'){
				 $results['getdata']=$this->insert_private_job($user_data,$adapter);
			}
			if($user_data['type']=='edit'){				
				 $results['getdata']=$this->update_private_job($user_data,$adapter);
			}
			if($user_data['type']=='delete'){				
				 $results['getdata']=$this->delete_private_job($user_data,$adapter);
			}
			if($user_data['type']=='get'){				
				$results['getdata']=$this->get_private_job($user_data,$adapter);
			}

			$sql_job_insert = "SELECT pv_id, f_id, emp_name, emp_email, priv_job_title, priv_job_rate, priv_job_location, priv_job_start_date, priv_create_date, priv_update_date, status FROM freelancer_private_job WHERE f_id='$uid' ORDER BY priv_job_start_date DESC";
			$job_obj = $adapter->query($sql_job_insert, $adapter::QUERY_MODE_EXECUTE);
			$priv_jod_data_array = $job_obj->toArray();
			if(!empty($priv_jod_data_array)){
				foreach ($priv_jod_data_array as $key => $priv_jod_data) {
					$priv_jod_data_array[$key]['priv_job_start_date'] = date('d/m/Y', strtotime($priv_jod_data['priv_job_start_date']));
				}
			}

			$results['results'] = $priv_jod_data_array;
			$is_user_pkg_allow_add_private_job = $packagePrivilegesController->getCurrentPackagePrivilegesResources('add_private_job',$uid,$adapter);
			$results['results_check_previliage'] = $is_user_pkg_allow_add_private_job;	
	
			return json_encode($results);
		}
		/****Insert Private Job ***/
		public function insert_private_job($job_data,$adapter)
		{
			
			$uid = isset($job_data['job_info']['id']) ? $job_data['job_info']['id']: ''; 
			$name = isset($job_data['job_info']['name']) ? $job_data['job_info']['name'] : '';
			$rate = isset($job_data['job_info']['rate']) ? $job_data['job_info']['rate'] : '';
			$title = isset($job_data['job_info']['title']) ? $job_data['job_info']['title'] : '';
			$location = isset($job_data['job_info']['location']) ? $job_data['job_info']['location'] : '';
			$date = isset($job_data['job_info']['date']) ? $job_data['job_info']['date'] : '';
			$date1 = str_replace('/', '-', $date1);	
			$start_date_new=date('Y-m-d', strtotime($date));
			$sql_job_insert = "INSERT INTO freelancer_private_job (f_id, emp_name, priv_job_title, priv_job_rate, priv_job_location, priv_job_start_date, priv_create_date, priv_update_date, status) VALUES('$uid','$name','$title','$rate','$location','$start_date_new',NOW(),NOW(),0)";
			$job_obj = $adapter->query($sql_job_insert, $adapter::QUERY_MODE_EXECUTE);
			return $job_data;
		}
		/****Edit/Update Private Job ***/		
		public function update_private_job($job_data, $adapter)
		{
			$pid = isset($job_data['job_info']['pid']) ? $job_data['job_info']['pid']: ''; 
			$name = isset($job_data['job_info']['name']) ? $job_data['job_info']['name'] : '';
			$rate = isset($job_data['job_info']['rate']) ? $job_data['job_info']['rate'] : '';
			$title = isset($job_data['job_info']['title']) ? $job_data['job_info']['title'] : '';
			$location = isset($job_data['job_info']['location']) ? $job_data['job_info']['location'] : '';
			$date = isset($job_data['job_info']['date']) ? $job_data['job_info']['date'] : '';
			$date1 = str_replace('/', '-', $date1);	
			$start_date_new=date('Y-m-d', strtotime($date));
			$sql_job_insert = "UPDATE freelancer_private_job  SET emp_name='$name', priv_job_title='$title', priv_job_rate='$rate', priv_job_location='$location', priv_job_start_date='$start_date_new', priv_update_date=NOW() WHERE pv_id='$pid'";
			$job_obj = $adapter->query($sql_job_insert, $adapter::QUERY_MODE_EXECUTE);
			return $job_obj;
		}

		/****Delete Private Job ***/
		public function delete_private_job($job_data, $adapter){
			$pid = isset($job_data['pid']) ? $job_data['pid']: '';
			$sql_job_insert = "DELETE FROM freelancer_private_job WHERE pv_id='$pid'";
			$job_obj = $adapter->query($sql_job_insert, $adapter::QUERY_MODE_EXECUTE);
			return $job_obj;
		}
		/****get By job Id Private Job ***/
		public function get_private_job($job_data, $adapter){
			$pid = isset($job_data['pid']) ? $job_data['pid']: '';
			$sql_job_insert = "SELECT emp_name, priv_job_title, priv_job_rate, priv_job_location,priv_job_start_date FROM freelancer_private_job WHERE pv_id='$pid' ORDER BY priv_job_start_date DESC";
			$job_obj = $adapter->query($sql_job_insert, $adapter::QUERY_MODE_EXECUTE);
			$results = $job_obj->toArray();	
			return $results[0];
		}

		/* == View Private Job == */
		public function view_private_job($data){
			$helpController = new HelperController();			
			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$f_id 			= $data['user_id'];
			$pj_id 			= $data['job_id'];
			$sql_job 		= "SELECT * FROM freelancer_private_job WHERE pv_id='$pj_id' AND f_id = '$f_id'";
			$job_obj 		= $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);
			$results 		= $job_obj->toArray();	
			if (!empty($results[0])) {
				$results[0]['priv_job_rate'] = $helpController->formate_price($results[0]['priv_job_rate']);
			}		
			return json_encode($results);	
		}
 
		public function attend_private_job($data){
			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$functionController     = new FunctionsController();
			$financeHelper  = new FinanceHelper();
			$privateJobFinanceModel = new PrivateJobFinanceModel();
			$financeModel           = new FinanceModel();
			$incomefinance          = new IncomeModel();
			$f_id 			= $data['user_id'];
			$pj_id 			= $data['job_id'];

			$sql_check 		= "SELECT * FROM freelancer_private_job WHERE pv_id='$pj_id' AND f_id = '$f_id' AND status = 2 ";
			$job_obj 		= $adapter->query($sql_check, $adapter::QUERY_MODE_EXECUTE);
			$results 		= $job_obj->toArray();
			if (!empty($results[0])) {
				$sql_job 		= "UPDATE freelancer_private_job SET status = 5 WHERE pv_id='$pj_id' AND f_id = '$f_id'";
				$job_obj 		= $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);

				/* Add income to finance module */
				$privateJobObj = $functionController->getPrivateJobInfo($adapter, $pj_id);
				if (!empty($privateJobObj)) {            
					$pJobId       =  $privateJobObj->pv_id; 
					$pJobFid      =  $privateJobObj->f_id; 
					$pJobEmpName  =  $privateJobObj->emp_name; 
					$pJobRate     =  $privateJobObj->priv_job_rate; 
					$pJobDate     =  $privateJobObj->priv_job_start_date;
					$pJobLocation =  $privateJobObj->priv_job_location;

					$check_finance = $financeHelper->checkFinanceincome($pJobId,$pJobFid,$pJobDate,1,2);           
					if (empty($check_finance)) {              
						$financeSaveArray = array(
							'job_id'        => $pJobId,
							'fre_id'        => $pJobFid,
							'emp_name'      => $pJobEmpName,
							'job_rate'      => $pJobRate,
							'job_date'      => date('d/m/Y',strtotime( $pJobDate ))
						);
						$insert = $privateJobFinanceModel->save($financeSaveArray);

						/*---start---*/
						$financeIncomeSaveArray = array(
							'job_id'    => $pJobId,
							'job_type'   => 2,
							'fre_id'    => $pJobFid,
							'emp_id'    => 0,
							'job_rate'  => $pJobRate,
							'job_date'  => $pJobDate,
							'location'  => $pJobLocation,
							'store'     => $pJobEmpName,
							'supplier'  => $pJobEmpName,
							'income_type'  => 1,
							'invoice_id'  => 0,
							'status'  => 1
						);
						$res =  $incomefinance->save($financeIncomeSaveArray);
						if($res){
							$finance_trans = array(
							'trans_type_id' => $res ,
							'trans_type'  => '1'
							);
							$financeModel->save($finance_trans);
						}
						/*---end---*/
					}
				}


				$response['response'] = "Attendance confirmed.";
			}else{
				$response['response'] = "Attendance is already done.";
			}
			return json_encode($response);
		}

	}