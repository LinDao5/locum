<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace GcFrontend\Helper;
use Zend\Db\Sql\Sql;
use Gc\Mvc\Controller\Action;
use GcFrontend\Controller\DbController as DbController;

/**
 * Index Helper for module Application
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Helper
 */
class FinanceHelper extends Action
{   
    public function getAdapter()
    {
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        return $adapter;
    }

    public function getAllIncome($uid , $financialYear , $filter , $openinvoice = false , $cat = null , $isbank = null , $supplier = null)
    {
        $adapter = $this->getAdapter();
		$financeyear = '' ;
		if($financialYear != null && $filter == 'year'){ 
		$financialYear_3 = $financialYear - 2 ;
            	$financeyear = "AND (YEAR(fin.created_at) <=  '$financialYear' AND  YEAR(fin.created_at) >=  '$financialYear_3')";}else{
			$financeyear = "AND YEAR(fin.created_at) =  '$financialYear'";
		}
		if($openinvoice == true){
			$financeyear .= " AND (bank IS NULL or bank = '') ";
		}if($cat != null){
			$financeyear .= " AND income_type = $cat";
		}if($isbank != null){
			$financeyear .= " AND bank = '1' ";
		}if($supplier != null){
			$financeyear .= " AND supplier = '".$supplier."' ";
		}
        $sqlAllincome ="SELECT f.trans_id ,f.trans_type  ,fin.* FROM finance f, finance_income_table fin WHERE fin.fre_id = '$uid' AND fin.id = f.trans_type_id AND f.trans_type = '1' $financeyear ";	
		$allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE); 
        $allincome = $allincomeObj->toArray();
        return $allincome;
    }

    /* Get all Expense by fre ID */
    public function getAllExpense($uid , $financialYear , $filter , $cat = null , $isbank = null)
    {
        $adapter = $this->getAdapter();
		$financeyear = '' ;
		if($financialYear != null && $filter == 'year'){
		 $financialYear_3 = $financialYear - 2 ;
            	$financeyear = "AND (YEAR(ex.created_at) <=  '$financialYear'  AND  YEAR(ex.created_at) >=  '$financialYear_3')";}else{
			$financeyear = "AND YEAR(ex.created_at) =  '$financialYear'";
		}if($cat != null){
			$financeyear .= " AND expense_type_Id = $cat";
		}if($isbank != null){
			$financeyear .= " AND bank = '1' ";
		}
        $sqlAllExpense="SELECT f.trans_id, f.trans_type ,ex.* FROM finance f, finance_expense_table ex WHERE ex.fre_id = '$uid' AND ex.id = f.trans_type_id AND f.trans_type = '2' $financeyear";
        $allExpenseObj = $adapter->query($sqlAllExpense, $adapter::QUERY_MODE_EXECUTE); 
        $allExpense = $allExpenseObj->toArray();
       /* echo "<pre>";
        print_r($allExpense);
        echo "</pre>";*/
        return $allExpense;
    }

    /* Get VAT for finance */
    public function getVatRate($gross)
    {
        $vatRate = 0.20; // 20 % vat 
        $vatCost = $vatRate * $gross;
        return $this->setPriceFormate($vatCost);
    }

    /* Get Net for finance */
    public function getNetRate($gross)
    {
        $vatRate = $this->getVatRate($gross);
        $netRate = $gross-$vatRate;
        return $this->setPriceFormate($netRate);
    }

    /* Formate price */
    public function setPriceFormate($price)
    {
        return number_format($price,2);
    }
	
	/* Get user register year */
    public function getCreatedDate($uid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT created_at FROM user WHERE id = '$uid' AND active = '1'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE); 
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj->created_at;
    }

    public function getCreatedYear($uid='')
    {
        $year = date('Y', strtotime($this->getCreatedDate($uid)) );
        return $year;
    }
	 
	 public function getCreatedMonth($uid)
    {
        $month = date('n', strtotime($this->getCreatedDate($uid)) );
        return $month;
    }
	
	
	public function chart_getAllIncome($uid , $financialYear , $filterType , $category = null , $supplier = null)
    {
        $adapter = $this->getAdapter();		
		if($filterType == 'year'){
			$financialYear_3 = $financialYear - 2 ;
			$cat = '' ;
			if($category != null){
				$cat = " and income_type = '".$category."'";
			}
			if($supplier != null){
				$cat .= " and supplier = '".$supplier."'";
			}

			$sqlAllincome ="SELECT YEAR(created_at) as year, SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and (YEAR(created_at) <=  '$financialYear'  AND YEAR(created_at) >=  '$financialYear_3')  $cat GROUP BY YEAR(created_at)";
		
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE); 
        $allincome = $allincomeObj->toArray();
		$dataX = $data1 = $data2 = '';
		foreach ($allincome as $income){
			if($filterType == 'year'){
				$dataX[] = $income['year'] ;
			}else{
				$dataX[] = $income['month_nm'] ;
			}
			
			$data1[] = $income['bank_yes'] ;
			$data2[] = $income['bank_no'] ;			
		}		
		$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		);	
		
		}else if($filterType == 'month'){			
			
	$AllMonth = $this->getMonthrangeByyear($financialYear , $uid);
	$dataX = $data1 = $data2 = $all_data = '';	
	if($AllMonth != ''){
	foreach($AllMonth as $key => $Month1){
		
		
	$monthWise = $this->getAllIncomeBymonth($uid ,$financialYear,$key ,$category,$supplier );
	if($monthWise != ''){
	$dataX[] = @$monthWise['month_nm'] ;
	$data1[] = @$monthWise['bank_yes'] ;
	$data2[] = @$monthWise['bank_no'] ;
	}else{
	$dataX[] = $Month1 ;
	$data1[] = 0 ;
	$data2[] = 0 ;
	}	
	}
		}
	$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		);	
	}   			
        return $all_data;
    }	
	
		public function getAllIncomeBymonth($uid , $financialYear ,$filtermonth , $category = null , $supplier = null )
    {
        $adapter = $this->getAdapter();	
			$cat = '' ;
			if($category != null){
				$cat = "and income_type = '".$category."'";
			}
        if($supplier != null){
        $cat .= " and supplier = '".$supplier."'";
    }

		$sqlAllincome ="SELECT MONTHNAME(created_at) as month_nm , SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' and MONTH(created_at) =  '$filtermonth ' $cat GROUP BY YEAR(created_at) , MONTH(created_at)";		
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE); 
        $allincome = $allincomeObj->current();
		if($allincome){
			 return $allincome;
		}else{
			 return false;
		}       
    }
	
	//RP -- not in use 10-05-17 
		public function old_chart_getAllIncome($uid , $financialYear , $filterType )
    {
        $adapter = $this->getAdapter();		
		if($filterType == 'year'){
			$sqlAllincome ="SELECT YEAR(created_at) as year, SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) <=  '$financialYear' GROUP BY YEAR(created_at)";
			
		}else if($filterType == 'month'){
			
			$sqlAllincome ="SELECT YEAR(created_at) as year, MONTH(created_at) as month ,MONTHNAME(created_at) as month_nm , SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' GROUP BY YEAR(created_at) , MONTH(created_at)";
		}   
		
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE); 
        $allincome = $allincomeObj->toArray();
		$dataX = $data1 = $data2 = '';
		foreach ($allincome as $income){
			if($filterType == 'year'){
				$dataX[] = $income['year'] ;
			}else{
				$dataX[] = $income['month_nm'] ;
			}
			
			$data1[] = $income['bank_yes'] ;
			$data2[] = $income['bank_no'] ;			
		}		
		$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		);		
        return $all_data;
    }
	
	
	
	public function chart_getAllExpense($uid , $financialYear , $filterType , $category = null )
    {
        $adapter = $this->getAdapter();		
		if($filterType == 'year'){
			$financialYear_3 = $financialYear - 2 ;
			$cat = '' ;
			if($category != null){
				$cat = "and expense_type_id = '".$category."'";
			}
			
			$sqlexpense ="SELECT YEAR(created_at) as year, SUM(IF(bank = 1 , cost , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , cost)) AS bank_no FROM finance_expense_table where fre_id = '$uid' and (YEAR(created_at) <=  '$financialYear'  AND YEAR(created_at) >=  '$financialYear_3') $cat GROUP BY YEAR(created_at)";
			$allexpenseObj = $adapter->query($sqlexpense, $adapter::QUERY_MODE_EXECUTE); 
			$allexpense = $allexpenseObj->toArray();
		$dataX = $data1 = $data2 = $all_data = '';
		foreach ($allexpense as $expense){
			if($filterType == 'year'){
				$dataX[] = $expense['year'] ;
			}else{
				$dataX[] = $expense['month_nm'] ;
			}
			
			$data1[] = $expense['bank_yes'] ;
			$data2[] = $expense['bank_no'] ;			
		}		
		$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		);			
		
		}else if($filterType == 'month'){

$AllMonth = $this->getMonthrangeByyear($financialYear , $uid);
	$dataX = $data1 = $data2 = $all_data = '';	
	if($AllMonth != ''){
	foreach($AllMonth as $key => $Month1){
	$monthWise = $this->getAllExpenseBymonth($uid ,$financialYear,$key ,$category );
	if($monthWise != ''){
	$dataX[] = @$monthWise['month_nm'] ;
	$data1[] = @$monthWise['bank_yes'] ;
	$data2[] = @$monthWise['bank_no'] ;
	}else{
	$dataX[] = $Month1 ;
	$data1[] = 0 ;
	$data2[] = 0 ;
	}	
		}
		}
	$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		);	
	}   
        return $all_data;
    }
	
	
		
	public function getAllExpenseBymonth($uid , $financialYear ,$filtermonth ,$category = null  )
    {
        $adapter = $this->getAdapter();	
		
		 $cat = '' ;
			if($category != null){
				$cat = "and expense_type_id = '".$category."'";
			}
		
		$sqlAllExpense ="SELECT YEAR(created_at) as year, MONTHNAME(created_at) as month_nm , SUM(IF(bank = 1 , cost , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , cost)) AS bank_no FROM finance_expense_table where fre_id = '$uid' and YEAR(created_at) = '$financialYear' and MONTH(created_at) =  '$filtermonth' $cat  GROUP BY YEAR(created_at) , MONTH(created_at)";
	    $allExpenseObj = $adapter->query($sqlAllExpense, $adapter::QUERY_MODE_EXECUTE); 
        $allExpense = $allExpenseObj->current();
		if($allExpense){
			 return $allExpense;
		}else{
			 return false;
		}       
    }
	
	
	
	public function chart_getAlltrans($uid , $financialYear , $filterType , $isbank = null)
    {
        $adapter = $this->getAdapter();		
		if($filterType == 'year'){
		$financialYear_3 = $financialYear - 2 ;
        $con_bank = "";
        if($isbank != null){
            $con_bank = " AND bank = '1' " ;
        }
			$sqlAlltrans ="SELECT YEAR(R.dat) AS year ,SUM(IF(R.trans_type = 1 ,money , 0 )) AS income , SUM(IF(R.trans_type = 2 ,money , 0 )) AS expence FROM
(SELECT fre_id , trans_type , trans_id , id , job_rate AS money , fin.created_at AS dat FROM finance_income_table fin JOIN finance ON  finance.trans_type_id = fin.id WHERE finance.trans_type = 1 $con_bank  UNION
SELECT fre_id , trans_type , trans_id , id , cost AS money , ex.created_at AS dat FROM finance_expense_table ex JOIN finance ON  finance.trans_type_id = ex.id WHERE finance.trans_type = 2 $con_bank ) AS R
WHERE R.fre_id = '$uid' AND  (YEAR(R.dat) <=  '$financialYear' AND YEAR(R.dat) >=  '$financialYear_3') GROUP BY YEAR(R.dat) ";

		$alltransObj = $adapter->query($sqlAlltrans, $adapter::QUERY_MODE_EXECUTE);
        $alltarns = $alltransObj->toArray();
		$dataX = $data1 = $data2 = $all_data = '';
		foreach ($alltarns as $trans){
			if($filterType == 'year'){
				$dataX[] = $trans['year'] ;
			}else{
				$dataX[] = $trans['month_nm'] ;
			}
			
			$data1[] = $trans['income'] ;
			$data2[] = $trans['expence'] ;
			$data3[] = $trans['income'] - $trans['expence'] ;
		}
		$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		'3' => $data3,
		);
		}else if($filterType == 'month'){			
	
		$AllMonth = $this->getMonthrangeByyear($financialYear , $uid);
	$dataX = $data1 = $data2 = $all_data ='';	
	if($AllMonth != ''){
	foreach($AllMonth as $key => $Month1){
	$monthWise = $this->getAllTransBymonth($uid ,$financialYear,$key ,$isbank );
	if($monthWise != ''){
	$dataX[] = @$monthWise['month_nm'] ;
	$data1[] = @$monthWise['income'] ;
	$data2[] = @$monthWise['expence'] ;
	$data3[] = $monthWise['income'] - $monthWise['expence'] ;
	}else{
	$dataX[] = $Month1 ;
	$data1[] = 0 ;
	$data2[] = 0 ;
	$data3[] = 0 ;
	}
	}
		}
	$all_data = array(
		'0' => $dataX,
		'1' => $data1,
		'2' => $data2,
		'3' => $data3,
		);
		}   	
        return $all_data;
    }
	
	
	// start from here by ... RP
	public function getAllTransBymonth($uid , $financialYear ,$filtermonth , $isbank = null)
    {
        $adapter = $this->getAdapter();
        $con_bank = "";
        if($isbank != null){
            $con_bank = " AND bank = '1' " ;
        }

		$sqlAllincome ="SELECT MONTHNAME(R.dat) AS month_nm ,SUM(IF(R.trans_type = 1 ,money , 0 )) AS income , SUM(IF(R.trans_type = 2 ,money , 0 )) AS expence FROM
(SELECT fre_id , trans_type , trans_id , id , job_rate AS money , fin.created_at AS dat FROM finance_income_table fin JOIN finance ON  finance.trans_type_id = fin.id WHERE finance.trans_type = 1 $con_bank  UNION
SELECT fre_id , trans_type , trans_id , id , cost AS money , ex.created_at AS dat FROM finance_expense_table ex JOIN finance ON  finance.trans_type_id = ex.id WHERE finance.trans_type = 2 $con_bank ) AS R
WHERE R.fre_id = '$uid' AND YEAR(R.dat) = '$financialYear' AND MONTH(R.dat) = '$filtermonth'  GROUP BY YEAR(R.dat) , MONTH(R.dat)";

        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE); 
        $allincome = $allincomeObj->current();
		if($allincome){
			 return $allincome;
		}else{
			 return false;
		}       
    }
	
	
    public function getIncomeByuser($uid , $year = null)
    {
		$adapter = $this->getAdapter();
        $con = '';
        if($year != null){  $con = " AND YEAR(job_date) = '$year' "; }
        $select = "SELECT SUM(job_rate) as job_rate  FROM `finance_income_table` WHERE `fre_id` = '$uid' $con";
		$alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE); 
		return $alltarns = $alltransObj->current();
    }
	
    public function getExpenceByuser($uid , $year = null)
    {
		$adapter = $this->getAdapter();
        $con = '';
        if($year != null){  $con = " AND YEAR(job_date) = '$year' "; }
        $select = "SELECT SUM(cost) as cost  FROM `finance_expense_table` WHERE `fre_id` = '$uid' $con";
		$alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE); 
		return $alltarns = $alltransObj->current();
    }
	
	    public function getdataDifference($date)
    {
		$date1=date_create($date);
	    $date2=date_create(date("Y-m-d"));
		$diff=date_diff($date1,$date2);
		return $diff->format("%a"); 
    }
	
	public function getExpencetype($id = null , $all = false)
    {
		$adapter = $this->getAdapter();
		$con = ''; 
		if($id != null){
			$con = " WHERE `id` = '$id'";
		}
		$select = "SELECT id , expense as cat , expense_colour FROM `expense_type` $con";
		$extypeObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE); 
		if($id != null){
			$extype = $extypeObj->current();
			if($all == true){
			return $extype;	
			}else{
			return $extype['cat'];	
			}			
		}else{
			return $extype = $extypeObj->toArray();	
		}
		
    }
	

	
	public function getIncometype($id = null)
    {			
	$catdata = array( 
	'1' => 'Income',
	'2' => 'Bonus',
	);
	if($id != null){
		return $catdata[$id];
	}else{
		return $catdata ;
	}		
    }
	public function getMonthrangeByyear($year , $uid = null){
	 $year = $year;
	 $year_curr = date('Y');
	 $registerYear = @$uid!= null ? $this->getCreatedYear($uid) : null;
		if($registerYear <= $year && $year_curr >= $year ){
		$registerMonth = @$uid!= null ? $this->getCreatedMonth($uid) : null;
		if($year == $year_curr){ $mon_end =  date('n'); }else{ $mon_end =  12; }
		if($year == $registerYear){ $mon_start = $registerMonth; }else{ $mon_start =  1; }
		for ($m=$mon_start; $m<=$mon_end; $m++) {
			 $month[$m] = date('F', mktime(0,0,0,$m, 1, $year));
			// echo $month. '<br>';
			 } 
			 return $month ;
		}	
	}


		public function GetchartExpenseBYcondition($uid , $financialYear)
    {
        $adapter = $this->getAdapter();	
			$sqlexpense ="SELECT YEAR(created_at) as year, expense_type_id , SUM(cost) AS cost FROM finance_expense_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' GROUP BY YEAR(created_at) , expense_type_id";
			$allexpenseObj = $adapter->query($sqlexpense, $adapter::QUERY_MODE_EXECUTE); 
			$allexpense = $allexpenseObj->toArray();
		$dataX = $data1 = $data2 = $cat_data ='';
		foreach ($allexpense as $expense){			
		$typedetail = $this->getExpencetype($expense['expense_type_id'],true);
		$cat_data[] =	array(		
		/* 'y' =>  $expense['cost'],
		 'label' => $typedetail['cat'],
		 'color' => $typedetail['expense_colour'],
		 'name' => $typedetail['cat']	*/	
			'value' => $expense['cost'],
			'color' => $typedetail['expense_colour'],
			'highlight' => $typedetail['expense_colour'],
			'label' => $typedetail['cat']
		);	
		}	
        return $cat_data;
    }

		public function GetchartIncomeBYcondition($uid , $financialYear)
    {
        $adapter = $this->getAdapter();	
			$sqlincome ="SELECT YEAR(created_at) as year, income_type , SUM(job_rate) AS job_rate FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' GROUP BY YEAR(created_at) , income_type";
			$allincomeObj = $adapter->query($sqlincome, $adapter::QUERY_MODE_EXECUTE); 
			$allincome = $allincomeObj->toArray();
		$dataX = $data1 = $data2 =  $cat_data = '';
		foreach ($allincome as $income){			
		$typedetail = $this->getIncometype($income['income_type']);
		$cat_data[] =	array(		
		    'value' => $income['job_rate'],
			'color' => @$income['income_type'] == 1 ? '#8064A2' : '#4F81BD',
			'highlight' => @$income['income_type'] == 1 ? '#8064A2' : '#4F81BD',
			'label' => $typedetail
		);	
		}	
        return $cat_data;
    }


    public function Getlast3year($uid,$selectedyear=null){
        $year_curr = @$selectedyear ? $selectedyear : date('Y');
        $registerYear = @$uid!= null ? $this->getCreatedYear($uid) : null;
        $year = '' ;
        $j =1 ;
        for($i=$year_curr;$i>=$registerYear;$i--)
        {   $year[] = $i ;
            if($j == 3)break;
            ++$j;
        }
        return $year;
    }


    public function getWeek(){
        $days = array(
            '1' => 'Sunday',
            '2' => 'Monday',
            '3' => 'Tuesday',
            '4' => 'Wednesday',
            '5' => 'Thursday',
            '6' => 'Friday',
            '7' => 'Saturday',
        );
        return $days;
    }


    public function chartGetTransWeekly($uid , $financialYear , $transtype)
    {

        if($transtype == 'income'){
            $incomes = $this->getAllIncomeByweek($uid,$financialYear);
            if($incomes == null){ $incomes = array();  }
            $weeks = $this->getWeek();
            foreach($incomes as $income){
                $in_data[$income['dayno']] = array ( 'day' => $income['day_nm'] ,'bank_yes' =>$income['bank_yes'] ,'bank_no' => $income['bank_no'] );
            }
            foreach($weeks as $key => $val)
            {
                if(isset($in_data[$key])){
                    $trnsdata1[] = $in_data[$key];
                }else{
                    $trnsdata1[] = array(
                        'day' => $val,
                        'bank_yes' => 0,
                        'bank_no' => 0,
                    );
                }
            }
            foreach($trnsdata1 as $in){
                $dataX[] = $in['day'];
                $data1[] = $in['bank_yes']; // this is use for all data
                $data2[] = $in['bank_no'];
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
            );


        }else if($transtype == 'expense'){
            $expenses = $this->getAllExpesneByweek($uid,$financialYear);
            if($expenses == null){ $expenses = array();  }
            $weeks = $this->getWeek();
            foreach($expenses as $expense){
                $expense_data[$expense['dayno']] = array ( 'day' => $expense['day_nm'] ,'bank_yes' =>$expense['bank_yes'] ,'bank_no' => $expense['bank_no'] );
            }
            foreach($weeks as $key => $val)
            {
                if(isset($expense_data[$key])){
                    $trnsdata1[] = $expense_data[$key];
                }else{
                    $trnsdata1[] = array(
                        'day' => $val,
                        'bank_yes' => 0,
                        'bank_no' => 0,
                    );
                }
            }
            foreach($trnsdata1 as $ex){
                $dataX[] = $ex['day'];
                $data1[] = $ex['bank_yes'];  // this is use for all data
                $data2[] = $ex['bank_no'];
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
            );
        }
        return array('data'=> $trnsdata1 , 'chart'=> $all_data);
        // return $all_data;
    }


    public function getAllIncomeByweek($uid , $financialYear ,$filterweekday = null , $category = null )
    {
        $adapter = $this->getAdapter();
        $con = '' ;
        if($category != null){
            $con = " and income_type = '".$category."'";
        }if($filterweekday != null){
        $con .= " and DAYOFWEEK(created_at) = '".$filterweekday."'";
    }
        $sqlAllincome ="SELECT DAYOFWEEK(created_at) as dayno , DAYNAME(created_at) as day_nm , SUM(job_rate) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) = '$financialYear' $con GROUP BY YEAR(created_at) , DAYOFWEEK(created_at)";
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        if($filterweekday != null){ $allincome = $allincomeObj->current(); }else{  $allincome = $allincomeObj->toArray();  }

        if($allincome){
            return $allincome;
        }else{
            return null;
        }
    }

    public function getAllExpesneByweek($uid , $financialYear ,$filterweekday = null , $category = null )
    {
        $adapter = $this->getAdapter();
        $con = '' ;
        if($category != null){
            $con = " and expense_type_id = '".$category."'";
        }if($filterweekday != null){
        $con .= " and DAYOFWEEK(created_at) = '".$filterweekday."'";
    }
        $sqlAllexpense ="SELECT DAYOFWEEK(created_at) as dayno , DAYNAME(created_at) as day_nm , SUM(cost) AS bank_yes , SUM(IF(bank = 1 , 0 , cost)) AS bank_no FROM finance_expense_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' $con GROUP BY YEAR(created_at) , DAYOFWEEK(created_at)";
        $allexpenseObj = $adapter->query($sqlAllexpense, $adapter::QUERY_MODE_EXECUTE);
        if($filterweekday != null){ $allexpense = $allexpenseObj->current(); }else{  $allexpense = $allexpenseObj->toArray();  }
        if($allexpense){
            return $allexpense;
        }else{
            return null;
        }
    }


    public function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
    }

    public function random_color() {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }


    public function GetchartIncomeBYlocation($uid , $financialYear)
    {
        $adapter = $this->getAdapter();
        $sqlincome ="SELECT YEAR(created_at) as year, location , SUM(job_rate) AS job_rate FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' GROUP BY YEAR(created_at) , location";
        $allincomeObj = $adapter->query($sqlincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        $dataX = $data1 = $data2 =  $cat_data = '';
        foreach ($allincome as $income){
            $color = $this->random_color();
            $cat_data[] =	array(
                'value' => $income['job_rate'],
                'color' => "#".$color,
                'highlight' => "#".$color,
                'label' => $income['location']
            );
        }
        return $cat_data;
    }

    public function chartGetJobWeekly($uid , $financialYear)
    {

            $jobs = $this->getAllJobByweek($uid,$financialYear);
            if($jobs == null){ $jobs = array();  }
            $weeks = $this->getWeek();
            foreach($jobs as $job){
                $in_data[$job['dayno']] = array ( 'day' => $job['day_nm'] ,'jobs' =>$job['jobs'] );
            }
            foreach($weeks as $key => $val)
            {
                if(isset($in_data[$key])){
                    $trnsdata1[] = $in_data[$key];
                }else{
                    $trnsdata1[] = array(
                        'day' => $val,
                        'jobs' => 0,
                    );
                }
            }
            foreach($trnsdata1 as $in){
                $dataX[] = $in['day'];
                $data1[] = $in['jobs'];
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
            );

        return array('data'=> $trnsdata1 , 'chart'=> $all_data);
    }


    public function getAllJobByweek($uid , $financialYear ,$filterweekday = null )
    {
        $adapter = $this->getAdapter();
        $con = '' ;
        if($filterweekday != null){
        $con .= " and DAYOFWEEK(created_at) = '".$filterweekday."'";
        }
        $sqlAlljob ="SELECT DAYOFWEEK(created_at) as dayno , DAYNAME(created_at) as day_nm , count(job_id) AS jobs FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) = '$financialYear' $con GROUP BY YEAR(created_at) , DAYOFWEEK(created_at)";
        $alljobObj = $adapter->query($sqlAlljob, $adapter::QUERY_MODE_EXECUTE);
        if($filterweekday != null){ $alljob = $alljobObj->current(); }else{  $alljob = $alljobObj->toArray();  }
        if($alljob){
            return $alljob;
        }else{
            return null;
        }
    }

   public function GetchartIncomeBYsupplier($uid , $financialYear)
    {
        $adapter = $this->getAdapter();
        $sqlincome ="SELECT YEAR(created_at) as year, supplier , SUM(job_rate) AS job_rate FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' GROUP BY YEAR(created_at) , supplier";
        $allincomeObj = $adapter->query($sqlincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        $dataX = $data1 = $data2 =  $cat_data = '';
        foreach ($allincome as $income){
            $color = $this->random_color();
            $cat_data[] =	array(
                'value' => $income['job_rate'],
                'color' => "#".$color,
                'highlight' => "#".$color,
                'label' => $income['supplier']
            );
        }
        return $cat_data;
    }
    
        public function loginUserDate($uid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT created_at , firstname , lastname , email , info.mobile , info.address , info.city , info.company FROM user join user_extra_info as info on info.uid = user.id  WHERE user.id = '$uid' AND active = '1'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getIncomefinancedata($id , $uid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT * FROM finance_income_table WHERE id = '$id' AND fre_id = '$uid'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getWebsitejob($job_id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT * FROM job_post  WHERE job_id = '$job_id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getPrivatejob($job_id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT pv_id , f_id , emp_name , emp_email , priv_job_title, priv_job_rate , priv_job_start_date ,  priv_job_location from freelancer_private_job WHERE pv_id = '$job_id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getjobDataInvoice($id , $uid)
    {
       $dt1 =  $this->getIncomefinancedata($id , $uid);
        if($dt1['job_id'] != ''){
            if($dt1['job_type'] == '1'){
                $dt2 =  $this->getWebsitejob($dt1['job_id']);

                return  array(
                    'id' => $dt1['id'],
                    'job_id' => $dt1['job_id'],
'invoice_id' => $dt1['invoice_id'],
                    'job_title' => $dt2['job_title'],
                    'job_rate' => $dt1['job_rate'],
                    'description' => $dt2['job_post_desc'],
                );
                //return   array_merge($dt1,$dt2);
            }else{
                $dt3 =  $this->getPrivatejob($dt1['job_id']);
                return  array(
                    'id' => $dt1['id'],
                    'job_id' => $dt1['job_id'],
'invoice_id' => $dt1['invoice_id'],
                    'job_title' => $dt3['priv_job_title'],
                    'job_rate' => $dt1['job_rate'],
                    'description' => '',
                );
            }
        }else{
            return $dt1;
        }
    }

    public function generateInvoicenum($semail,$adminEmail,$amount,$uid)
    {
        $adapter = $this->getAdapter();

        $sqlGerRegisterDate="INSERT INTO `send_invoice`(to_email, from_email,amount , user_by) VALUES ('$semail','$adminEmail','$amount','$uid')";
        $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        return $adapter->getDriver()->getLastGeneratedValue();
     }

    public function updateIncomeInvoice($id,$invoiceid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="UPDATE finance_income_table SET invoice_id='$invoiceid' WHERE id='$id'";
        $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
     }


    public function getOnlyFinanceincome($id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT * from finance_income_table WHERE id = '$id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }
    public function getOnlyFinanceexpence($id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate="SELECT * from finance_expense_table WHERE id = '$id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getWebsiteJobdetail($job_id)
    {
        $adapter = $this->getAdapter();
        $sqlGetjob = "SELECT job_id, e_id, job_title,  STR_TO_DATE( `job_date`, '%d/%m/%Y') as job_date, job_post_desc, job_rate, job_type, job_address, job_region, store_id , esl.emp_store_name as store_nm , user.firstname as first_nm , user.lastname as last_nm FROM job_post as jp left join employer_store_list as esl on jp.store_id = esl.emp_st_id left join user as user on user.id = jp.e_id	 WHERE job_id = '$job_id'";
        $getjobs = $adapter->query($sqlGetjob, $adapter::QUERY_MODE_EXECUTE);
        $getjob = $getjobs->current();
        return $getjob ;

    }



    public function getExpenceLunchtravel($uid,$year=null)
    {
        $adapter = $this->getAdapter();
        $con = '';
        if($year != null){  $con = " AND YEAR(job_date) = '$year' "; }
        $select = "SELECT SUM(cost) as cost  FROM `finance_expense_table` WHERE `fre_id` = '$uid' and expense_type_id IN ('1','3') $con";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }


    public function getfinanceIncomeUser($year=null)
    {
        $adapter = $this->getAdapter();
        $con = '';
        if($year != null){  $con = " where YEAR(job_date) = '$year' "; }
        $select = "SELECT fre_id as fre_id FROM finance_income_table $con GROUP BY fre_id ";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->toArray();
    }

    public function InsertFinanceprofitloss($data)
    {
        $adapter = $this->getAdapter();
        $prifitlossdata = $this->getFinanceprofitloss($data['fre_id'],$data['year']);
       if(empty($prifitlossdata) && $prifitlossdata == ''){
            $sqlGerRegisterDate="INSERT INTO `finance_profit_loss`(`fre_id`, `revenue`, cos , othercost , `income_tax`, `interest_income`, `financial_year`) VALUES ('".$data['fre_id']."','".$data['revenue']."','".$data['cos']."','".$data['othercost']."','".$data['totaltax']."','".$data['interestincome']."','".$data['year']."')";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
            return $adapter->getDriver()->getLastGeneratedValue();
        }else{
            $sqlGerRegisterDate="UPDATE finance_profit_loss SET  interest_income = '".$data['interestincome']."'  WHERE financial_year = '".$data['year']."' and fre_id = '".$data['fre_id']."'";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        }
    }



    public function getFinanceprofitloss($uid,$year)
    {
        $adapter = $this->getAdapter();
        $select = "SELECT *  FROM `finance_profit_loss` WHERE `fre_id` = '$uid' and YEAR(create_at) = '$year'";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }


    public function InsertFinancebalance($data)
    {
        $inputdata = serialize(array('put1' => $data['put1'],'put2' => $data['put2'],'put3' => $data['put3'],'put4' => $data['put4'],'put6' => $data['put6'],'put7' => $data['put7']));
        $adapter = $this->getAdapter();
        $prifitlossdata = $this->getFinancebalancesheet($data['fre_id'],$data['year']);
        if(empty($prifitlossdata) && $prifitlossdata == ''){
            $sqlGerRegisterDate="INSERT INTO `finance_balancesheet`(`fre_id`, `income_tax`, `input_data`, `financial_year`) VALUES ('".$data['fre_id']."','".$data['totaltax']."','".$inputdata."','".$data['year']."')";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
            return $adapter->getDriver()->getLastGeneratedValue();
        }else{
            $sqlGerRegisterDate="UPDATE finance_balancesheet SET  input_data = '".$inputdata."'  WHERE financial_year = '".$data['year']."' and fre_id = '".$data['fre_id']."'";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function getFinancebalancesheet($uid,$year)
    {
        $adapter = $this->getAdapter();
        $select = "SELECT *  FROM `finance_balancesheet` WHERE `fre_id` = '$uid' and YEAR(create_at) = '$year'";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }



}
