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

    public function checkFinanceincome($job_id, $fre_id, $job_date, $income_type, $job_type)
    {
        $adapter = $this->getAdapter();
        $select = "SELECT *  FROM `finance_income_table` WHERE job_type = '$job_type' and job_id = '$job_id' and `fre_id` = '$fre_id' and job_date = '$job_date' and income_type  = '$income_type' ";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }

    public function getUserFinanceyearStartMonth($uid, $arr = false, $adapter = null)
    {
        if ($adapter == null) {
            $adapter = $this->getAdapter();
        }

        $sql_select = "SELECT *  FROM financial_year WHERE user_id  = '$uid' ";
        $allfyearObj = $adapter->query($sql_select, $adapter::QUERY_MODE_EXECUTE);
        $finyear = $allfyearObj->current();

        if ($arr == true) {
            if (empty($finyear)) {
                $finyear['month_start'] = 4;
                return $finyear;
            } else {
                return $finyear;
            }
        } else {

            if (empty($finyear['month_start']) || $finyear['month_start'] == '') {
                return 4; // April
            } else {
                return $finyear['month_start'];
            }
        }
    }


    public function getAllIncome($uid, $financialYear, $filter, $openinvoice = false, $cat = null, $isbank = null, $supplier = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);

        $financeyear = $havefinanceyear = '';
        if ($financialYear != null) {
            $financialYear = $this->getMonthFinancialYear($uid, $financialYear);
        }
        if ($financialYear != null && $filter == 'year') {
            $financialYear_3 = $financialYear - 2;
            $financialYear_3 = $this->getMonthFinancialYear($uid, $financialYear_3);

            $havefinanceyear = "HAVING (financial_year <=  '$financialYear' AND  financial_year  >=  '$financialYear_3')";
        } elseif ($financialYear != null) {
            $havefinanceyear = "HAVING financial_year =  '$financialYear'";
        }
        if ($openinvoice == true) {
            $financeyear .= " AND (bank IS NULL or bank = '') ";
        }
        if ($cat != null) {
            $financeyear .= " AND income_type = $cat";
        }
        if ($isbank != null) {
            $financeyear .= " AND bank = '1' ";
        }
        if ($supplier != null) {
            $financeyear .= " AND supplier = '" . $supplier . "' ";
        }

        $sqlAllincome = "SELECT f.trans_id ,f.trans_type  ,fin.* , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
      ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance f, finance_income_table fin WHERE fin.fre_id = '$uid' AND fin.id = f.trans_type_id AND f.trans_type = '1' $financeyear  $havefinanceyear order by f.trans_id desc";
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        return $allincome;
    }

    /* Get all Expense by fre ID */
    public function getAllExpense($uid, $financialYear, $filter, $cat = null, $isbank = null)
    {
        $adapter = $this->getAdapter();
        $financeyear = $havefinanceyear = '';
        $startmonth = $this->getUserFinanceyearStartMonth($uid);

        if ($financialYear != null) {
            $financialYear = $this->getMonthFinancialYear($uid, $financialYear);
        }
        if ($financialYear != null && $filter == 'year') {
            $financialYear_3 = $financialYear - 2;
            $financialYear_3 = $this->getMonthFinancialYear($uid, $financialYear_3);
            $havefinanceyear = "HAVING (financial_year <=  '$financialYear' AND  financial_year  >=  '$financialYear_3')";
        } elseif ($financialYear != null) {
            $havefinanceyear = "HAVING financial_year =  '$financialYear'";
        }
        if ($cat != null) {
            $financeyear .= " AND expense_type_Id = $cat";
        }
        if ($isbank != null) {
            $financeyear .= " AND bank = '1' ";
        }
        $sqlAllExpense = "SELECT f.trans_id, f.trans_type ,ex.* ,
 CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year
 FROM finance f, finance_expense_table ex WHERE ex.fre_id = '$uid' AND ex.id = f.trans_type_id AND f.trans_type = '2' $financeyear $havefinanceyear order by f.trans_id desc";
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
        $netRate = $gross - $vatRate;
        return $this->setPriceFormate($netRate);
    }

    /* Formate price */
    public function setPriceFormate($price)
    {
        return number_format($price, 2);
    }

    /* Get user register year */
    public function getCreatedDate($uid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT created_at FROM user WHERE id = '$uid' AND active = '1'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj->created_at;
    }

    public function getCreatedYear($uid = '')
    {
        $year = date('Y', strtotime($this->getCreatedDate($uid)));
        return $year;
    }

    public function getCreatedMonth($uid)
    {
        $month = date('n', strtotime($this->getCreatedDate($uid)));
        return $month;
    }


    public function chart_getAllIncome($uid, $financialYear, $filterType, $category = null, $supplier = null)
    {

        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);
        if ($filterType == 'year') {
            $financialYear_3 = $financialYear - 2;
            $financialYear = $this->getMonthFinancialYear($uid, $financialYear);
            $financialYear_3 = $this->getMonthFinancialYear($uid, $financialYear_3);
            $cat = '';
            if ($category != null) {
                $cat = " and income_type = '" . $category . "'";
            }
            if ($supplier != null) {
                $cat .= " and supplier = '" . $supplier . "'";
            }

            $sqlAllincome = "SELECT  SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no ,
CASE WHEN MONTH(job_date)>=$startmonth THEN CONCAT(YEAR(job_date), '-',YEAR(job_date)+1) ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year
FROM finance_income_table WHERE fre_id = '$uid' $cat GROUP BY financial_year HAVING (financial_year <= '$financialYear' AND financial_year >= '$financialYear_3')";

            $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
            $allincome = $allincomeObj->toArray();
            $dataX = $data1 = $data2 = '';
            foreach ($allincome as $income) {
                if ($filterType == 'year') {
                    $dataX[] = $income['financial_year'];
                } else {
                    $dataX[] = $income['month_nm'];
                }

                $data1[] = $income['bank_yes'];
                $data2[] = $income['bank_no'];
                $data3[] = $income['bank_yes'] + $income['bank_no'];
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
                '3' => $data3,
            );
        } else if ($filterType == 'month') {

            $AllMonth = $this->getMonthrangeByyear($financialYear, $uid);

            $dataX = $data1 = $data2 = $all_data = '';
            if ($AllMonth != '') {
                foreach ($AllMonth as $key => $Month1) {

                    $monthWise = $this->getAllIncomeBymonth($uid, $Month1['1'], $key, $category, $supplier);

                    $Month1[1] = substr($Month1['1'], 2, 2);
                    if ($monthWise != '') {
                        $dataX[] = @$monthWise['month_nm'] . ',' . $Month1['1'];
                        $data1[] = @$monthWise['bank_yes'];
                        $data2[] = @$monthWise['bank_no'];
                        $data3[] = $monthWise['bank_yes'] + $monthWise['bank_no'];
                    } else {
                        $dataX[] = $Month1;
                        $data1[] = 0;
                        $data2[] = 0;
                        $data3[] = 0;
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

    public function chart_getAllIncomeExpect($uid, $financialYear, $filterType, $category = null, $supplier = null)
    {

        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);
        if ($filterType == 'year') {
            $financialYear_3 = $financialYear - 2;
            $financialYear = $this->getMonthFinancialYear($uid, $financialYear);
            $financialYear_3 = $this->getMonthFinancialYear($uid, $financialYear_3);
            $cat = '';
            if ($category != null) {
                $cat = " and income_type = '" . $category . "'";
            }
            if ($supplier != null) {
                $cat .= " and supplier = '" . $supplier . "'";
            }

            $sqlAllincome = "SELECT  SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no ,
CASE WHEN MONTH(job_date)>=$startmonth THEN CONCAT(YEAR(job_date), '-',YEAR(job_date)+1) ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year
FROM finance_income_table WHERE fre_id = '$uid' $cat GROUP BY financial_year HAVING (financial_year <= '$financialYear' AND financial_year >= '$financialYear_3')";

            $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
            $allincome = $allincomeObj->toArray();
            $dataX = $data1 = $data2 = '';
            foreach ($allincome as $income) {
                if ($filterType == 'year') {
                    $dataX[] = $income['financial_year'];
                } else {
                    $dataX[] = $income['month_nm'];
                }

                $data1[] = $income['bank_yes'];
                $data2[] = $income['bank_no'];
                $data3[] = $income['bank_yes'] + $income['bank_no'];
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
                '3' => $data3,
            );
        } else if ($filterType == 'month') {

            $AllMonth = $this->getMonthrangeByyear($financialYear, $uid);

            $dataX = $data1 = $data2 = $all_data = $data3= '';
            if ($AllMonth != '') {
                foreach ($AllMonth as $key => $Month1) {

                    $monthWise = $this->getAllIncomeBymonth($uid, $Month1['1'], $key, $category, $supplier);

                    $Month1[1] = substr($Month1['1'], 2, 2);
                    if ($monthWise != '') {
                        $dataX[] = @$monthWise['month_nm'] . ',' . $Month1['1'];
                        $data1[] = @$monthWise['bank_yes'];
                        $data2[] = @$monthWise['bank_no'];
                        $data3[] = $monthWise['bank_yes'] + $monthWise['bank_no'];
                    } else {
                        $dataX[] = $Month1;
                        $data1[] = 0;
                        $data2[] = 0;
                        $data3[] = 0;
                    }
                }
            }

            // for projected income by cheng
            $expectedArrIndex = count($dataX);
            $sqlMonth = "SELECT SUM(jp.job_rate) as expected_value, SUBSTRING_INDEX(SUBSTRING_INDEX(jp.job_date, '/', -2), '/', 1) as mon
                        FROM job_post jp
                        LEFT JOIN job_action ja
                        ON  jp.job_id = ja.job_Id
                        where ja.f_id=".$uid." AND jp.job_status = 4 AND ja.action = 4 AND SUBSTRING_INDEX(jp.job_date, '/', -1) >= ".date('Y')."
                        AND SUBSTRING_INDEX(SUBSTRING_INDEX(jp.job_date, '/', -2), '/', 1) >= ".date('m')."
                        GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(jp.job_date, '/', -2), '/', 1)";

            $allMonObj = $adapter->query($sqlMonth, $adapter::QUERY_MODE_EXECUTE);
            $allMonth = $allMonObj->toArray();

            $sqlPriMonth = "SELECT SUM(fpj.priv_job_rate) as expected_value, MONTH(fpj.priv_job_start_date) as mon
                            FROM freelancer_private_job fpj
                            where fpj.f_id=".$uid." AND MONTH(fpj.priv_job_start_date) >= ".date('m')." AND YEAR(fpj.priv_job_start_date) >= ".date('Y')."
                            GROUP BY MONTH(fpj.priv_job_start_date)";

            $allPivMonObj = $adapter->query($sqlPriMonth, $adapter::QUERY_MODE_EXECUTE);
            $allPriMonth = $allPivMonObj->toArray();
//            var_dump($allPriMonth);
//            exit;

            foreach ($allMonth as $key => $item) {
                $monthName = date('F', mktime(0, 0, 0, $item['mon'], 10));
                $dataX[] = substr($monthName, 0, 3);
                foreach ($allPriMonth as $pri_key => $pri_item) {
                    if($pri_item['mon'] == $item['mon']) {
                        $data3[] = $item['expected_value'] + $pri_item['expected_value'];
                    } else {
                        $data3[] = $item['expected_value'];
                    }
                }
            }

//            var_dump($dataX);
//            exit;
            if($allMonth == null) {
                foreach ($allPriMonth as $pri_key => $pri_item) {
                    $monthName = date('F', mktime(0, 0, 0, $pri_item['mon'], 10));
                    $dataX[] = substr($monthName, 0, 3);
                    $data3[] = $pri_item['expected_value'];
                }
            }

            $expectedAll = array_sum($data3); // calcuation income + projected value
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
                '3' => $data3,
                '4' => $expectedArrIndex,
                '5' => $expectedAll,
            );
        }
        return $all_data;
    }

    public function getAllIncomeBymonth($uid, $financialYear, $filtermonth, $category = null, $supplier = null)
    {
        $adapter = $this->getAdapter();
        $cat = '';
        if ($category != null) {
            $cat = "and income_type = '" . $category . "'";
        }
        if ($supplier != null) {
            $cat .= " and supplier = '" . $supplier . "'";
        }

        $sqlAllincome = "SELECT LEFT(MONTHNAME(job_date),3) as month_nm , SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(job_date) =  '$financialYear' and MONTH(job_date) =  '$filtermonth ' $cat GROUP BY YEAR(job_date) , MONTH(job_date)";
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->current();
        if ($allincome) {
            return $allincome;
        } else {
            return false;
        }
    }

    //RP -- not in use 10-05-17 
    public function old_chart_getAllIncome($uid, $financialYear, $filterType)
    {
        $adapter = $this->getAdapter();
        if ($filterType == 'year') {
            $sqlAllincome = "SELECT YEAR(created_at) as year, SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) <=  '$financialYear' GROUP BY YEAR(created_at)";

        } else if ($filterType == 'month') {

            $sqlAllincome = "SELECT YEAR(created_at) as year, MONTH(created_at) as month ,MONTHNAME(created_at) as month_nm , SUM(IF(bank = 1 , job_rate , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where fre_id = '$uid' and YEAR(created_at) =  '$financialYear' GROUP BY YEAR(created_at) , MONTH(created_at)";
        }

        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        $dataX = $data1 = $data2 = '';
        foreach ($allincome as $income) {
            if ($filterType == 'year') {
                $dataX[] = $income['year'];
            } else {
                $dataX[] = $income['month_nm'];
            }

            $data1[] = $income['bank_yes'];
            $data2[] = $income['bank_no'];
        }
        $all_data = array(
            '0' => $dataX,
            '1' => $data1,
            '2' => $data2,
        );
        return $all_data;
    }


    public function chart_getAllExpense($uid, $financialYear, $filterType, $category = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);
        if ($filterType == 'year') {
            $financialYear_3 = $financialYear - 2;
            $financialYear = $this->getMonthFinancialYear($uid, $financialYear);
            $financialYear_3 = $this->getMonthFinancialYear($uid, $financialYear_3);

            $cat = '';
            if ($category != null) {
                $cat = "and expense_type_id = '" . $category . "'";
            }

            $sqlexpense = "SELECT  SUM(IF(bank = 1 , cost , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , cost)) AS bank_no ,
             CASE WHEN MONTH(job_date)>=$startmonth THEN CONCAT(YEAR(job_date), '-',YEAR(job_date)+1) ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year
             FROM finance_expense_table where fre_id = '$uid' $cat GROUP BY financial_year HAVING (financial_year <= '$financialYear' AND financial_year >= '$financialYear_3')";

            $allexpenseObj = $adapter->query($sqlexpense, $adapter::QUERY_MODE_EXECUTE);
            $allexpense = $allexpenseObj->toArray();
            $dataX = $data1 = $data2 = $all_data = '';
            foreach ($allexpense as $expense) {
                if ($filterType == 'year') {
                    $dataX[] = $expense['financial_year'];
                } else {
                    $dataX[] = $expense['month_nm'];
                }

                $data1[] = $expense['bank_yes'];
                $data2[] = $expense['bank_no'];
                $data3[] = $expense['bank_yes'] + $expense['bank_no'];
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
                '3' => $data3,
            );

        } else if ($filterType == 'month') {

            $AllMonth = $this->getMonthrangeByyear($financialYear, $uid);
            $dataX = $data1 = $data2 = $all_data = '';
            if ($AllMonth != '') {
                foreach ($AllMonth as $key => $Month1) {
                    $monthWise = $this->getAllExpenseBymonth($uid, $Month1[1], $key, $category);


                    $Month1[1] = substr($Month1['1'], 2, 2);
                    if ($monthWise != '') {
                        $dataX[] = @$monthWise['month_nm'] . ',' . $Month1[1];
                        $data1[] = @$monthWise['bank_yes'];
                        $data2[] = @$monthWise['bank_no'];
                        $data3[] = $monthWise['bank_yes'] + $monthWise['bank_no'];
                    } else {
                        $dataX[] = $Month1;
                        $data1[] = 0;
                        $data2[] = 0;
                        $data3[] = 0;
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


    public function getAllExpenseBymonth($uid, $financialYear, $filtermonth, $category = null)
    {
        $adapter = $this->getAdapter();

        $cat = '';
        if ($category != null) {
            $cat = "and expense_type_id = '" . $category . "'";
        }

        $sqlAllExpense = "SELECT YEAR(job_date) as year, LEFT(MONTHNAME(job_date),3) as month_nm , SUM(IF(bank = 1 , cost , 0)) AS bank_yes , SUM(IF(bank = 1 , 0 , cost)) AS bank_no FROM finance_expense_table where fre_id = '$uid' and YEAR(job_date) = '$financialYear' and MONTH(job_date) =  '$filtermonth' $cat  GROUP BY YEAR(job_date) , MONTH(job_date)";
        $allExpenseObj = $adapter->query($sqlAllExpense, $adapter::QUERY_MODE_EXECUTE);
        $allExpense = $allExpenseObj->current();
        if ($allExpense) {
            return $allExpense;
        } else {
            return false;
        }
    }


    public function chart_getAlltrans($uid, $financialYear, $filterType, $isbank = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);
        if ($filterType == 'year') {

            if ($financialYear != null) {
                $financialYear = $this->getMonthFinancialYear($uid, $financialYear);
            }
            $financialYear_3 = $financialYear - 2;
            $financialYear_3 = $this->getMonthFinancialYear($uid, $financialYear_3);
            $con_bank = "";
            if ($isbank != null) {
                $con_bank = " AND bank = '1' ";
            }
            /*      $sqlAlltrans ="SELECT YEAR(R.dat) AS year ,SUM(IF(R.trans_type = 1 ,money , 0 )) AS income , SUM(IF(R.trans_type = 2 ,money , 0 )) AS expence FROM
        (SELECT fre_id , trans_type , trans_id , id , job_rate AS money , fin.job_date AS dat FROM finance_income_table fin JOIN finance ON  finance.trans_type_id = fin.id WHERE finance.trans_type = 1 $con_bank  UNION
        SELECT fre_id , trans_type , trans_id , id , cost AS money , ex.job_date AS dat FROM finance_expense_table ex JOIN finance ON  finance.trans_type_id = ex.id WHERE finance.trans_type = 2 $con_bank ) AS R
        WHERE R.fre_id = '$uid' AND  (YEAR(R.dat) <=  '$financialYear' AND YEAR(R.dat) >=  '$financialYear_3') GROUP BY YEAR(R.dat) "; */


            $sqlAlltrans = "SELECT R.financial_year AS year  , YEAR(R.dat)  ,SUM(IF(R.trans_type = 1 ,money , 0 )) AS income , SUM(IF(R.trans_type = 2 ,money , 0 )) AS expence FROM
(SELECT fre_id , trans_type , trans_id , id , job_rate AS money , fin.job_date AS dat ,
 CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year
 FROM finance_income_table fin JOIN finance ON  finance.trans_type_id = fin.id WHERE finance.trans_type = 1 $con_bank  UNION
SELECT fre_id , trans_type , trans_id , id , cost AS money , ex.job_date AS dat ,
 CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year
 FROM finance_expense_table ex JOIN finance ON  finance.trans_type_id = ex.id WHERE finance.trans_type = 2 $con_bank ) AS R
WHERE R.fre_id = '$uid' GROUP BY R.financial_year HAVING (financial_year <=  '$financialYear' AND  financial_year  >=  '$financialYear_3')";


            $alltransObj = $adapter->query($sqlAlltrans, $adapter::QUERY_MODE_EXECUTE);
            $alltarns = $alltransObj->toArray();
            $dataX = $data1 = $data2 = $all_data = '';
            foreach ($alltarns as $trans) {
                if ($filterType == 'year') {
                    $dataX[] = $trans['year'];
                } else {
                    $dataX[] = $trans['month_nm'];
                }

                $data1[] = round($trans['income'], 2);
                $data2[] = round($trans['expence'], 2);
                $data3[] = round($trans['income'] - $trans['expence'], 2);
            }
            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
                '3' => $data3,
            );
        } else if ($filterType == 'month') {

            $AllMonth = $this->getMonthrangeByyear($financialYear, $uid);
            $dataX = $data1 = $data2 = $all_data = '';
            if ($AllMonth != '') {
                foreach ($AllMonth as $key => $Month1) {

                    $monthWise = $this->getAllTransBymonth($uid, $Month1['1'], $key, $isbank);

                    $Month1[1] = substr($Month1['1'], 2, 2);
                    if ($monthWise != '') {
                        $dataX[] = @$monthWise['month_nm'] . ',' . $Month1['1'];
                        $data1[] = @$monthWise['income'];
                        $data2[] = @$monthWise['expence'];
                        $data3[] = $monthWise['income'] - $monthWise['expence'];
                    } else {
                        $dataX[] = $Month1;
                        $data1[] = 0;
                        $data2[] = 0;
                        $data3[] = 0;
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
    public function getAllTransBymonth($uid, $financialYear, $filtermonth, $isbank = null)
    {
        $adapter = $this->getAdapter();
        $con_bank = "";
        if ($isbank != null) {
            $con_bank = " AND bank = '1' ";
        }

        $sqlAllincome = "SELECT LEFT(MONTHNAME(R.dat),3) AS month_nm ,SUM(IF(R.trans_type = 1 ,money , 0 )) AS income , SUM(IF(R.trans_type = 2 ,money , 0 )) AS expence FROM
(SELECT fre_id , trans_type , trans_id , id , job_rate AS money , fin.job_date AS dat FROM finance_income_table fin JOIN finance ON  finance.trans_type_id = fin.id WHERE finance.trans_type = 1 $con_bank  UNION
SELECT fre_id , trans_type , trans_id , id , cost AS money , ex.job_date AS dat FROM finance_expense_table ex JOIN finance ON  finance.trans_type_id = ex.id WHERE finance.trans_type = 2 $con_bank ) AS R
WHERE R.fre_id = '$uid' AND YEAR(R.dat) = '$financialYear' AND MONTH(R.dat) = '$filtermonth'  GROUP BY YEAR(R.dat) , MONTH(R.dat)";

        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->current();
        if ($allincome) {
            return $allincome;
        } else {
            return false;
        }
    }


    public function getIncomeByuser($uid, $year = null)
    {
        $adapter = $this->getAdapter();
        $con = '';


        if ($year != null) {
//$con = " AND YEAR(job_date) = '$year' "; 
            $year = $this->getMonthFinancialYear($uid, $year);
            $startmonth = $this->getUserFinanceyearStartMonth($uid);
            $con = "  HAVING financial_year = '$year'";

            $select = "SELECT SUM(job_rate) as job_rate , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM `finance_income_table` WHERE `fre_id` = '$uid' GROUP BY financial_year $con";
        } else {
            $select = "SELECT SUM(job_rate) as job_rate  FROM `finance_income_table` WHERE `fre_id` = '$uid'";
        }


        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }

    public function getExpenceByuser($uid, $year = null)
    {

        $adapter = $this->getAdapter();
        $con = '';

        if ($year != null) {  //$con = " AND YEAR(job_date) = '$year' ";

            $year = $this->getMonthFinancialYear($uid, $year);
            $startmonth = $this->getUserFinanceyearStartMonth($uid);
            $con = "  HAVING financial_year = '$year'";
            $select = "SELECT SUM(cost) as cost , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year  FROM `finance_expense_table` WHERE `fre_id` = '$uid' GROUP BY financial_year  $con";

        } else {
            $select = "SELECT SUM(cost) as cost  FROM `finance_expense_table` WHERE `fre_id` = '$uid' $con";
        }

        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);

        return $alltarns = $alltransObj->current();
    }

    public function getdataDifference($date)
    {
        $date1 = date_create($date);
        $date2 = date_create(date("Y-m-d"));
        $diff = date_diff($date1, $date2);
        return $diff->format("%a");
    }

    public function getExpencetype($id = null, $all = false)
    {
        $adapter = $this->getAdapter();
        $con = '';
        if ($id != null) {
            $con = " WHERE `id` = '$id'";
        }
        $select = "SELECT id , expense as cat , expense_colour FROM `expense_type` $con";

        $extypeObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        if ($id != null) {
            $extype = $extypeObj->current();
            if ($all == true) {
                return $extype;
            } else {
                return $extype['cat'];
            }
        } else {
            return $extype = $extypeObj->toArray();
        }

    }


    public function getExpenseCategoryName($id = null, $adapter)
    {
        try {

            $select1 = "SELECT * FROM expense_type WHERE id = '$id'";
            $extypeObj = $adapter->query($select1, $adapter::QUERY_MODE_EXECUTE);

            $extype = $extypeObj->current();
            return $extype['expense'];
        } catch (Exception $e) {
        }

    }


    public function getIncometype($id = null)
    {
        $catdata = array(
            '1' => 'Income',
            '2' => 'Bonus',
            '3' => 'Other',
        );
        if ($id != null) {
            return $catdata[$id];
        } else {
            return $catdata;
        }
    }

    public function getMonthrangeByyear_old($year, $uid = null)
    {
        $year = $year;
        $year_curr = date('Y');
        $registerYear = @$uid != null ? $this->getCreatedYear($uid) : null;
        if ($registerYear <= $year && $year_curr >= $year) {
            $registerMonth = @$uid != null ? $this->getCreatedMonth($uid) : null;
            if ($year == $year_curr) {
                $mon_end = date('n');
            } else {
                $mon_end = 12;
            }
            if ($year == $registerYear) {
                $mon_start = $registerMonth;
            } else {
                $mon_start = 1;
            }
            for ($m = $mon_start; $m <= $mon_end; $m++) {
                $month[$m] = date('F', mktime(0, 0, 0, $m, 1, $year));
                // echo $month. '<br>';
            }
            return $month;
        }
    }


    public function getMonthrangeByyear_old_14_07_2017($year, $uid)
    {

        $year_curr = date('Y');
        $y0 = $year - 1;
        $y1 = $year;
        $y2 = $year + 1;

        $tty = $this->getUserFinanceyearStartMonth($uid, true);

        /*  if($tty['month_start'] == '1'){ $startyear = $y1 ; $startend = $y1 ;  } */

        if ((date('n') >= $tty['month_start'])) {
            $startyear = $y1;
            $startend = $y2;
        } else {
            $startyear = $y0;
            $startend = $y1;
        }

        $registerYear = @$uid != null ? $this->getCreatedYear($uid) : null;
        if ($registerYear - 1 <= $year && $year_curr >= $year) {
            $registerMonth = @$uid != null ? $this->getCreatedMonth($uid) : null;

            if ($year == $year_curr) {
                $mon_end = date('n') + 1;
            } else {
                $mon_end = $tty['month_start'];
            }

            if ($year == $registerYear) {

                if ($registerMonth < $tty['month_start']) {
                    $mon_start = $tty['month_start'];
                } else {
                    $mon_start = $registerMonth;

                }

//$startyear = $registerYear ; 


            } else {
                $mon_start = $tty['month_start'];
            }
            $mon_start . "==" . $mon_end;

            if ($mon_end <= $mon_start) {
                $mon_end = $mon_end + 12;
            }

            for ($m = $mon_start; $m < $mon_end; $m++) {
                if (date('Y', mktime(0, 0, 0, $m, 1, $startyear)) >= $registerYear && date('n', mktime(0, 0, 0, $m, 1, $startyear)) >= $registerMonth) {
                    $month[date('n', mktime(0, 0, 0, $m, 1, $startyear))] = array(date('M', mktime(0, 0, 0, $m, 1, $startyear)), date('Y', mktime(0, 0, 0, $m, 1, $startyear)));

                }
            }
            return $month;
        }
    }


    public function getMonthrangeByyear($year, $uid)
    {
        $year_curr = date('Y');
        $y0 = $year - 1;
        $y1 = $year;
        $y2 = $year + 1;
        $tty = $this->getUserFinanceyearStartMonth($uid, true);
        $registerYear = @$uid != null ? $this->getCreatedYear($uid) : null;
        $registerMonth = @$uid != null ? $this->getCreatedMonth($uid) : null;

        if ((date('n') >= $tty['month_start'])) {
            $startyear = $y1;
            $startend = $y2;
        } else {
            $startyear = $y0;
            $startend = $y1;
        }
        if ($year_curr >= $year) {
            if ($year == $year_curr) {
                $mon_end = date('n') + 1;
            } else {
                $mon_end = $tty['month_start'];
            }
            $mon_start = $tty['month_start'];
            if ($mon_end <= $mon_start) {
                $mon_end = $mon_end + 12;
            }
            for ($m = $mon_start; $m < $mon_end; $m++) {
                if (strtotime(date('d-n-Y', mktime(0, 0, 0, $m, 1, $startyear))) >= strtotime(date('d-n-Y', mktime(0, 0, 0, $registerMonth, 1, $registerYear)))) {
                    $month[date('n', mktime(0, 0, 0, $m, 1, $startyear))] = array(date('M', mktime(0, 0, 0, $m, 1, $startyear)), date('Y', mktime(0, 0, 0, $m, 1, $startyear)));
                }
            }
            return $month;
        }
    }


    public function GetchartExpenseBYcondition($uid, $financialYear)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);

        $sqlexpense = "SELECT YEAR(job_date) as year, expense_type_id , SUM(cost) AS cost ,CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_expense_table where fre_id = '$uid' GROUP BY financial_year , expense_type_id HAVING financial_year ='$financialYear'";
        $allexpenseObj = $adapter->query($sqlexpense, $adapter::QUERY_MODE_EXECUTE);
        $allexpense = $allexpenseObj->toArray();
        $dataX = $data1 = $data2 = $cat_data = '';
        foreach ($allexpense as $expense) {
            $typedetail = $this->getExpencetype($expense['expense_type_id'], true);


            $cat_data[] = array(
                /* 'y' =>  $expense['cost'],
                 'label' => $typedetail['cat'],
                 'color' => $typedetail['expense_colour'],
                 'name' => $typedetail['cat']   */

                'value' => round($expense['cost'], '2'),
                'color' => $typedetail['expense_colour'],
                'highlight' => $typedetail['expense_colour'],
                'label' => $typedetail['cat']
            );
        }
        return $cat_data;
    }

    public function GetchartIncomeBYcondition($uid, $financialYear)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);

        $sqlincome = "SELECT YEAR(job_date) as year, income_type , SUM(job_rate) AS job_rate , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_income_table where fre_id = '$uid'  GROUP BY financial_year , income_type HAVING financial_year = '$financialYear'";
        $allincomeObj = $adapter->query($sqlincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        $dataX = $data1 = $data2 = $cat_data = '';
        foreach ($allincome as $income) {
            $typedetail = $this->getIncometype($income['income_type']);
            $colo = '#ffffff';
            if ($income['income_type'] == 1) {
                $colo = '#8064A2';
            }
            if ($income['income_type'] == 2) {
                $colo = '#4F81BD';
            }
            if ($income['income_type'] == 3) {
                $colo = '#d45d6d';
            }

            $cat_data[] = array(
                'value' => round($income['job_rate'], '2'),
                'color' => $colo,
                'highlight' => $colo,
                'label' => $typedetail
            );
        }
        return $cat_data;
    }


    public function Getlast3year($uid, $selectedyear = null)
    {
        $year_curr = @$selectedyear ? $selectedyear : date('Y');
        $registerYear = @$uid != null ? $this->getCreatedYear($uid) : null;
        $year = '';
        $j = 1;
        $m = $this->getUserFinanceyearStartMonth($uid);
        $cm = $this->getCreatedMonth($uid);

        if ($cm < $m || date('n') >= $m) {
            $registerYear = $registerYear;
        } else {
            $registerYear += 1;
        }

        for ($i = $year_curr; $i >= $registerYear; $i--) {
            $year[] = $this->getMonthFinancialYear($uid, $i);;
            if ($j == 3) break;
            ++$j;
        }
        return $year;
    }


    public function getWeek()
    {
        $days = array(
            '1' => 'Sun',
            '2' => 'Mon',
            '3' => 'Tue',
            '4' => 'Wed',
            '5' => 'Thu',
            '6' => 'Fri',
            '7' => 'Sat',
        );
        return $days;
    }


    public function chartGetTransWeekly($uid, $financialYear, $transtype)
    {

        if ($transtype == 'income') {
            $incomes = $this->getAllIncomeByweek($uid, $financialYear);
            if ($incomes == null) {
                $incomes = array();
            }
            $weeks = $this->getWeek();
            foreach ($incomes as $income) {
                $in_data[$income['dayno']] = array('day' => substr($income['day_nm'], 0, 3), 'bank_yes' => $income['bank_yes'], 'bank_no' => $income['bank_no']);
            }
            foreach ($weeks as $key => $val) {
                if (isset($in_data[$key])) {
                    $trnsdata1[] = $in_data[$key];
                } else {
                    $trnsdata1[] = array(
                        'day' => $val,
                        'bank_yes' => 0,
                        'bank_no' => 0,
                    );
                }
            }
            foreach ($trnsdata1 as $in) {
                $dataX[] = $in['day'];
                $data1[] = $in['bank_yes']; // this is use for all data
                $data2[] = $in['bank_no'];
            }


            $all_data = array(
                '0' => $dataX,
                '1' => $data1,
                '2' => $data2,
            );


        } else if ($transtype == 'expense') {
            $expenses = $this->getAllExpesneByweek($uid, $financialYear);
            if ($expenses == null) {
                $expenses = array();
            }
            $weeks = $this->getWeek();
            foreach ($expenses as $expense) {
                $expense_data[$expense['dayno']] = array('day' => substr($expense['day_nm'], 0, 3), 'bank_yes' => $expense['bank_yes'], 'bank_no' => $expense['bank_no']);
            }
            foreach ($weeks as $key => $val) {
                if (isset($expense_data[$key])) {
                    $trnsdata1[] = $expense_data[$key];
                } else {
                    $trnsdata1[] = array(
                        'day' => $val,
                        'bank_yes' => 0,
                        'bank_no' => 0,
                    );
                }
            }
            foreach ($trnsdata1 as $ex) {
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
        return array('data' => $trnsdata1, 'chart' => $all_data);
        // return $all_data;
    }


    public function getAllIncomeByweek($uid, $financialYear, $filterweekday = null, $category = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);
        $con = '';
        if ($category != null) {
            $con = " and income_type = '" . $category . "'";
        }
        if ($filterweekday != null) {
            $con .= " and DAYOFWEEK(job_date) = '" . $filterweekday . "'";
        }
        /* if($financialYear != null){
         //   $financialYear =  $this->getMonthFinancialYear($uid , $financialYear) ;
         }*/


        $sqlAllincome = "SELECT DAYOFWEEK(job_date) as dayno , DAYNAME(job_date) as day_nm , SUM(job_rate) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no ,CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_income_table where  fre_id = '$uid' $con GROUP BY financial_year , DAYOFWEEK(job_date) HAVING financial_year = '$financialYear'";

        //  echo   $sqlAllincome ="SELECT DAYOFWEEK(job_date) as dayno , DAYNAME(job_date) as day_nm , SUM(job_rate) AS bank_yes , SUM(IF(bank = 1 , 0 , job_rate)) AS bank_no FROM finance_income_table where income_type = '1' and fre_id = '$uid' and YEAR(job_date) = '$financialYear' $con GROUP BY YEAR(job_date) , DAYOFWEEK(job_date)";
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        if ($filterweekday != null) {
            $allincome = $allincomeObj->current();
        } else {
            $allincome = $allincomeObj->toArray();
        }

        if ($allincome) {
            return $allincome;
        } else {
            return null;
        }
    }

    public function getAllExpesneByweek($uid, $financialYear, $filterweekday = null, $category = null)
    {
        $adapter = $this->getAdapter();
        $con = '';
        if ($category != null) {
            $con = " and expense_type_id = '" . $category . "'";
        }
        if ($filterweekday != null) {
            $con .= " and DAYOFWEEK(job_date) = '" . $filterweekday . "'";
        }
        $sqlAllexpense = "SELECT DAYOFWEEK(job_date) as dayno , DAYNAME(job_date) as day_nm , SUM(cost) AS bank_yes , SUM(IF(bank = 1 , 0 , cost)) AS bank_no FROM finance_expense_table where fre_id = '$uid' and YEAR(job_date) =  '$financialYear' $con GROUP BY YEAR(job_date) , DAYOFWEEK(job_date)";
        $allexpenseObj = $adapter->query($sqlAllexpense, $adapter::QUERY_MODE_EXECUTE);
        if ($filterweekday != null) {
            $allexpense = $allexpenseObj->current();
        } else {
            $allexpense = $allexpenseObj->toArray();
        }
        if ($allexpense) {
            return $allexpense;
        } else {
            return null;
        }
    }


    public function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public function random_color()
    {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }


    public function GetchartIncomeBYlocation($uid, $financialYear)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);

        $sqlincome = "SELECT YEAR(job_date) as year, location , SUM(job_rate) AS job_rate ,  CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_income_table where fre_id = '$uid' GROUP BY financial_year , location HAVING financial_year = '$financialYear'";
        $allincomeObj = $adapter->query($sqlincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        $dataX = $data1 = $data2 = $cat_data = '';
        foreach ($allincome as $income) {
            $color = $this->random_color();
            $cat_data[] = array(
                'value' => $income['job_rate'],
                'color' => "#" . $color,
                'highlight' => "#" . $color,
                'label' => $income['location']
            );
        }
        return $cat_data;
    }

    public function chartGetJobWeekly($uid, $financialYear)
    {

        $jobs = $this->getAllJobByweek($uid, $financialYear);
        if ($jobs == null) {
            $jobs = array();
        }
        $weeks = $this->getWeek();
        foreach ($jobs as $job) {
            $in_data[$job['dayno']] = array('day' => substr($job['day_nm'], 0, 3), 'jobs' => $job['jobs']);
        }
        foreach ($weeks as $key => $val) {
            if (isset($in_data[$key])) {
                $trnsdata1[] = $in_data[$key];
            } else {
                $trnsdata1[] = array(
                    'day' => $val,
                    'jobs' => 0,
                );
            }
        }
        foreach ($trnsdata1 as $in) {
            $dataX[] = $in['day'];
            $data1[] = $in['jobs'];
        }
        $all_data = array(
            '0' => $dataX,
            '1' => $data1,
        );


        return array('data' => $trnsdata1, 'chart' => $all_data);
    }


    public function getAllJobByweek($uid, $financialYear, $filterweekday = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);

        $con = '';
        if ($filterweekday != null) {
            $con .= " and DAYOFWEEK(job_date) = '" . $filterweekday . "'";
        }
        // $sqlAlljob ="SELECT DAYOFWEEK(job_date) as dayno , DAYNAME(job_date) as day_nm , count(job_id) AS jobs FROM finance_income_table where fre_id = '$uid' and income_type='1' and YEAR(job_date) = '$financialYear' $con GROUP BY YEAR(job_date) , DAYOFWEEK(job_date)";


        $sqlAlljob = "SELECT DAYOFWEEK(job_date) as dayno , DAYNAME(job_date) as day_nm , count(job_id) AS jobs , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_income_table where fre_id = '$uid' and income_type='1' $con  GROUP BY financial_year , DAYOFWEEK(job_date) HAVING financial_year = '$financialYear'";


        $alljobObj = $adapter->query($sqlAlljob, $adapter::QUERY_MODE_EXECUTE);
        if ($filterweekday != null) {
            $alljob = $alljobObj->current();
        } else {
            $alljob = $alljobObj->toArray();
        }
        if ($alljob) {
            return $alljob;
        } else {
            return null;
        }
    }

    public function GetchartIncomeBYsupplier($uid, $financialYear, $cat = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($uid);
        if ($cat == '') {
            $sqlincome = "SELECT YEAR(job_date) as year, supplier , SUM(job_rate) AS job_rate , CASE WHEN MONTH(job_date)>=$startmonth THEN CONCAT(YEAR(job_date), '-',YEAR(job_date)+1) ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_income_table where fre_id = '$uid' GROUP BY financial_year , supplier HAVING financial_year = '$financialYear'";
        } else {
            $sqlincome = "SELECT YEAR(job_date) as year, supplier , SUM(job_rate) AS job_rate , CASE WHEN MONTH(job_date) >= $startmonth THEN CONCAT(YEAR(job_date), '-',YEAR(job_date)+1) ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM finance_income_table where fre_id = '$uid' AND supplier = '$cat' GROUP BY financial_year , supplier HAVING financial_year = '$financialYear'";
        }

        $allincomeObj = $adapter->query($sqlincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        $dataX = $data1 = $data2 = $cat_data = '';
        foreach ($allincome as $income) {
            $color = $this->random_color();
            $cat_data[] = array(
                'value' => $income['job_rate'],
                'color' => "#" . $color,
                'highlight' => "#" . $color,
                'label' => $income['supplier']
            );
        }
        return $cat_data;
    }

    public function loginUserDate($uid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT created_at , firstname , lastname , email , info.mobile , info.address , info.city , info.company FROM user join user_extra_info as info on info.uid = user.id  WHERE user.id = '$uid' AND active = '1'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getIncomefinancedata($id, $uid)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT * FROM finance_income_table WHERE id = '$id' AND fre_id = '$uid'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getWebsitejob($job_id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT * FROM job_post  WHERE job_id = '$job_id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getPrivatejob($job_id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT pv_id , f_id , emp_name , emp_email , priv_job_title, priv_job_rate , priv_job_start_date ,  priv_job_location from freelancer_private_job WHERE pv_id = '$job_id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getjobDataInvoice($id, $uid)
    {
        $dt1 = $this->getIncomefinancedata($id, $uid);
        if ($dt1['job_id'] != '') {
            if ($dt1['job_type'] == '1') {
                $dt2 = $this->getWebsitejob($dt1['job_id']);

                return array(
                    'id' => $dt1['id'],
                    'job_id' => $dt1['job_id'],
                    'job_date' => $dt1['job_date'],
                    'invoice_id' => $dt1['invoice_id'],
                    'job_title' => $dt2['job_title'],
                    'job_rate' => $dt1['job_rate'],
                    'description' => $dt2['job_post_desc'],
                );
                //return   array_merge($dt1,$dt2);
            } else {
                $dt3 = $this->getPrivatejob($dt1['job_id']);
                return array(
                    'id' => $dt1['id'],
                    'job_id' => $dt1['job_id'],
                    'invoice_id' => $dt1['invoice_id'],
                    'job_title' => $dt3['priv_job_title'],
                    'job_rate' => $dt1['job_rate'],
                    'description' => '',
                );
            }
        } else {
            return $dt1;
        }
    }

    public function generateInvoicenum($semail, $adminEmail, $amount, $uid)
    {
        $adapter = $this->getAdapter();

        $sqlGerRegisterDate = "INSERT INTO `send_invoice`(to_email, from_email,amount , user_by) VALUES ('$semail','$adminEmail','$amount','$uid')";
        $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        return $adapter->getDriver()->getLastGeneratedValue();
    }

    public function updateIncomeInvoice($id, $invoiceid)
    {

        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "UPDATE finance_income_table SET invoice_id='$invoiceid' WHERE id='$id'";
        $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
    }


    public function getOnlyFinanceincome($id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT * from finance_income_table WHERE id = '$id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getOnlyFinanceexpence($id)
    {
        $adapter = $this->getAdapter();
        $sqlGerRegisterDate = "SELECT * from finance_expense_table WHERE id = '$id'";
        $gerRegisterDate = $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        $gerRegisterDateObj = $gerRegisterDate->current();
        return $gerRegisterDateObj;
    }

    public function getWebsiteJobdetail($job_id)
    {
        $adapter = $this->getAdapter();
        $sqlGetjob = "SELECT job_id, e_id, job_title,  STR_TO_DATE( `job_date`, '%d/%m/%Y') as job_date, job_post_desc, job_rate, job_type, job_address, job_region, store_id , esl.emp_store_name as store_nm , user.firstname as first_nm , user.lastname as last_nm FROM job_post as jp left join employer_store_list as esl on jp.store_id = esl.emp_st_id left join user as user on user.id = jp.e_id   WHERE job_id = '$job_id'";
        $getjobs = $adapter->query($sqlGetjob, $adapter::QUERY_MODE_EXECUTE);
        $getjob = $getjobs->current();
        return $getjob;

    }


    public function getExpenceLunchtravel($uid, $year = null)
    {
        $adapter = $this->getAdapter();
        $con = '';
        if ($year != null) {
// $con = " AND YEAR(job_date) = '$year' "; 
            $year = $this->getMonthFinancialYear($uid, $year);
            $startmonth = $this->getUserFinanceyearStartMonth($uid);

            $con = "  HAVING financial_year = '$year'";

            $select = "SELECT SUM(cost) as cost , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year FROM `finance_expense_table` WHERE `fre_id` = '$uid' and expense_type_id IN ('1','3') GROUP BY financial_year  $con";

        } else {
            $select = "SELECT SUM(cost) as cost  FROM `finance_expense_table` WHERE `fre_id` = '$uid' and expense_type_id IN ('1','3') $con";
        }
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }


    public function getfinanceIncomeUser($year = null, $uid = null, $adapter = null)
    {
        if ($adapter == null) {
            $adapter = $this->getAdapter();
        }
        $con = '';
        if ($year != null && $uid != '') {
            //$year = $this->getMonthFinancialYear($uid , $year);
            $startmonth = $this->getUserFinanceyearStartMonth($uid, false, $adapter);

            if ($startmonth == 1) {
                $con = " where fre_id = '$uid' AND YEAR(job_date) = '$year'";
            } else {
                if (date('m') >= $startmonth) {
                    $con = " where fre_id = '$uid' AND ( YEAR(job_date) = '$year' OR YEAR(job_date) = '" . ($year + 1) . "' ) ";
                } else {
                    $con = " where fre_id = '$uid' AND ( YEAR(job_date) = '$year' OR YEAR(job_date) = '" . ($year - 1) . "' ) ";
                }
            }
        } else {
            $con = " where YEAR(job_date) = '$year' ";
        }
        $select = "SELECT fre_id as fre_id FROM finance_income_table $con GROUP BY fre_id ";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->toArray();
    }

    public function InsertFinanceprofitloss($data)
    {
        $adapter = $this->getAdapter();
        $prifitlossdata = $this->getFinanceprofitloss($data['fre_id'], $data['year']);

        if (empty($prifitlossdata) && $prifitlossdata == '') {
            $sqlGerRegisterDate = "INSERT INTO `finance_profit_loss`(`fre_id`, `revenue`, cos , othercost , `income_tax`, `interest_income`, `financial_year` , tax_calculation) VALUES ('" . $data['fre_id'] . "','" . $data['revenue'] . "','" . $data['cos'] . "','" . $data['othercost'] . "','" . $data['totaltax'] . "','" . $data['interestincome'] . "','" . $data['year'] . "','" . $data['taxcalculationhelp'] . "')";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
            return $adapter->getDriver()->getLastGeneratedValue();
        } else {
            $sqlGerRegisterDate = "UPDATE finance_profit_loss SET  interest_income = '" . $data['interestincome'] . "'  WHERE financial_year = '" . $data['year'] . "' and fre_id = '" . $data['fre_id'] . "'";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        }
    }


    public function getFinanceprofitloss($uid, $year)
    {


        $adapter = $this->getAdapter();
        $select = "SELECT *  FROM `finance_profit_loss` WHERE `fre_id` = '$uid' and financial_year = '$year'";

        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }


    public function InsertFinancebalance($data)
    {
        $inputdata = serialize(array('put1' => $data['put1'], 'put2' => $data['put2'], 'put3' => $data['put3'], 'put4' => $data['put4'], 'put6' => $data['put6'], 'put7' => $data['put7']));
        $adapter = $this->getAdapter();
        $prifitlossdata = $this->getFinancebalancesheet($data['fre_id'], $data['year']);
        if (empty($prifitlossdata) && $prifitlossdata == '') {
            $sqlGerRegisterDate = "INSERT INTO `finance_balancesheet`(`fre_id`, `income_tax`, `input_data`, `financial_year`) VALUES ('" . $data['fre_id'] . "','" . $data['totaltax'] . "','" . $inputdata . "','" . $data['year'] . "')";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
            return $adapter->getDriver()->getLastGeneratedValue();
        } else {
            $sqlGerRegisterDate = "UPDATE finance_balancesheet SET  input_data = '" . $inputdata . "'  WHERE financial_year = '" . $data['year'] . "' and fre_id = '" . $data['fre_id'] . "'";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function getFinancebalancesheet($uid, $year)
    {
        $adapter = $this->getAdapter();
        $select = "SELECT *  FROM `finance_balancesheet` WHERE `fre_id` = '$uid' and financial_year = '$year'";
        $alltransObj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $alltarns = $alltransObj->current();
    }


    public function EmpFinanceGetAllIncome($empid, $id = null, $financialYear, $ispaid = null)
    {
        $adapter = $this->getAdapter();
        $startmonth = $this->getUserFinanceyearStartMonth($empid);
        $financeyear = $havefinanceyear = '';
        if ($financialYear != null) {

            $financialYear = $this->getMonthFinancialYear($empid, $financialYear);

            $havefinanceyear = "HAVING financial_year =  '$financialYear'";
            //  $financeyear = "AND YEAR(job_date) =  '$financialYear'";
        }
        if ($ispaid != null) {
            $financeyear .= " AND bank = '1' ";
        }
        if ($id != null) {
            $financeyear .= " AND id = '$id' ";
        }
        //     $sqlAllincome ="SELECT * from finance_employer WHERE emp_id = '$empid' $financeyear ";

        $sqlAllincome = "SELECT * , CASE WHEN MONTH(job_date)>=$startmonth THEN
          CONCAT(YEAR(job_date), '-',YEAR(job_date)+1)
   ELSE CONCAT(YEAR(job_date)-1,'-', YEAR(job_date)) END AS financial_year from finance_employer WHERE emp_id = '$empid' $financeyear $havefinanceyear";
        $allincomeObj = $adapter->query($sqlAllincome, $adapter::QUERY_MODE_EXECUTE);
        $allincome = $allincomeObj->toArray();
        return $allincome;
    }


    public function getEmpFinanceCost($uid, $financialYear)
    {
        $AllMonth = $this->getMonthrangeByyear($financialYear, $uid);
        $dataX = $data1 = $data2 = $all_data = '';
        if ($AllMonth != '') {
            foreach ($AllMonth as $key => $Month1) {
                $monthWise = $this->getEmpFinanceBymonth($uid, $Month1['1'], $key, 'cost');
                $Month1[1] = substr($Month1['1'], 2, 2);
                if ($monthWise != '') {
                    $dataX[] = @$monthWise['month_nm'] . ',' . $Month1['1'];
                    $data1[] = @$monthWise['job_rate'];
                } else {
                    $dataX[] = $Month1;
                    $data1[] = 0;
                }
            }
        }
        $all_data = array(
            '0' => $dataX,
            '1' => $data1,
        );
        return $all_data;
    }

    public function getEmpFinanceJob($uid, $financialYear)
    {
        $AllMonth = $this->getMonthrangeByyear($financialYear, $uid);
        $dataX = $data1 = $data2 = $all_data = '';
        if ($AllMonth != '') {
            foreach ($AllMonth as $key => $Month1) {
                $monthWise = $this->getEmpFinanceBymonth($uid, $Month1['1'], $key, 'job');
                $Month1[1] = substr($Month1['1'], 2, 2);
                if ($monthWise != '') {
                    $dataX[] = @$monthWise['month_nm'] . ',' . $Month1['1'];
                    $data1[] = @$monthWise['job_id'];
                } else {
                    $dataX[] = $Month1;
                    $data1[] = 0;
                }
            }
        }
        $all_data = array(
            '0' => $dataX,
            '1' => $data1,
        );
        return $all_data;
    }


    public function getEmpFinanceBymonth($uid, $financialYear, $filtermonth, $filtertype)
    {
        $adapter = $this->getAdapter();
        if ($filtertype == 'job') {
            $sqlAllExpense = "SELECT YEAR(job_date) as year, LEFT(MONTHNAME(job_date),3) as month_nm , count(job_id) as job_id FROM finance_employer where emp_id = '$uid' and YEAR(job_date) = '$financialYear' and MONTH(job_date) =  '$filtermonth' GROUP BY YEAR(created_at) , MONTH(created_at)";
        }
        if ($filtertype == 'cost') {
            $sqlAllExpense = "SELECT YEAR(job_date) as year, LEFT(MONTHNAME(job_date),3) as month_nm , SUM(job_rate)AS job_rate  FROM finance_employer where emp_id = '$uid' and YEAR(job_date) = '$financialYear' and MONTH(job_date) =  '$filtermonth' GROUP BY YEAR(created_at) , MONTH(created_at)";
        }
        $allExpenseObj = $adapter->query($sqlAllExpense, $adapter::QUERY_MODE_EXECUTE);
        $allExpense = $allExpenseObj->current();
        if ($allExpense) {
            return $allExpense;
        } else {
            return false;
        }
    }

    public function getJobtype($id = null)
    {
        $jobtype = array(
            '1' => 'Website',
            '2' => 'Private',
            '3' => 'Other',
        );

        if ($id != null) {
            return $jobtype[$id];
        } else {
            return $jobtype;
        }
    }

    public function InsertIndustrynews($data)
    {
        $adapter = $this->getAdapter();
        if (!isset($data['id']) && $data['id'] == '') {
            $sqlGerRegisterDate = "INSERT INTO `industry_news`(`title`, `slug`, `description`, `image_path`,  `user_type`, `category_id`, `status`, `metatitle`, `metadescription`,`metakeywords`) VALUES ('" . $data['title'] . "','" . $data['slug'] . "','" . $data['description'] . "','" . $data['image_path'] . "','" . $data['user_type'] . "','" . $data['category_id'] . "','" . $data['status'] . "','" . $data['metatitle'] . "','" . $data['metadescription'] . "','" . $data['metakeywords'] . "')";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
            return $adapter->getDriver()->getLastGeneratedValue();
        } else {
            if (isset($data['image_path'])) {
                $u = ", image_path = '" . $data['image_path'] . "'";
            }
            $sqlGerRegisterDate = "UPDATE industry_news SET  title = '" . $data['title'] . "' , slug = '" . $data['slug'] . "' , description = '" . $data['description'] . "' , user_type = '" . $data['user_type'] . "' , category_id = '" . $data['category_id'] . "' , status = '" . $data['status'] . "', metatitle = '" . $data['metatitle'] . "', metadescription = '" . $data['metadescription'] . "', metakeywords = '" . $data['metakeywords'] . "' " . $u . "   WHERE id = '" . $data['id'] . "'";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function getIndustrynews($id = null, $cat = null, $pro = null)
    {
        if ($id != null) {
            $where = " where id=" . $id;
        } elseif ($cat != null && $pro != null) {
            $where = " where FIND_IN_SET(" . $cat . ",user_type) and  FIND_IN_SET(" . $pro . ",category_id)";
        } else {
            $where = "";
        }
        $adapter = $this->getAdapter();
        $select = "SELECT * FROM industry_news " . $where;
        $dateobj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $date = $dateobj->toArray();
    }

    function getoneplusyear($year)
    {
        $year1 = $year + 1;
        return $year . '-' . $year1;
    }

    public function getMonthNamer()
    {
        for ($m = 1; $m <= 12; $m++) {
            $month[date('n', mktime(0, 0, 0, $m, 1))] = date('F', mktime(0, 0, 0, $m, 1));
        }
        return $month;
    }

    public function getMonthFinancialYear($uid, $year)
    {
        if ($uid == null) {
            $month_start = '4';
        } else {
            $tty = $this->getUserFinanceyearStartMonth($uid, true);
            if (!empty($tty)) {
                $month_start = $tty['month_start'];
            } else {
                $month_start = '4';
            }
        }
        $y0 = $year - 1;
        $y1 = $year;
        $y2 = $year + 1;

        if ($month_start == 1) {
            // return $y1;
            return $y1 . '-' . $y2;
        } elseif ((date('n') >= $month_start)) {
            return $y1 . '-' . $y2;
        } else {
            return $y0 . '-' . $y1;
        }
    }

    public function getIndustrynewsfordisplay($cat = null, $pro = null)
    {
        if ($cat != null && $pro != null) {
            $where = " and FIND_IN_SET(" . $cat . ",user_type) and  FIND_IN_SET(" . $pro . ",category_id)";
        } else {
            $where = "";
        }
        $adapter = $this->getAdapter();
        $select = "SELECT * FROM industry_news where status = '1' " . $where . " Order by create_at desc LIMIT 0,3";
        $dateobj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $date = $dateobj->toArray();
    }

    public function getEmpFinanceBymonthoverall($uid)
    {
        $adapter = $this->getAdapter();
        $sqlAllExpense = "SELECT count(id) as count FROM finance_employer where emp_id = '$uid' ";
        $allExpenseObj = $adapter->query($sqlAllExpense, $adapter::QUERY_MODE_EXECUTE);
        $allExpense = $allExpenseObj->current();
        if ($allExpense) {
            return $allExpense['count'];
        } else {
            return 0;
        }
    }

    public function getpostblog($id = null, $is_status = 1)
    {
        if ($is_status) {
            if ($id != null) {
                $where = " where status = '1' AND id=" . $id;
            } else {
                $where = " where status = '1'";
            }
        }
        $adapter = $this->getAdapter();
        $select = "SELECT * FROM blogs" . $where . " ORDER BY create_at DESC";
        $dateobj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $date = $dateobj->toArray();
    }

    public function Insertblogpost($data)
    {
        $adapter = $this->getAdapter();
        if (!isset($data['id']) && $data['id'] == '') {
            $sqlGerRegisterDate = "INSERT INTO `blogs`(`title`, `slug`, `description`, `image_path`, `pdf_path`, `user_type`, `category_id`, `status`, `metatitle`, `metadescription`,`metakeywords`) VALUES ('" . $data['title'] . "','" . $data['slug'] . "','" . $data['description'] . "','" . $data['image_path'] . "', '" . $data['pdf_path'] . "', '" . $data['user_type'] . "','" . $data['category_id'] . "','" . $data['status'] . "','" . $data['metatitle'] . "','" . $data['metadescription'] . "','" . $data['metakeywords'] . "')";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
            return $adapter->getDriver()->getLastGeneratedValue();
        } else {
            if (isset($data['image_path'])) {
                $u = ", image_path = '" . $data['image_path'] . "'";
            }
            if (isset($data['pdf_path'])) {
                $pu = ", pdf_path = '" . $data['pdf_path'] . "'";
            }
            $sqlGerRegisterDate = "UPDATE blogs SET  title = '" . $data['title'] . "' , slug = '" . $data['slug'] . "' , description = '" . $data['description'] . "' , user_type = '" . $data['user_type'] . "' , category_id = '" . $data['category_id'] . "' , status = '" . $data['status'] . "' , metatitle = '" . $data['metatitle'] . "', metadescription = '" . $data['metadescription'] . "', metakeywords = '" . $data['metakeywords'] . "' " . $u . $pu . "   WHERE id = '" . $data['id'] . "'";
            $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function getblogcategory($id = null)
    {
        if ($id != null) {
            $where = " where id=" . $id;
        } else {
            $where = "";
        }
        $adapter = $this->getAdapter();
        $select = "SELECT * FROM blogs_category" . $where;
        $dateobj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $date = $dateobj->toArray();
    }

    public function getpostblogfordisplay($cat = null)
    {
        if ($cat != null) {
            $where = " and FIND_IN_SET(" . $cat . ",user_type)";
        } else {
            $where = "";
        }
        $adapter = $this->getAdapter();
        $select = "SELECT * FROM industry_news where status = '1' " . $where . " Order by create_at desc";
        $dateobj = $adapter->query($select, $adapter::QUERY_MODE_EXECUTE);
        return $date = $dateobj->toArray();
    }

    public function checkBlogSlugExist($slug = null, $post_type = null)
    {
        $is_exist = false;
        if ($slug && $post_type) {
            if ($post_type == 'blog') {
                $sql = "SELECT * FROM blogs WHERE slug = '$slug'";
            } else {
                $sql = "SELECT * FROM industry_news WHERE slug = '$slug'";
            }
            $adapter = $this->getAdapter();
            $slugObj = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
            return $slugArray = $slugObj->toArray();
        }
    }


}
