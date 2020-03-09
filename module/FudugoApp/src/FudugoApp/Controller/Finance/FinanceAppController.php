<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Finance;
	use Gc\Registry;
	use GcFrontend\Controller\DbController as DbController;
	use GcFrontend\Helper\FinanceHelper as FinanceHelper; 
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use Gc\User\Finance\Income\Model as IncomeModel;
	use Gc\User\Finance\Expense\Model as ExpenseModel; 
	use Gc\User\Finance\Expense\CategoryCollection as CategoryCollection;
	use GcConfig\Controller\FinanceController as FinanceController;
	use Gc\User\Finance\Model as FinanceModel;
	use GcFrontend\Controller\JobmailController as JobmailController;
	use Gc\User\Finance\Employertrans\Model as EmployertransModel;
	use Gc\User\Finance\AddSupplier\Collection as SupplierCollection;
	use Gc\User\Finance\Bank\Model as BankModel;
	use Gc\User\Finance\Bank\Collection as BankCollection;


	Class FinanceAppController{
		public function get_finance($user_data)
		{
			$dbConfig 	= new DbController();
   			$adapter 	= $dbConfig->locumkitDbConfig();
			$user_id 	= $user_data['user_id'];
			$role_id 	= $user_data['role_id'];
			$page_id 	= $user_data['page_id'];


			$finance_records = array();		
			switch ($page_id) {
				case 'all-transaction':
					$show 		= $user_data['show_type'];
					$sort_by 	= isset($user_data['sort_by']) ? $user_data['sort_by'] : 'job_date';
					$finance_records = $this->getAllTransaction($user_id, $cat=null, $show, $sort_by, $adapter);
					break;
				case 'emp-all-transaction': 
					$paid = isset($user_data['paid']) ? $user_data['paid'] : null;
					$paid_date = isset($user_data['paid_date']) ? $user_data['paid_date'] : null;
					$trans_id = isset($user_data['trans_id']) ? $user_data['trans_id'] : null;
					$sort_by = isset($user_data['sort_by']) ? $user_data['sort_by'] : 'job_date';
					$finance_records = $this->getEmpAllTransaction($user_id,$trans_id,$paid,$paid_date, $sort_by);
					break;
				case 'update-transaction':					
					$trans_id 	= $user_data['trans_id'];
					$trans_date = $user_data['trans_date'];
					$trans_type = $user_data['trans_type'];
					$show 		= $user_data['show_type'];					
					$action 	= isset($user_data['action']) ? $user_data['action'] : '';					
					$this->updateTransaction($trans_id,$trans_date,$trans_type, $adapter);
					if ($action=="income-by-area") {						
						$finance_records = $this->get_income_by_area($user_id);	
					}elseif ($action=="income-by-category") {						
						$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';					
						$finance_records = $this->get_income_by_category($user_id,$filter_cat);	
					}elseif ($action=="expense-by-category") {						
						$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';					
						$finance_records = $this->get_expense_by_category($user_id,$filter_cat);	
					}elseif ($action=="income-by-suplier") {						
						$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';	
						$finance_records = $this->get_income_by_suplier($user_id,$filter_cat);	
					}elseif ($action=="open-invoice") {							
						$invoice_status = isset($user_data['invoice_status']) ? $user_data['invoice_status'] : '';	
						$invoice_job_id = isset($user_data['invoice_job_id']) ? $user_data['invoice_job_id'] : '';	
						$finance_records = $this->get_open_invoices($user_id,$invoice_status,$invoice_job_id);	
					}else{
						$finance_records = $this->getAllTransaction($user_id, $cat=null, $show, $adapter);
					}					
					break;	
				case 'cash-movements':					
					$finance_records = $this->getCashMovements($user_id, $cat=null, $adapter);	
					break;
				case 'weekly-report':					
					$finance_records = $this->getWeeklyReport($user_id, $adapter);	
					break;
				case 'finanace-summary':					
					$finance_records = json_encode($this->get_user_finance_summary($user_id));	
					break;
				case 'income-by-area':					
					$finance_records = $this->get_income_by_area($user_id);	
					break;
				case 'income-by-category':
					$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';					
					$finance_records = $this->get_income_by_category($user_id, $filter_cat);
					break;
				case 'income-by-suplier':					
					$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';	
					$finance_records = $this->get_income_by_suplier($user_id,$filter_cat);
					break;
				case 'expense-by-category':
					$filter_cat = isset($user_data['filter_cat']) ? $user_data['filter_cat'] : '';					
					$finance_records = $this->get_expense_by_category($user_id, $filter_cat);
					break;
				case 'net-income':					
					$finance_records = $this->get_net_income($user_id);	
					break;	
				case 'open-invoices':									
					$finance_records = $this->get_open_invoices($user_id);
					break;
				case 'send-invoice':					
					$income_id = isset($user_data['income_id']) ? $user_data['income_id'] : '';
					$send = isset($user_data['send']) ? $user_data['send'] : '';
					$data = isset($user_data['data']) ? $user_data['data'] : '';
					$finance_records = $this->send_invoice($user_id,$income_id,$send,$data);
					break;	
				case 'get-store-list':									
					$finance_records = $this->get_supplier_store_list($user_data);
					break;
				case 'get-bank-detail':									
					$finance_records = $this->get_bank_details($user_data);
					break;
				case 'set-bank-detail':									
					$finance_records = $this->set_bank_details($user_data);
					break;
			}
			return $finance_records;
		}

		public function getAllTransaction($user_id, $cat=null, $show=null, $sort_by, $adapter)
		{
			$financeHelper 	= new FinanceHelper();
			$helpController = new HelperController();
			$financialYear	= date('Y');
			$filter			= 'month';
			$all_tra_records= array();
			$all_records 	= array();
			$all_records_sort_by 	= array();
			if ($show == 'income') {
				$all_tra_records 	= $financeHelper->getAllIncome($user_id,$financialYear,$filter,null, $cat);
			}elseif($show == 'expense'){
				$all_tra_records 	= $financeHelper->getAllExpense($user_id,$financialYear,$filter, $cat);
			}else{
				$incomeRecord 	= $financeHelper->getAllIncome($user_id,$financialYear,$filter ); 
				$expenseRecord 	= $financeHelper->getAllExpense($user_id,$financialYear,$filter);
				$all_tra_records = array_merge($incomeRecord,$expenseRecord);
			}
			if (!empty($all_tra_records)) {
				foreach ($all_tra_records as $key => $record) {
					if($sort_by == 'job_date'){
						$all_records_sort_by[$key] = strtotime( str_replace('/', '-', $record['job_date']) );
					}else if($sort_by == 'job_rate'){
						$all_records_sort_by[$key] = isset($record['job_rate']) ? $record['job_rate'] : $record['cost'];
					}else{
						$all_records_sort_by[$key] = @$record[$sort_by] ? $record[$sort_by] : '0';
					}
					$all_records[] = array(
						'trans_id' 		=> $record['trans_id'],
						'trans_type' 	=> $record['trans_type'],
						'supplier' 		=> $record['supplier'],
						'store' 		=> $record['store'],
						'status' 		=> $record['status'],
						'location' 		=> $record['location'],
						'job_type' 		=> $record['job_type'],
						'job_date' 		=> $record['job_date'],
						'job_rate' 		=> isset($record['job_rate']) ? $helpController->formate_price($record['job_rate']) : $helpController->formate_price($record['cost']),
						'job_id' 		=> isset($record['job_id']) ? $record['job_id'] : 'N/A',
						'category'		=> isset($record['income_type']) ? $financeHelper->getIncometype($record['income_type']) :  $financeHelper->getExpencetype($record['expense_type_id']),
						'id'			=> $record['id'],
						'fre_id'		=> $record['fre_id'],
						'emp_id'		=> $record['emp_id'],
						'bank_date'		=> date('d/m/Y', strtotime($record['bank_date'])),
						'bank'			=> $record['bank'],
						'description'	=> isset($record['description']) ? substr($record['description'],0,10).'...' : '',
						'long_description'	=> isset($record['description']) ? $record['description'] : ''
					);
				}
				if($sort_by == 'job_date' || $sort_by == 'job_rate' || $sort_by == 'job_id' || $sort_by == 'trans_id'){
					array_multisort($all_records_sort_by, SORT_DESC, SORT_NUMERIC, $all_records);
				}else{
					array_multisort($all_records_sort_by, SORT_DESC, SORT_STRING, $all_records);
				}
			}		
			
			return json_encode($all_records);
		}

		public function getEmpAllTransaction($user_id,$trans_id = null, $paid=null, $paid_date=null, $sort_by)
		{
			$financeHelper 	= new FinanceHelper();
			$helpController = new HelperController();
			$employertrans	= new EmployertransModel();
			$financialYear	= date('Y');
			$filter			= 'month';
			if($paid & $paid == 1){
				$incomeData = array(
			        'paid' 			=> $paid,
			        'paid_date' 	=> $paid_date,
			    );
			    $employertrans->update_bank($trans_id,$incomeData);
			}
			$empfinanceRecord = $financeHelper->EmpFinanceGetAllIncome($user_id, null,$financialYear,null);
			$filterTransactionArray = array();
			if (!empty($empfinanceRecord )) {
				foreach ($empfinanceRecord as $key => $record) {
					if($sort_by == 'job_date'){
						$filterTransactionArray[$key] = strtotime(str_replace('/', '-', $record[$sort_by])) ;
					}else if($sort_by == 'total'){
						$filterTransactionArray[$key] = (float)$record['job_rate'] + (float)$record['bonus'];
					}else{
						$filterTransactionArray[$key] = $record[$sort_by];
					}
					
					$empfinanceRecord[$key]['job_rate'] = $helpController->formate_price($record['job_rate']);
					$empfinanceRecord[$key]['total'] = $helpController->formate_price((float)$record['job_rate'] + (float)$record['bonus']);
					$empfinanceRecord[$key]['bonus'] = isset($record['bonus']) ? $helpController->formate_price($record['bonus']):'';

				}
				array_multisort($filterTransactionArray, SORT_DESC, SORT_NUMERIC, $empfinanceRecord);
			}
			
			return json_encode($empfinanceRecord);
		}

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

		public function getCashMovements($user_id, $cat, $adapter)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$categoryCollection = new CategoryCollection();
			$helpController 	= new HelperController();
			$movement_report 	= array();
			$financialYear		= date('Y');
			$filter 		= 'month';
			$incomeRecord 		= $financeHelper->getAllIncome($user_id,$financialYear,$filter,false,null,1); 
			$expenseRecord 		= $financeHelper->getAllExpense($user_id,$financialYear,$filter,null,1); 	
			$transchart 		= $financeHelper->chart_getAlltrans($user_id,$financialYear,$filter,1); 
			$alltrans 		= array_merge($incomeRecord , $expenseRecord);
			$movement_report['finance_sumamry'] = $this->get_user_finance_summary($user_id);
			$chart_records 		= array();
			$chart_label 		= array();			
			if (!empty($transchart[0])) {
				foreach ($transchart[0] as $key => $label) {
					if (is_array($label)) {
						$chart_label[] = implode(",", $label);
					}else{
						$chart_label[] = $label ;
					}
				}
			}
			if (!empty($transchart[3])) {				
				foreach ($transchart[3] as $key => $record) {
					$chart_records[] = round($record,2);					
				}
			}			
			
			$movement_report['chart_data'] = array(
					'label' => $chart_label,
					'data' 	=> $chart_records
				);			
			
			$all_records 		= array();
			if (!empty($alltrans)) {				
				foreach ($alltrans as $key => $record) {
					$trans_type = ($record['trans_type'] == 1) ? 'Income' : 'Expense';
					$all_records[] = array(
							'trans_id' 		=> $record['trans_id'],
							'trans_type' 	=> $trans_type,
							'job_date' 		=> $record['job_date'],
							'job_rate' 		=> isset($record['job_rate']) ? $helpController->formate_price($record['job_rate']) : $helpController->formate_price($record['cost']),
							'job_id' 		=> isset($record['job_id']) ? $record['job_id'] : 'N/A',
							'category'		=> isset($record['income_type']) ? $financeHelper->getIncometype($record['income_type']) :  $financeHelper->getExpencetype($record['expense_type_id']),
							'id'			=> $record['id'],
							'fre_id'		=> $record['fre_id'],
							'emp_id'		=> $record['emp_id'],
							'bank_date'		=> $record['bank_date'],
							'bank'			=> $record['bank']							
						);
				}
			}
			$movement_report['records'] = $all_records;	
							
			return json_encode($movement_report);
		}

		public function get_user_finance_summary($user_id)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear		= date('Y');
			$finance_summary 	= array();
			$finyeardata 		=  $financeHelper->getUserFinanceyearStartMonth($user_id,true);
			$finyearusrtype 	= $finyeardata['user_type'];
			$income 	= $financeHelper->getIncomeByuser($user_id , $financialYear);
			$expence 	= $financeHelper->getExpenceByuser($user_id , $financialYear);
			$tax 		= $financeController->taxclaculation(($income['job_rate']) - ($expence['cost']),$finyearusrtype,$financialYear,$user_id);
			$finance_summary['income'] 	= $helpController->formate_price($income['job_rate']);
			$finance_summary['expence'] = $helpController->formate_price($expence['cost']);
			$finance_summary['profit'] 	= $helpController->formate_price(($income['job_rate']) - ($expence['cost']));
			$finance_summary['tax'] 	= $helpController->formate_price($tax);
			$finance_summary['finance_year'] = $income['financial_year'];
			return $finance_summary;
		}

		public function getWeeklyReport($user_id, $adapter)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear 		= date('Y');
			$filter 			= 'month';
			$weekly_report 		= array();
			$incomeWeekdata		= array();
			$incomeWeekchart	= array();
			$incomeWeek 		= array();
			$jobWeekdata 		= array();
			$jobWeekchart 		= array();
			$jobWeek 			= array();
			
			$cyear[] 			= $financeHelper->getMonthFinancialYear($user_id , $financialYear);			
		    foreach($cyear as $year){
		    	$incomeWeekdata 	= $financeHelper->chartGetTransWeekly($user_id,$year,'income');
		    	foreach ($incomeWeekdata['data'] as $key => $income) {
		    		$incomeWeekdata['data'][$key] = array(
		    				'day' 		=> $income['day'],
		    				'bank_yes'	=> $helpController->formate_price($income['bank_yes'])
		    			);
		    	}
foreach($incomeWeekdata['chart'][0] as $k=>$weekDay){
$incomeWeekdata['chart'][0][$k] =  substr($weekDay,0,3);
}
		        $incomeWeekchart 	= $incomeWeekdata['chart'];
		        $incomeWeek 		= $incomeWeekdata['data'];

		        $jobWeekdata 	= $financeHelper->chartGetJobWeekly($user_id,$year);
foreach($jobWeekdata['chart'][0] as $k=>$weekDay){
$jobWeekdata['chart'][0][$k] =  substr($weekDay,0,3);
}
		        $jobWeekchart 	= $jobWeekdata['chart'];
		        $jobWeek 	= $jobWeekdata['data'];
		    }
		    
		    $weekly_report = array(		    		
		    		'incomeWeekchart' 	=> $incomeWeekchart,
		    		'incomeWeek'		=> $incomeWeek,
		    		'jobWeekchart'		=> $jobWeekchart,
		    		'jobWeek'			=> $jobWeek,
		    		'finance_year'		=> ($financialYear-1).'-'.$financialYear
		    	);
		    /*print_r($weekly_report);
		    die();*/
		    return json_encode($weekly_report);
		}

		public function get_income_by_area($user_id)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear 		= date('Y');
			$filter 			= 'month';
			$catIncome 			= array();

			$cattype 		= $financeHelper->getIncometype();
    		$incomeRecord 	= $financeHelper->getAllIncome($user_id,$financialYear,$filter , null ,null);
    		$cat_year[] 	= $financeHelper->getMonthFinancialYear($user_id , $financialYear);
    		foreach($cat_year as $year){ 
    			$chartIncome 	= $financeHelper->GetchartIncomeBYlocation($user_id,$year);
    		}
    		$chart_label = array();
    		$chart_data  = array();
    		$chart_color = array();
    		foreach ($chartIncome as $key => $income) {
    			$chart_label[] = $income['label'];
    			$chart_data[]  = $income['value'];
    			$chart_color[] = $income['color'];
    		}

    		$formated_data = array();    		
    		if (!empty($incomeRecord)) {
    			foreach ($incomeRecord as $key => $record) {
    				$trans_type = ($record['trans_type'] == 1) ? 'Income' : 'Expense';
	    			$formated_data[] = array(
							'trans_id' 		=> $record['trans_id'],
							'trans_type' 	=> $trans_type,
							'job_date' 		=> $record['job_date'],
							'job_rate' 		=> isset($record['job_rate']) ? $helpController->formate_price($record['job_rate']) : $helpController->formate_price($record['cost']),
							'job_id' 		=> isset($record['job_id']) ? $record['job_id'] : 'N/A',
							'category'		=> isset($record['income_type']) ? $financeHelper->getIncometype($record['income_type']) :  $financeHelper->getExpencetype($record['expense_type_id']),
							'id'			=> $record['id'],							
							'bank_date'		=> $record['bank_date'],
							'bank'			=> $record['bank'],		
							'location'		=> $record['location']		
	    				);
	    		}
    		}
    		

    		$income_by_area_report = array(
    				'chart' 		=> array(
	    						'label' => $chart_label,
	    						'data' 	=> $chart_data,
	    						'color' => $chart_color,
	    					),
    				'data'			=> $formated_data,
    				'finance_year'	=> ($financialYear-1).'-'.$financialYear
    			);
    		return json_encode($income_by_area_report);   			
		}

		public function get_income_by_category($user_id, $cat = null)
		{			
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear 		= date('Y');
			$filter 			= 'month';
			$catIncome 			= array();
			$income_by_cat 		= array();

			$cattype 			= $financeHelper->getIncometype();
			$incomeRecord 		= $financeHelper->getAllIncome($user_id,$financialYear,$filter , null ,$cat);
    		//$incomechart 		= $financeHelper->chart_getAllIncome($user_id,$financialYear,$filter ,$cat);
    		$cat_year[] 		= $financeHelper->getMonthFinancialYear($user_id , $financialYear);
    		foreach($cat_year as $year){ 
    			$chartIncome 	= $financeHelper->GetchartIncomeBYcondition($user_id,$year); 
    		}
    		$formated_data = array();
    		if (!empty($incomeRecord)) {
    			foreach ($incomeRecord as $key => $record) {
    				$trans_type = ($record['trans_type'] == 1) ? 'Income' : 'Expense';
	    			$formated_data[] = array(
							'trans_id' 		=> $record['trans_id'],
							'trans_type' 	=> $trans_type,
							'job_date' 		=> $record['job_date'],
							'job_rate' 		=> isset($record['job_rate']) ? $helpController->formate_price($record['job_rate']) : $helpController->formate_price($record['cost']),
							'job_id' 		=> isset($record['job_id']) ? $record['job_id'] : 'N/A',
							'category'		=> isset($record['income_type']) ? $financeHelper->getIncometype($record['income_type']) :  $financeHelper->getExpencetype($record['expense_type_id']),
							'id'			=> $record['id'],							
							'bank_date'		=> $record['bank_date'],
							'bank'			=> $record['bank']	
	    				);
	    		}
    		}
    		$chart_label = array();
    		$chart_data  = array();
    		$chart_color = array();
    		foreach ($chartIncome as $key => $income) {
    			$chart_label[] = $income['label'];
    			$chart_data[]  = $income['value'];
    			$chart_color[] = $income['color'];
    		}
    		$category = array();
    		foreach ($cattype as $key => $cat) {
    			$category[] = array(
    					'cat_id' => $key,
    					'cat'	 => $cat
    				);
    		}
    		$income_by_cat 		= array(
    				'category' 	=> $category,
    				'data'		=> $formated_data,
    				'chart' 	=> array(
	    						'label' => $chart_label,
	    						'data' 	=> $chart_data,
	    						'color' => $chart_color,
	    					),
    				'finance_year'	=>($financialYear-1).'-'.$financialYear
    			);
    		
    		return json_encode($income_by_cat);
		}

		public function get_expense_by_category($user_id, $cat = null)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear 		= date('Y');
			$filter 			= 'month';

			$cattype 			= $financeHelper->getExpencetype();
			$expenseRecord 		= $financeHelper->getAllExpense($user_id,$financialYear,$filter , $cat); 
			$expenseChart 		= $financeHelper->chart_getAllExpense($user_id,$financialYear,$filter ,$cat ); 
			$cat_year[] 		= $financeHelper->getMonthFinancialYear($user_id , $financialYear);
			foreach($cat_year as $year){ 
				$catExpence = $financeHelper->GetchartExpenseBYcondition($user_id,$year); 
			}			
			$formated_data = array();
			
    		if (!empty($expenseRecord)) {
    			foreach ($expenseRecord as $key => $record) {
    				$trans_type = ($record['trans_type'] == 1) ? 'Income' : 'Expense';
	    			$formated_data[] = array(
							'trans_id' 		=> $record['trans_id'],	
							'trans_type' 	=> $trans_type,
							'job_date' 		=> $record['job_date'],
							'job_rate' 		=> isset($record['job_rate']) ? $helpController->formate_price($record['job_rate']) : $helpController->formate_price($record['cost']),
							'job_id' 		=> isset($record['job_id']) ? $record['job_id'] : 'N/A',
							'category'		=> isset($record['income_type']) ? $financeHelper->getIncometype($record['income_type']) :  $financeHelper->getExpencetype($record['expense_type_id']),
							'id'			=> $record['id'],							
							'bank_date'		=> $record['bank_date'],
							'bank'			=> $record['bank'],
							'description'	=> isset($record['description']) ? substr($record['description'],0,10).'...' : '',
							'long_description'	=> isset($record['description']) ? $record['description'] : ''
	    				);
	    		}
    		}
    		$chart_label = array();
    		$chart_data  = array();
    		$chart_color = array();
    		foreach ($catExpence as $key => $expence) {
    			$chart_label[] = $expence['label'];
    			$chart_data[]  = $expence['value'];
    			$chart_color[] = $expence['color'];
    		}
    		$category = array();
    		foreach ($cattype as $key => $cat) {
    			$category[] = array(
    					'cat_id' => $key,
    					'cat'	 => $cat
    				);
    		}
    		$expense_by_cat 	= array(
    				'category' 	=> $cattype,
    				'data'		=> $formated_data,
    				'chart' 	=> array(
	    						'label' => $chart_label,
	    						'data' 	=> $chart_data,
	    						'color' => $chart_color,
	    					),
    				'finance_year'	=> ($financialYear-1).'-'.$financialYear
    			);

    		return json_encode($expense_by_cat);
		}

		public function get_net_income($user_id)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear 		= date('Y');
			$filter 			= 'month';

			
			$transchart 		= $financeHelper->chart_getAlltrans($user_id,$financialYear,$filter);
			$finance_summary 	= $this->get_user_finance_summary($user_id);

			$net_income_data = array();
			$net_income_chart_data = array();
			foreach ($transchart[3] as $key => $net_income) {
			 	$net_income_data[] = $helpController->formate_price($net_income);
			 	$net_income_chart_data[] = $net_income;
			} 
			

			$net_income_label = array();
			foreach ($transchart[0] as $key => $label) {
				if (is_array($label)) {
					$net_income_label[] = implode(',', $label);
				}else{
					$net_income_label[] = $label;
				}
			}

			$net_income_record  = array(
					'data' 		=> $net_income_data,
					'chart_data'=> $net_income_chart_data,
					'label'		=> $net_income_label,
					'summary'	=> $finance_summary 
				);
			
			return json_encode($net_income_record);
		}

		public function get_income_by_suplier($user_id, $cat = null)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$financialYear 		= date('Y');
			$filter 			= 'month';

			$cattype 				= $financeHelper->getIncometype();
		    $incomeRecord_select 	= $financeHelper->getAllIncome($user_id,$financialYear,$filter);
		    $incomeRecord 			= $financeHelper->getAllIncome($user_id,$financialYear,$filter , null ,null , null ,$cat);
		    $incomechart 			= $financeHelper->chart_getAllIncome($user_id,$financialYear,'month' ,null, $cat);
		    $cat_year[] 			= $financeHelper->getMonthFinancialYear($user_id , $financialYear);
		    foreach($cat_year as $year){ 
		    	$catIncome = $financeHelper->GetchartIncomeBYsupplier($user_id,$year); 
		    }

		    $supplier = array();
		    foreach($incomeRecord_select as $incomedata){
		      $supplier[] =  $incomedata['supplier'];
		    }
		    $supplier = array_unique($supplier);
		    $supplier = array_values($supplier);

		   	
    		$chart_data = array();
    		foreach ($catIncome as $key => $income) {
    			$chart_data['label'][] = $income['label'];
    			$chart_data['data'][]  = $income['value'];
    			$chart_data['color'][] = $income['color'];
    		}
    		$formated_data = array();
    		if (!empty($incomeRecord)) {
    			foreach ($incomeRecord as $key => $record) {
    				$trans_type = ($record['trans_type'] == 1) ? 'Income' : 'Expense';
	    			$formated_data[] = array(
							'trans_id' 		=> $record['trans_id'],	
							'trans_type' 	=> $trans_type,
							'job_date' 		=> $record['job_date'],
							'job_rate' 		=> isset($record['job_rate']) ? $helpController->formate_price($record['job_rate']) : $helpController->formate_price($record['cost']),
							'job_id' 		=> isset($record['job_id']) ? $record['job_id'] : 'N/A',
							'category'		=> isset($record['income_type']) ? $financeHelper->getIncometype($record['income_type']) :  $financeHelper->getExpencetype($record['expense_type_id']),
							'id'			=> $record['id'],							
							'bank_date'		=> $record['bank_date'],
							'bank'			=> $record['bank'],
							'supplier'		=> $record['supplier']
	    				);
	    		}
    		}

		    $income_by_suplier = array(
		    		'suplier' 		=> $supplier,
		    		'data'			=> $formated_data,
		    		'chart_data'	=> $chart_data,
		    		'finance_year'	=> ($financialYear-1).'-'.$financialYear
		    	);
		   
		  	return json_encode($income_by_suplier);

		}

		public function get_open_invoices($user_id,$status = null,$id = null)
		{
			$financeController 	= new FinanceController();
			$financeHelper 		= new FinanceHelper();
			$helpController 	= new HelperController();
			$incomeModel 		= new IncomeModel();
			$financialYear 		= date('Y');
			$filter 			= 'month';

			if ($status || $status == 0) {
				$incomeData = array(
		    		'invoice_notrequired' 	=> $status, 
		    	);
		   		$incomeModel->income_invoiceReq($id,$incomeData);
			}

			$incomeRecord = $financeHelper->getAllIncome($user_id,$financialYear,$filter, true );
			$i30 = $i60 = $i90 = $i90plus = 0;
			if (!empty($incomeRecord)) {
				foreach ($incomeRecord as $key => $income) {
					$incomeRecord[$key]['job_rate'] 	= $helpController->formate_price($income['job_rate']);
					$incomeRecord[$key]['job_type'] 	= $financeHelper->getJobtype($income['job_type']);
					$incomeRecord[$key]['income_type'] 	= $financeHelper->getIncometype($income['income_type']);
					$diffDay = $financeHelper->getdataDifference($income['job_date']);
					if (@$diffDay < 30) {
						$i30++;
						$incomeRecord[$key]['zero_30'] = 1;
						$incomeRecord[$key]['between_30_60'] = 0;
						$incomeRecord[$key]['between_60_90'] = 0;
						$incomeRecord[$key]['above_90'] = 0;
					}elseif(@$diffDay >= 30 && @$diffDay < 60){
						$i60++;
						$incomeRecord[$key]['zero_30'] = 0;
						$incomeRecord[$key]['between_30_60'] = 1;
						$incomeRecord[$key]['between_60_90'] = 0;
						$incomeRecord[$key]['above_90'] = 0;

					}elseif(@$diffDay >= 60 && @$diffDay < 90){
						$i90++;
						$incomeRecord[$key]['zero_30'] = 0;
						$incomeRecord[$key]['between_30_60'] = 0;
						$incomeRecord[$key]['between_60_90'] = 1;
						$incomeRecord[$key]['above_90'] = 0;
					}else{
						$i90plus++;
						$incomeRecord[$key]['zero_30'] = 0;
						$incomeRecord[$key]['between_30_60'] = 0;
						$incomeRecord[$key]['between_60_90'] = 0;
						$incomeRecord[$key]['above_90'] = 1;
					}
					
				}
			}
			$invoiceChartRecord  = array($i30,$i60,$i90,$i90plus);
			$ionvoice_record = array(
					'data' 			=> $incomeRecord,
					'chart'			=> $invoiceChartRecord,
					'finance_year'	=>array( ($financialYear-1).'-'.$financialYear)
				);		
			return json_encode($ionvoice_record);
		}

		public function send_invoice($user_id,$income_id, $send = null, $data= null)
		{

 
			$financeHelper 		= new FinanceHelper();
			$dbConfig 	= new DbController();
   			$adapter 	= $dbConfig->locumkitDbConfig();
			$userdata 			= $financeHelper->loginUserDate($user_id);
			$jobdata 			= $financeHelper->getjobDataInvoice($income_id,$user_id);
			$bankcollection = new BankCollection();
			
			if ($send) {

				$jobmailController 	= new JobmailController();
				$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
				$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
				$site_name 	= $configGet->get('site_name');
				$adminEmail = $configGet->get('mail_from');
				$currency 	= $configGet->get('site_currency');

				/*Get Bank Details */
				$bank_details_array = '';
				$bank_details_array['acc_name'] =  $data['supplier_account_name'];
				$bank_details_array['acc_number'] =  $data['supplier_account_no'];
				$bank_details_array['acc_sort_code'] =  $data['supplier_account_sortcode'];

				$invoiceno = $financeHelper->generateInvoicenum($data['supplier_email'],$adminEmail,$jobdata['job_rate'],$uid);
				$job_title = isset($jobdata['job_title']) ? $jobdata['job_title'] : '-';
				$tempalte   = '<div style="width: 700px;margin:0 auto;"><div style=" border: 2px solid #dedede;float: left; width: 100%;" class="prevboxshadow"><div class="mail-header" style="width: 100%; float: left; clear: both; background: rgb(0, 169, 224) none repeat scroll 0px 0px; border-bottom:2px solid #dedede;">';
				$tempalte  .= '<a href="'.$serverUrl().'"><img src="'.$serverUrl().'/public/frontend/locumkit-template/img/logo.png" alt="'.$site_name.'" width="100px"  style="margin:10px;"></a>';
				$tempalte  .= '</div><div style="margin-bottom: -5px; float: left; width: 100%;">';
				$tempalte  .= '<div style="float: left; width: 100%;">';
				$tempalte  .= '<div style="float: left; width: 100%;">';
				$tempalte  .= '<section style="float: left; width: 100%;">';
				$tempalte  .= '<div style="float: left; width: 100%;">';
				$tempalte  .= '<div  style="text-align: center; float: left; width: 100%;">';
				$tempalte  .= '<h1 style="margin: 0; border-bottom:2px solid #dedede; padding: 10px 0;text-transform: capitalize;font-size: 23px;font-weight: 600;background: #e0e0e0;color: #000;">Invoice</h1>';
				$tempalte  .= '</div>';
				$tempalte  .= '<div style="text-align: right;float: left;">';
				$tempalte  .= '<div style="float: left; width: 100%;">';
				$tempalte  .= '<table style="width: 100%;    border-spacing: 0;">';
				$tempalte  .= '<tbody>';
				$tempalte  .= '<tr>';
				$tempalte  .= '<td style="padding: 15px; text-align:left;">';
				$tempalte  .= '<div class="invoice-user-info" style="width: 315px;"><div style="font-weight: bold;border-bottom: 1px solid #ccc;padding-bottom: 5px;margin-bottom: 10px;">Supplier Information</div><div><span><b>Name :</b> </span>'.ucfirst($data['supplier_name']).'</div>';
				$tempalte  .= '<div><span><b>Address :</b> </span> '.$data['supplier_address'].', '.$data['supplier_address2'].'<br/>'.$data['supplier_town'].', '.$data['supplier_country'].', '.$data['supplier_postcode'].'</div><div><b>Email :</b> '.$data['supplier_email'].'</div></div>';
				$tempalte  .= '</td>';
				$tempalte  .= '<td style=" width: 40%;text-align:left; padding:15px;"><div style="font-weight: bold;border-bottom: 1px solid #ccc;padding-bottom: 5px;margin-bottom: 10px;">Locum Information</div><div><span><b>Name :</b> </span>'.ucfirst($data['your_name']).'</div><div><span><b>Email :</b> </span>'.$data['your_email'].'</div><div><b>Invoice number : </b> '.$invoiceno.'</div><div><b>Invoice Date :</b> '.date("d/m/Y").'</div><!--<div>Due Date :
				 '.date("d/m/Y").'</div>--></td>';
				$tempalte  .= '</tr>';
				$tempalte  .= '</tbody>';
				$tempalte  .= '</table>';
				$tempalte  .= '</div>';

				$tempalte  .= '<div style="float: left; width: 100%;">';
				$tempalte .= '<table style="border-top: 2px solid #dedede; width: 100%;border-collapse:collapse;">'; //class="table table-striped"
				$tempalte .= '<thead style="background: #e0e0e0;">';
				$tempalte .= '<tr style="height: 45px; border-bottom: 2px solid #dedede;">';
				$tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;">Job No.</th>';
				/*$tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;">Title</th>';
				if($jobdata['description'] != '')
				    $tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;">Description</th>';*/
				$tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;">Price</th>';
				$tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;">Amount</th>';
				$tempalte .= '</tr>';
				$tempalte .= '</thead>';
				$tempalte .= '<tbody>';
				$tempalte .= '<tr style="height: 45px;">';
				$tempalte .= '<td style="text-align: center; padding: 15px 0;">'.$jobdata['job_id'].'</td>';
				/*$tempalte .= '<td style="text-align: center; padding: 15px 0;">'.$jobdata['job_title'].'</td>';
				if($jobdata['description'] != '')
				    $tempalte .= '<th style="text-align: center; padding: 15px 0;">'.$jobdata['description'].'</th>';*/
				$tempalte .= '<td style="text-align: center; padding: 15px 0;">'.$currency.$financeHelper->setPriceFormate($jobdata['job_rate']).'</td>';
				$tempalte .= '<td style="text-align: center; padding: 15px 0;">'.$currency.$financeHelper->setPriceFormate($jobdata['job_rate']).'</td>';
				$tempalte .= '</tr>';

				$tempalte  .= '<tr style="height: 45px;">';
				$tempalte  .= '<td></td>';
				/*$tempalte  .= '<td></td>';
				if($jobdata['description'] != '')
				$tempalte  .= '<td></td>';*/
				$tempalte  .= '<td  style="text-align: center;border-top: 2px solid black; padding: 20px 0;"><b>TOTAL DUE</b></td>';
				$tempalte  .= '<td  style="text-align: center;border-top: 2px solid black; padding: 20px 0;"><b>'.$currency.$financeHelper->setPriceFormate($jobdata['job_rate']).'</b></td>';
				$tempalte  .= '</tr>'; 
				$tempalte  .= '</tbody>';
				$tempalte  .= '</table>';
				$tempalte  .= '</div>';
				$tempalte  .= '</div>';
				if (!empty($bank_details_array)) {
				    $is_bank_details = true;
				    $tempalte .= '<table style="border-top: 2px solid #dedede; width: 100%;border-collapse:collapse;">'; //class="table table-striped"
				    $tempalte .= '<thead style="background: #e0e0e0;">';
				    $tempalte .= '<tr style="height: 45px; border-bottom: 2px solid #dedede;">';
				    $tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;" colspan="2">Bank Details</th>';
				    $tempalte .= '<th style="text-align: center;border-bottom:2px solid #dedede;"></th>';
				    $tempalte .= '</tr>';
				    $tempalte .= '</thead>';
				    $tempalte .= '<tbody>';
				    $tempalte .= '<tr style="height: 35px;">';
				    $tempalte  .= '<td  style="text-align: center;border-bottom: 1px solid #e4e4e4;padding: 5px 0;"><b>Account Name <span style="float: right;">:</span></b></td>';
				    $tempalte  .= '<td  style="text-align: center;border-bottom: 1px solid #e4e4e4;padding: 5px 0;">'.$bank_details_array['acc_name'].'</td>';
				    $tempalte .= '</tr>';
				    $tempalte .= '<tr style="height: 35px;">';
				    $tempalte  .= '<td  style="text-align: center;border-bottom: 1px solid #e4e4e4;padding: 5px 0;"><b>Account Number <span style="float: right;">:</span></b></td>';
				    $tempalte  .= '<td  style="text-align: center;border-bottom: 1px solid #e4e4e4;padding: 5px 0;">'.$bank_details_array['acc_number'].'</td>';
				    $tempalte .= '</tr>';
				        $tempalte .= '<tr style="height: 35px;">';
				    $tempalte  .= '<td  style="text-align: center;padding: 5px 0;"><b>Account Sort Code <span style="float: right;">:</span></b></td>';
				    $tempalte  .= '<td  style="text-align: center;padding: 5px 0;">'.$bank_details_array['acc_sort_code'].'</td>';
				    $tempalte .= '</tr>';
				    $tempalte .= '</tbody>';
				    $tempalte .= '</table>';
				}
				$tempalte  .= '</section>';
				$tempalte  .= '</div></div></div></div></div>';
				
				$sts = $jobmailController->invoiceMail($tempalte,$data['supplier_email'],$data['supplier_name'],$invoiceno,$data['your_id'],$is_bank_details,$adapter);

				if($sts){
				    $financeHelper->updateIncomeInvoice($jobdata['id'],$invoiceno);
				    return 1;
				}else{
					return 0;
				}
			}else{
				return json_encode($userdata);
			}			
		}

		public function get_supplier_store_list($user_data){
			$uid = $user_data['user_id'];
			$store_name = $user_data['store_name'];
			$suppliercollection = new SupplierCollection();
			$dataSupplier = $suppliercollection->searchLocumSupplier($uid,$store_name);
			return json_encode($dataSupplier);
		}

		public function set_bank_details($user_data){
			$bankModel 		= new BankModel();
			if(isset($user_data['data']['bank_id']) && $user_data['data']['bank_id'] != 0){
				//update Bank details
				 $bankData = array(
			    		'bank_id'		=> $user_data['data']['bank_id'], 	    		
			    		'acc_name' 		=> $user_data['data']['supplier_account_name'], 
			    		'acc_number' 	=> $user_data['data']['supplier_account_no'], 
			    		'acc_sort_code' => $user_data['data']['supplier_account_sortcode']	    		
			    	);
			    $lastId = $bankModel->save($bankData);
			}else{
				//insert bank details
				$bankData = array(
		    		'user_id'			=> $user_data['user_id'],	    		
		    		'acc_name' 			=> $user_data['data']['supplier_account_name'], 
		    		'acc_number' 		=> $user_data['data']['supplier_account_no'], 
		    		'acc_sort_code' 	=> $user_data['data']['supplier_account_sortcode']	    		
		    	);
		    	$lastId = $bankModel->save($bankData);		    	
			}
			return $lastId;
		}

		public function get_bank_details($user_data){
			$bankcollection = new BankCollection();
			$data = $bankcollection->getBankInfoByUserId($user_data['user_id']);		
			return json_encode($data);
		}

	}