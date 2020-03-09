<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Finance;
	use GcFrontend\Controller\DbController as DbController;
	use GcFrontend\Helper\FinanceHelper as FinanceHelper; 
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use FudugoApp\Controller\Job\JobController as JobController;
	use Gc\User\Finance\Income\Model as IncomeModel;
	use Gc\User\Finance\Expense\Model as ExpenseModel; 
	use Gc\User\Finance\Expense\Model as AddSupplier; 
	use Gc\User\Finance\Expense\CategoryCollection as CategoryCollection;
	use GcConfig\Controller\FinanceController as FinanceController;
	use Gc\User\Finance\Model as FinanceModel;
	use Gc\User\Finance\Employertrans\Model as EmployertransModel;
	use Gc\User\Collection;

	Class IncomeAppController{
		public function manage_income($user_data)
		{
			$dbConfig 	= new DbController();
   			$adapter 	= $dbConfig->locumkitDbConfig();
   			$page_id 	= $user_data['page_id'];			
			$finance_records = array();				

			switch ($page_id) {
				case 'getJobbyId':					
					$job_id = $user_data['formInfo']['job_id'];
					$finance_records = $this->get_job_details_by_id($job_id, $adapter);
					break;
				case 'update-transaction':					
					$trans_id 	= $user_data['trans_id'];
					$trans_date = $user_data['trans_date'];
					$trans_type = $user_data['trans_type'];
					$show 		= $user_data['show_type'];		
					$user_data  = $user_data['formInfo'];			
					$action 	= isset($user_data['action']) ? $user_data['action'] : '';					
					$this->updateTransaction($trans_id,$trans_date,$trans_type, $adapter);
					if ($action=="income-by-area") {						
						$finance_records = $this->get_income_by_area($user_id);	
					}else{
						$finance_records = $this->getAllTransaction($user_id, $cat=null, $show, $adapter);
					}					
					break;	
				case 'insert-income':	
				    $trans_id 	= ($user_data['tran_id'] != '') ? $user_data['tran_id'] : null;					    
				    $trans_type = $user_data['trans_type'];	
				    $user_data  = $user_data['formInfo'];	
					$finance_records = $this->insertTransaction($user_data,$trans_type,$trans_id, $adapter);	
					break;
				case 'insert-cost':	
				    $trans_type = $user_data['trans_type'];	
				    $tran_id  	= $user_data['tran_id'];				    
				    $user_data  = $user_data['formInfo'];				    
				   	
					$finance_records = $this->insertCostTransaction($user_data,$trans_type,$tran_id, $adapter);	
					break;
				case 'manage-supplier':	
				    $trans_type = $user_data['trans_type'];	
				    $user_data=$user_data['formInfo'];
					$finance_records = $this->manageSupplier($user_data,$trans_type, $adapter);	
					break;
				case 'delete-income-expense':
					$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';					
					//$finance_records = $this->get_income_by_category($user_id, $filter_cat);
					break;				
				case 'edit-transaction':
					$trans_id 	= $user_data['tra_id'];
					$trans_type = $user_data['tra_type'];					
					$user_type 	= $user_data['user_type'];					
					$user_id 	= $user_data['user_id'];					
					$finance_records = $this->getTransactionDetails($trans_id,$trans_type,$user_id,$user_type);
					break;	
				case 'delete-transaction':
					$trans_id 	= $user_data['trans_id'];
					$trans_type = $user_data['trans_type'];					
					$user_id 	= $user_data['user_id'];					
					$finance_records = $this->deleteTransaction($trans_id,$trans_type,$user_id);
					break;				
			}
			return json_encode($finance_records);
		}

		/***Update income /expenses  ***/
		public function updateTransaction($trans_id,$trans_date,$trans_type, $adapter)
		{
			$incomeModel 	= new IncomeModel();
			$expenseModel 	= new ExpenseModel(); 
			if ($trans_type == 1 || $trans_type == 'Income' ){
				# Income
				$incomeData = array(
		    		'bank' 			=> 1, 
		    		'bank_date' 	=> $trans_date, 
		    	);
		   		$incomeModel->inupdate_bank($trans_id,$incomeData);
			}elseif ($trans_type == 2 || $trans_type == 'Expense'){
				#Expense
				$incomeData = array(
		    		'bank' 			=> 1, 
		    		'bank_date' 	=> $trans_date, 
		    	);
		    	$expenseModel->exupdate_bank($trans_id,$incomeData);	
			}			
		}

		public function insertTransaction($income_data,$trans_type, $trans_id = null, $adapter)
		{					
			$incomeModel 	= new IncomeModel();
			$expenseModel 	= new ExpenseModel(); 
			$trans_ation 	= new FinanceModel();
			if ($trans_type == 1 || $trans_type == 'Income' ){
				$income_array = array(		
					'job_type' 	=> 	$income_data['in_job_type'] ,
					'job_id' 	=> 	trim($income_data['in_jobno']) ,
					'fre_id'	=> 	$income_data['uid'],
					'emp_id' 	=>  $income_data['in_emp_id'],
					'job_rate' 	=>  trim($income_data['in_rate']),
					'job_date' 	=> 	$income_data['in_date'] ? date('Y-m-d',strtotime(str_replace('/', '-', $income_data['in_date'])))  : null,
					'income_type' 	=> trim($income_data['in_category']) ,
					'bank' 		=> 	$income_data['in_bank'] ,
					'bank_date' 	=>  $income_data['in_bankdate'] ? date('Y-m-d', strtotime(str_replace('/', '-', $income_data['in_bankdate']))) : null ,
					'store' 	=> 	trim($income_data['in_store']) ,
					'location' 	=> 	trim($income_data['in_location']) ,
					'supplier' 	=> 	trim($income_data['in_supplier']) ,
					'status' 	=>  1,
					'invoice_id' => 0 
				);				

				if ($trans_id != '' && $trans_id != null) {
					$conditional_array = array('id' => $trans_id);
					$results = $incomeModel->update($income_array,$conditional_array);
				}else{
					$results = $incomeModel->save($income_array);
					if($results){			
						$finance_trans = array(		
							'trans_type_id' => $results ,
							'trans_type' 	=> '1' 
						);
						$res1 =	$trans_ation->save($finance_trans);
				   	}
				}
		   		
				
			}elseif ($trans_type == 2 || $trans_type == 'Expense'){
				$income_array = array(		
					'job_type' 			=> $income_data['in_job_type'] ,
					'job_id' 			=> trim($income_data['in_jobno']) ,
					'fre_id'			=>  $income_data['uid'] ,
					'cost' 				=>  trim($income_data['in_rate']),
					'job_date' 			=> 	$income_data['in_date'] ? date('Y-m-d',strtotime(str_replace('/', '-', $income_data['in_date'])))  : null,
					'expense_type_id' 	=> trim($income_data['in_category']) ,
					'bank' 				=> 	$income_data['in_bank'] ,
					'bank_date' 		=>  $income_data['in_bankdate'] ? date('Y-m-d', strtotime(str_replace('/', '-', $income_data['in_bankdate']))) : null ,
					'description' 		=> 	trim($income_data['in_description']) 
				);

				

				if ($trans_id != '' && $trans_id != null) {
					$conditional_array = array('id' => $trans_id);
					$results = $expenseModel->update($income_array,$conditional_array);
				}else{
		    		$results = $expenseModel->save($income_array);	
			    	if($results){			
						$finance_trans = array(		
							'trans_type_id' => $results ,
							'trans_type' 	=> '2' 
						);
						$res1 =	$trans_ation->save($finance_trans);
				    }
				}
			}		

			return $results;	
		}

		public function insertCostTransaction($user_data, $trans_type, $tran_id = null, $adapter)
		{			
			$action = new EmployertransModel();
			$farray = array(
		        'job_id' 	=> 	isset($user_data['in_jobno']) ? $user_data['in_jobno'] : 0 ,
		        'emp_id' 	=> 	$user_data['uid'] ,
		        'fre_id'	=>  isset($user_data['in_locum']) ? trim($user_data['in_locum']) : 0,
		        'fre_type' 	=>  $user_data['locum_type'] ,
		        'job_date' 	=>  $user_data['in_date'] ? date('Y-m-d', strtotime(str_replace('/', '-', $user_data['in_date']))) : null  ,
		        'job_rate' 	=>  trim($user_data['in_rate']),
		        'bonus' 	=> 	isset($user_data['in_bonus']) ? trim($user_data['in_bonus']) : null ,
		        'paid' 		=> 	($user_data['in_paid'] ==1) ? $user_data['in_paid'] : null ,
		        'paid_date' =>  isset($user_data['in_paiddate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $user_data['in_paiddate']))) : null  ,
		    );
		   	
			if ($tran_id != '' && $tran_id != null) {
				$conditional_array = array('id' => $tran_id);
				$action->update($farray,$conditional_array);
				$res = 1;
			}else{
				$res = $action->save($farray);
			}			
			
		    if($res){
		        return 1;
		    } else{
		    	return 0;
		    }			
		}
		
		public function manageSupplier($post_data,$trans_type, $adapter){

			 if($trans_type=='add' && $trans_type!=''){
			 	$supplier_records = $this->insertSupplier($post_data, $adapter);
			 }
			 if($trans_type=='edit' && $trans_type!=''){
			 	$supplier_records = $this->updateSupplier($post_data, $adapter);
			 }
			 if($trans_type=='delete' && $trans_type!=''){
			 	$supplier_records = $this->deleteSupplier($post_data['supid'], $adapter);
			 }
			 if($trans_type=='list' && $trans_type!=''){
			 	$supplier_records = $this->allSupplier($post_data['uid'], $adapter);
			 }
			 if($trans_type=='getlist' && $trans_type!=''){
			 	$supplier_records = $this->getSupplierById($post_data['supid'], $adapter);
			 }
			 return $supplier_records;
			
		}
		/*****Insert Supplier ***/
		public function insertSupplier($user_data, $adapter){
			$supid = isset($user_data['supid']) ? $user_data['supid'] : '';	
			$cname = isset($user_data['cname']) ? $user_data['cname'] : '';	
			$sname = isset($user_data['sname']) ? $user_data['sname'] : '';	
			$address = isset($user_data['address']) ? $user_data['address'] : '';	
			$address2 = isset($user_data['address2']) ? $user_data['address2'] : '';	
			$town = isset($user_data['town']) ? $user_data['town'] : '';	
			$county = isset($user_data['county']) ? $user_data['county'] : '';
			$postcode = isset($user_data['postcode']) ? $user_data['postcode'] : '';	
			$email = isset($user_data['email']) ? $user_data['email'] : '';	
			//$acc_name = isset($user_data['acc_name']) ? $user_data['acc_name'] : '';	
			//$acc_number = isset($user_data['acc_number']) ? $user_data['acc_number'] : '';	
			//$acc_sort_code = isset($user_data['acc_sort_code']) ? $user_data['acc_sort_code'] : '';	
			$contact_no = isset($user_data['contact_no']) ? $user_data['contact_no'] : '';	
			$uid = isset($user_data['uid']) ? $user_data['uid'] : '';	
			$actom = 'No';
	    	$sql_paypal = "INSERT INTO add_supplier (name,store_name,address,addresssec,town,country,postcode,email,automaticinvoice,contact_no,created_by) values('$cname','$sname','$address','$address2','$town','$county','$postcode','$email', '$actom','$contact_no','$uid')";
			$results = $adapter->query($sql_paypal, $adapter::QUERY_MODE_EXECUTE);
	    	return $results;
		}
		/*****Update Supplier ***/
		public function updateSupplier($user_data, $adapter){		
			$supid 		= isset($user_data['supid']) ? $user_data['supid'] : '';	
			$cname 		= isset($user_data['cname']) ? trim($user_data['cname']) : '';	
			$sname 		= isset($user_data['sname']) ? trim($user_data['sname']) : '';	
			$address 	= isset($user_data['address']) ? trim($user_data['address']) : '';
			$address2 	= isset($user_data['address2']) ? trim($user_data['address2']) : '';	
			$town 		= isset($user_data['town']) ? $user_data['town'] : '';	
			$county 	= isset($user_data['county']) ? $user_data['county'] : '';
			$postcode 	= isset($user_data['postcode']) ? $user_data['postcode'] : '';	
			$email 		= isset($user_data['email']) ? $user_data['email'] : '';	
			$contact_no = isset($user_data['contact_no']) ? $user_data['contact_no'] : '';	
			$uid 		= isset($user_data['uid']) ? $user_data['uid'] : '';	
			//$acc_name = isset($user_data['acc_name']) ? $user_data['acc_name'] : '';	
			//$acc_number = isset($user_data['acc_number']) ? $user_data['acc_number'] : '';	
			//$acc_sort_code = isset($user_data['acc_sort_code']) ? $user_data['acc_sort_code'] : '';	
			 $sql_user 	= "UPDATE add_supplier SET name='$cname',store_name='$sname',address='$address',addresssec='$address2',town='$town',country='$county',postcode='$postcode',email='$email',contact_no='$contact_no'  WHERE supplier_id='$supid'";

			$insert_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
	        	return $getRecord2;
		}
		/*****delete Supplier ***/
		public function deleteSupplier($supid, $adapter){
			$sqlGetRecord="DELETE FROM add_supplier  WHERE supplier_id='$supid'"; 
			$getRecord2 = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
	        return $getRecord2;
		}

		/*****All Supplier List***/
		public function allSupplier($uid, $adapter){
			/*****get all records of blocked FREELANCER/LOCUMS ***/
			$sqlGetRecord="SELECT * FROM add_supplier WHERE  created_by='$uid'"; 
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$getvalues= $getRecord->toArray();
		    return $getvalues;
		}
		/***** get Supplier Data By Id***/
		public function getSupplierById($supid, $adapter){
			/*****get all records of blocked FREELANCER/LOCUMS ***/
			$sqlGetRecord="SELECT * FROM add_supplier WHERE  supplier_id='$supid'"; 
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$getvalues= $getRecord->toArray();
		    return $getvalues[0];
		}
		//get store information and user infomation by job id
		public function get_job_details_by_id($job_id, $adapter){
			$job_info = new JobController();			
			$finance_records = $job_info->get_job_info_by_id($job_id, $adapter);
			//if($finance_records!=''){
			$userData = $this->getUserByIdApp($finance_records['e_id'],$adapter);
			
			if(count($userData)==0){
				$userData=null;
			}else{
				$userDataStore = $this->employerStoreByID($finance_records['store_id'],$adapter);
				$userData['store_details']=$userDataStore['emp_store_name'];
				$userData['job_date']=date('d/m/Y',strtotime($finance_records['job_date']));
				$userData['job_rate']=$finance_records['job_rate'];
				$userData['locations']=$finance_records['job_address'];
			}
		    //}
		    return $userData;
			
		}	

		/***** get Supplier Data By Id***/
		public function getUserByIdApp($user_id,$adapter){
			/*****get all records of blocked FREELANCER/LOCUMS ***/
			$sqlGetRecord="SELECT * FROM user WHERE  id='$user_id'"; 
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$getvalues= $getRecord->toArray();
		    return $getvalues[0];
		}
		/***** get Supplier Data By Id***/
		public function employerStoreByID($store_id,$adapter){
			/*****get all records of blocked FREELANCER/LOCUMS ***/
			$sqlGetRecord="SELECT * FROM employer_store_list WHERE  emp_st_id='$store_id'";
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$getvalues= $getRecord->toArray();
		    return $getvalues[0];
		}

		/*Get transaction info by transaction ID*/
		public function getTransactionDetails($trans_id,$trans_type, $user_id = null, $user_type = null){
			$trans_records = '';
			$financeHelper = new FinanceHelper();
			if ($user_type == 2) {
				if($trans_type == 1){
					$incomeInfo = (array)$financeHelper->getOnlyFinanceincome($trans_id);
					if (!empty($incomeInfo)) {
						$trans_records = array(
								'in_job_type' 	=> $incomeInfo['income_type'],
								'in_jobno' 		=> $incomeInfo['job_id'],
								'in_date' 		=> $incomeInfo['job_date'],
								'in_rate' 		=> $incomeInfo['job_rate'],
								'in_store' 		=> $incomeInfo['store'],
								'in_category' 	=> $incomeInfo['income_type'],
								'in_location' 	=> $incomeInfo['location'],
								'in_supplier' 	=> $incomeInfo['supplier'],
								'in_emp_id' 	=> $incomeInfo['emp_id'],
								'uid' 			=> $incomeInfo['fre_id'],
								'in_bank'		=> ($incomeInfo['bank'] == 1) ? true : false,
								'in_bankdate'	=> ($incomeInfo['bank'] == 1) ? $incomeInfo['bank_date'] : null
							);
					}
				}elseif($trans_type == 2){
					$expenceInfo = (array)$financeHelper->getOnlyFinanceexpence($trans_id);	
					if (!empty($expenceInfo)) {
						$trans_records = array(
								'uid' 			=> $expenceInfo['fre_id'],
								'in_job_type' 	=> $expenceInfo['job_type'],
								'in_jobno' 		=> $expenceInfo['job_id'],
								'in_rate' 		=> $expenceInfo['cost'],
								'in_date' 		=> $expenceInfo['job_date'],
								'in_description'=> $expenceInfo['description'],
								'in_category' 	=> $expenceInfo['expense_type_id'],
								'in_bank'		=> ($expenceInfo['bank'] == 1) ? true : false,
								'in_bankdate'	=> ($expenceInfo['bank'] == 1) ? $expenceInfo['bank_date'] : null	
							);
					}
				}
			}elseif($user_type == 3){
				$costInfo = (array)$financeHelper->EmpFinanceGetAllIncome($user_id, $trans_id,null,null,null);
				if (!empty($costInfo[0])) {
					$trans_records = array(
							'locum_type' 	=> $costInfo[0]['fre_type'],
							'in_jobno' 		=> $costInfo[0]['job_id'],
							'in_locum' 		=> $costInfo[0]['fre_id'],
							'in_date' 		=> $costInfo[0]['job_date'],
							'in_rate' 		=> $costInfo[0]['job_rate'],
							'in_bonus' 		=> $costInfo[0]['bonus'],
							'in_paid'		=> ($costInfo[0]['paid'] == 1) ? true : false,
							'in_paiddate'	=> ($costInfo[0]['paid'] == 1) ? $costInfo[0]['paid_date'] : null		
						);
				}				
			}
			
			return $trans_records;
			
		}

		/* Delete transaction by transaction Id */
		public function deleteTransaction($trans_id,$trans_type,$user_id)
		{
			$incomeModel 	= new IncomeModel();
			$expenseModel 	= new ExpenseModel(); 
			$delete_status 	= 0;
			if ($trans_type == 1) {
				//Delete locum income records
				$incomeModel->deleteFinanceincome($trans_id,$user_id);
				$delete_status 	= 1;
			}elseif($trans_type == 2){
				//Delete locum expense records
				$expenseModel->deleteFinanceexpence($trans_id,$user_id);
				$delete_status 	= 1;
			}elseif($trans_type == 3){
				// Delete Employer cost
				$action = new EmployertransModel();
				$action->deleteFinance($trans_id,$user_id);
				$delete_status 	= 1;
			}	
			return $delete_status;
		}


		/* Edit transaction by emp, get transaction info*/
	}