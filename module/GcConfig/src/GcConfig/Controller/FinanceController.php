<?php


namespace GcConfig\Controller;

use GcConfig\Form\UserLogin;
use Gc\User\Finance\Tax\Ni as FinanceNiTax;
use GcConfig\Form\Finance as FinanceForm;
use GcConfig\Form\UserForgotPassword as UserForgotForm;
use Gc\Mvc\Controller\Action;
use Gc\User;
use Gc\User\Role;
use Gc\User\Professional;
use Zend\View\Model\ViewModel;
use Zend\Validator\Identical;
use GcFrontend\Controller\JobmailController as MailController;
use GcFrontend\Helper\FinanceHelper as FinanceHelper;
use Gc\User\Finance\AddSupplier\Collection as SupplierCollection;

use GcConfig\Controller\FinanceTaxController as FinanceTaxController;

/**
 * User controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class FinanceController extends Action
{
    /**
     * Contains information about acl resource
     *
     * @var array
     */
    protected $aclResource = 'Settings';

    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'finance');

    /**
     * List all roles
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $financeHelper = new FinanceHelper();
        if (isset($_GET['y']) ) {
            $y = $_GET['y'];
        }else{ 
            $y = date("Y"); 
        }

        
        $fre_id = array();
        /*foreach($inUser as $user){
            $fre_id[] = $user['fre_id'];
        }*/
        $userCollection = new User\Collection();
        $userFre  = array();
        if (isset($_GET['c']) && $_GET['c'] != '') {
            $catId = $_GET['c'];
        }else{
            $catId = 1;
        }
        $adapter = $financeHelper->getAdapter();
        $professions = $this->getProfession();
        $userFre1 = $userCollection->getFinanceUsers();

        foreach ($userFre1 as $user) {
            $inUser = $financeHelper->getfinanceIncomeUser($y,$user->getId(),$adapter);
            if(!empty($inUser)){
                if($user->getUserAclProfessionId() == $catId) {
                    $userFre[] = $user;
                }
            }
        }
        return array( 'userFre'=>$userFre, 'professions' => $professions);
    }
    /**
     * Create user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function balancesheetAction()
    {
        $financeHelper = new FinanceHelper();
        $userCollection = new User\Collection();
        $userId   = $this->getRouteMatch()->getParam('id');
        $year   = $this->getRouteMatch()->getParam('year');
        $fYear =  $financeHelper->getMonthFinancialYear($userId , $year) ;
        $financeusertypedata =  $financeHelper->getUserFinanceyearStartMonth($userId,true);
        $financeusertype = $financeusertypedata['user_type'] ;

        if ($year == date("Y") || $year == date("Y")-1) { $year = $year;  }else{ $year = date("Y"); }
        $post = $this->getRequest()->getPost();  $post1 = 0;
        if ($this->getRequest()->isPost()) { $financeHelper->InsertFinancebalance($post);
            $post1 = 1;
        }
        $balancesheet = $financeHelper->getFinancebalancesheet($userId,$fYear);
        
        $profitlossdata = $financeHelper->getFinanceprofitloss($userId,$fYear);
        
        if(!empty($balancesheet) && $balancesheet != ''){
            $udata = array();
            $userdata =   $userCollection->getFinanceUsers($userId);
            foreach ($userdata as $user) {
                $udata[]      =  $user;
            }
            return  array(
                'userData' => $user ,
                'revenue1'=> '0' , // not use if data already exist
                'totaltax' => $balancesheet['income_tax'],
                'year' => $balancesheet['financial_year'],
                'inputdata' => unserialize($balancesheet['input_data']),
                'post1' => $post1
            );

        }else{
            $rec = $this->profitlossdata($userId,$year);
            $user     = $rec['udata'][0];
            $revenue   = $rec['revenue'][0];
            $taxcalculation = @$profitlossdata['income_tax'] ? $profitlossdata['income_tax'] : $this->taxclaculation($revenue , $financeusertype, $year ,$userId );
            return  array( 'userData' => $user ,
                'revenue1'=> $revenue ,
                'totaltax' => $taxcalculation,
                'year' => $fYear,
                'post1' => $post1
            );
        }
    }


    public function profitlossAction()
    {
       
        $financeHelper = new FinanceHelper();
        $userCollection = new User\Collection();
        $userId    = $this->getRouteMatch()->getParam('id');
        $year   = $this->getRouteMatch()->getParam('year');

        $fYear =  $financeHelper->getMonthFinancialYear($userId , $year) ;    
            
        $financeusertypedata =  $financeHelper->getUserFinanceyearStartMonth($userId,true);        
        $financeusertype = $financeusertypedata['user_type'] ;
        $finusertype = '' ;
        if($financeusertype == 'soletrader'){
         $finusertype = ' (Sole Trader) ' ;
        }else if($financeusertype == 'limitedcompany'){
        $finusertype = ' (Limited Company) ' ;
        }


        $post = $this->getRequest()->getPost();  $post1 = 0;
        if ($this->getRequest()->isPost()) { $financeHelper->InsertFinanceprofitloss($post);
           $post1 = 1;
        }
        if ($year == date("Y") || $year == date("Y")-1) { $year = $year;  }else{ $year = date("Y"); }
        $profitlossdata = $financeHelper->getFinanceprofitloss($userId,$fYear);

        if(!empty($profitlossdata) && $profitlossdata != ''){
            $udata = array();
            $userdata =   $userCollection->getFinanceUsers($userId);
            foreach ($userdata as $user) {
                $udata[]      =  $user;
            }
            $pagedata =  array(
                'userData'  => $udata[0] ,
                'revenue1'  => $profitlossdata['revenue'] ,
                'cos1'      =>$profitlossdata['cos'] ,
                'othercost1'=> $profitlossdata['othercost'] ,
                'totaltax'  => $profitlossdata['income_tax'],
                'interest_income'  => $profitlossdata['interest_income'],
                'year'      => $profitlossdata['financial_year'],
                'post1'      => $post1,
                'taxclaculation_help'  =>   $profitlossdata['tax_calculation'], // $this->taxclaculation_help($profitlossdata['revenue'] , $financeusertype)                
                'taxclaculation_help_of' => $finusertype ,
            );
            return $pagedata ;
        }else{
            $rec = $this->profitlossdata($userId,$year);
            $user       = $rec['udata'][0];
            $revenue    = $rec['revenue'][0];
            $cos        = $rec['cos'][0];
            $othercost  = $rec['othercost'][0];

            $taxcalculation = $this->taxclaculation($revenue , $financeusertype , $year, $userId);
            return  array(
                'userData' => $user ,
                'revenue1'=> $revenue ,
                'cos1' =>$cos ,
                'othercost1' => $othercost ,
                'totaltax' => $taxcalculation,
                'year' => $fYear,
                'post1'      => 0,
                'taxclaculation_help'     => $this->taxclaculation_help($revenue , $financeusertype, $year ,$userId),
                'taxclaculation_help_of' => $finusertype ,
            );
        }
    }


    public function alltransactionsAction()
    {
        $financeHelper = new FinanceHelper();
        $userCollection = new User\Collection();
        $userId    = $this->getRouteMatch()->getParam('id');
        $year   = $this->getRouteMatch()->getParam('year');
        $fYear =  $financeHelper->getMonthFinancialYear($userId , $year) ;
        $t = 'in';
        if (isset($_GET['t']) && $_GET['t'] == 'in') {
            $t = 'in';
            $trans = $financeHelper->getAllIncome($userId,$year,null);
        }elseif(isset($_GET['t']) && $_GET['t'] == 'ex'){
            $t = 'ex';
            $trans = $financeHelper->getAllExpense($userId,$year,null);
        }else{
            $trans = $financeHelper->getAllIncome($userId,$year,null);
        }

        $userdata =   $userCollection->getFinanceUsers($userId);
        foreach ($userdata as $user) {
            $udata[]      =  $user;
        }
        return  array(
            'year' => $fYear ,
            'userData' => $user ,
            'alltrans'=> $trans ,
        );
    }

   public function supplierlistAction()
    {
        $userCollection = new User\Collection();
        $suppliercollection = new SupplierCollection();
        $userId    = $this->getRouteMatch()->getParam('id');
        $year   = $this->getRouteMatch()->getParam('year');

        $dataSupplier = $suppliercollection->getSupplier();

       $userdata =   $userCollection->getFinanceUsers($userId);
         foreach ($userdata as $user) {
            $udata[]      =  $user;
        }
        return  array(
            'year' => $year ,
            'userData' => $user ,
            'supplier'=> $dataSupplier ,
        );
    }

    public function profitlossdata($userid = null,$year = null)
    {
        $userCollection = new User\Collection();
        $financeHelper = new FinanceHelper();
        $udata = $revenue = $cos = $othercost  = array();

        $userdata = $userCollection->getFinanceUsers($userid);

        foreach ($userdata as $user) {
            $overallincome = $financeHelper->getIncomeByuser($userid,$year);
            $overallexpence = $financeHelper->getExpenceByuser($userid,$year);
            $expenceLunchtraval = $financeHelper->getExpenceLunchtravel($userid,$year);
            $udata[]      = $user;
            $revenue[]    = $overallincome['job_rate'];
            $cos[]        = @$expenceLunchtraval['cost'] ? $expenceLunchtraval['cost'] : '0';
            $othercost[]  =  $overallexpence['cost'] - $expenceLunchtraval['cost'];
        }
        return array('udata'=> $udata, 'revenue' => $revenue , 'cos' => $cos, 'othercost' => $othercost);
    }

    public function taxclaculation_old($amount = 0)
    {
        if('11000' >= $amount){ // 0%
            return $totaltax = 0 ;
        }elseif('11000' < $amount && $amount <= '44500'){ // 20%
            $val_44500 = $amount - 11000 ; // 20%
            $val_44500_per =  $val_44500 * 20 /100 ;
            return  $totaltax = $val_44500_per ;
        }elseif('44500' < $amount && $amount <= '150000'){  // 40%
            $val_44500 = 44500 - 11000 ;    // 20%
            $val_150000 = $amount - 44500 ; // 40%
            $val_44500_per =  $val_44500 * 20 /100 ;
            $val_150000_per =  $val_150000 * 40 /100 ;
            return  $totaltax = $val_44500_per+$val_150000_per ;
        }elseif('150000' < $amount){ // 45%
            $val_44500 = 44500 - 11000 ; // 20%
            $val_150000 = 150000 - 44500 ; // 40%
            $val_150000_above = $amount - 150000;
            $val_44500_per =  $val_44500 * 20 /100 ;
            $val_150000_per =  $val_150000 * 40 /100 ;
            $val_150000_above_per = $val_150000_above * 45 /100 ;
            return  $totaltax = $val_44500_per+$val_150000_per+$val_150000_above_per ;
        }
    }
    
    
    public function taxclaculation($amount = 0 , $finusertype = null , $finyear=null , $userid = null){ 
   


        $financeTaxController = new FinanceTaxController();
        $financeHelper = new FinanceHelper();
        if($finyear == null){
            $finyear = date('Y');
        }
        $financeyear = $financeHelper->getMonthFinancialYear($userid , $finyear) ;     
        $taxdata = $financeTaxController->taxListByinyear($financeyear);

       
        /*Net Profit*/
        if($userid == null){
            $userid = $_SESSION['user_id'];
        }
        $overallincome_curryear = $financeHelper->getIncomeByuser($userid , $finyear);
        $overallexpence_curryear = $financeHelper->getExpenceByuser($userid , $finyear);

       if(!empty($overallincome_curryear) && !empty($overallexpence_curryear)){
              $amount = $overallincome_curryear['job_rate'] - $overallexpence_curryear['cost'];
       }elseif(!empty($overallincome_curryear) && empty($overallexpence_curryear)){
              $amount = $overallincome_curryear['job_rate'];
       }elseif(empty($overallincome_curryear)){
              $amount = 0;
       }
      



        if($finusertype!=null && $finusertype=='limitedcompany'){
            
            if($taxdata){
                $taxper = $taxdata->getCompanyLimitedTax();
            }else{
                $taxper = '20';
            }  
            $totaltax = $amount*$taxper / 100 ;
            return $totaltax ;
        
        }else{

            /* Normal Tax */        
            if($taxdata){
                $basicrate_amt      = $taxdata->getPersonalAllowanceRate() ;
                $higherrate_amt     = $taxdata->getBasicRate() ;
                $additionalrate_amt = $taxdata->getHigherRate() ;  

                $basicrate_per      = $taxdata->getBasicRateTax() ;
                $higherrate_per     = $taxdata->getHigherRateTax() ;
                $additionalrate_per = $taxdata->getAdditionalRateTax() ;
            }else{    
                $basicrate_amt      = '11000' ;
                $higherrate_amt     = '44500' ;
                $additionalrate_amt = '150000' ;
                   
                $basicrate_per      = '20' ;
                $higherrate_per     = '40' ;
                $additionalrate_per = '45' ;      
            }       

            
            if($basicrate_amt >= $amount){ // 0% Personal Allowance
                $totaltax = 0 ;
            }elseif($basicrate_amt < $amount && $amount <= $higherrate_amt){ // 20% Basic rate
                $val_44500 = $amount - $basicrate_amt ; // 20%
                $val_44500_per =  $val_44500 * $basicrate_per /100 ;
                $totaltax = $val_44500_per ;
            }elseif($higherrate_amt < $amount && $amount <= $additionalrate_amt){  // 40% Higher rate
                $val_44500 = $higherrate_amt - $basicrate_amt ;    // 20%
                $val_150000 = $amount - $higherrate_amt ; // 40%
                $val_44500_per =  $val_44500 * $basicrate_per /100 ;
                $val_150000_per =  $val_150000 * $higherrate_per /100 ;
                $totaltax = $val_44500_per+$val_150000_per ;
            }elseif($additionalrate_amt < $amount){ // 45% Additional rate
                $val_44500 = $higherrate_amt - $basicrate_amt ; // 20%
                $val_150000 = $additionalrate_amt - $higherrate_amt ; // 40%
                $val_150000_above = $amount - $additionalrate_amt;
                $val_44500_per =  $val_44500 * $basicrate_per /100 ;
                $val_150000_per =  $val_150000 * $higherrate_per /100 ;
                $val_150000_above_per = $val_150000_above * $additionalrate_per /100 ;
                $totaltax = $val_44500_per+$val_150000_per+$val_150000_above_per ;
            }

            /* Ni Tax */
            $niTaxData = $this->getNiTaxSetting($financeyear);
            if ($niTaxData) {                
                $c4_min_amount_1    = $niTaxData->getC4MinAmmount_1(); // Nil
                $c4_min_amount_2    = $niTaxData->getC4MinAmmount_2(); // 9%
                $c4_above_amount_3  = $niTaxData->getC4MinAmmount_3(); // 2%
                $c2_amount          = $niTaxData->getC2MinAmount(); // 2.85 % per week of year                                  
                
                $c4_min_amount_2_tax    = $niTaxData->getC4MinAmmountTax_2();
                $c4_above_amount_3_tax  = $niTaxData->getC4MinAmmountTax_3();
                $c2_amount_tax          = $niTaxData->getC2Tax();

            }else{
                $c4_min_amount_1    = '8000'; // Nil
                $c4_min_amount_2    = '45000'; // 9%
                $c4_above_amount_3  = '45001'; // 2%
                $c2_amount          = '6025'; // 2.85 % per week of year
                   
                $c4_min_amount_2_tax    = '2' ;
                $c4_above_amount_3_tax  = '9' ;
                $c2_amount_tax          = '148.2';
            }

           
            if($c4_min_amount_1 >= $amount){ // 0% Personal Allowance
                $nitotaltax = 0 ;
            }elseif($c4_min_amount_1 < $amount && $amount <= $c4_min_amount_2){ // 9% 
                $val_45000      = $amount - $c4_min_amount_1 ; // 9%
                $val_45000_per  = $val_45000 * $c4_min_amount_2_tax /100 ;
                $nitotaltax     = $val_45000_per ;
            }elseif($c4_min_amount_2 < $amount ){  // 2% 
                $val_45000          = $c4_min_amount_2 - $c4_min_amount_1 ;    // 9%
                $val_45k_plus       = $amount - $c4_min_amount_2 ; // 2%
                $val_45000_per      = $val_45000 * $c4_min_amount_2_tax /100 ;
                $val_45k_plus_per   = $val_45k_plus * $c4_above_amount_3_tax /100 ;
                $nitotaltax         = $val_45000_per + $val_45k_plus_per ;
            }           
            
            if($c2_amount < $amount){ // add yearlly charge
                $nitotaltax = $nitotaltax + $c2_amount_tax;
            }
             
            $fullTotalTax = $totaltax + $nitotaltax;
            return $fullTotalTax;
        }

    }
    
    
    
    public function taxclaculation_help_old($amount = 0)
    {
        $financeHelper = new FinanceHelper();
        $currency = 'Â£';

        if('11000' >= $amount){ // 0%
            $totaltax = 0 ;
            $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td> '. $currency.$financeHelper->setPriceFormate(11000*0/100) .'    ( 0% )</td></tr>';
            $rr .= '<tr><td><b>Total Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
            return $rr ;
        }elseif('11000' < $amount && $amount <= '44500'){ // 20%
            $val_44500 = $amount - 11000 ; // 20%
            $val_44500_per =  $val_44500 * 20 /100 ;
            $totaltax = $val_44500_per ;
            $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate('11000').'</td><td> '. $currency.$financeHelper->setPriceFormate(11000*0/100) .'    ( 0% )</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_44500).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_44500*20/100) .' (20%)</td></tr>';
            $rr .= '<tr><td>Total Tax</td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
            return $rr ;

        }elseif('44500' < $amount && $amount <= '150000'){  // 40%
            $val_44500 = 44500 - 11000 ;    // 20%
            $val_150000 = $amount - 44500 ; // 40%
            $val_44500_per =  $val_44500 * 20 /100 ;
            $val_150000_per =  $val_150000 * 40 /100 ;
             $totaltax = $val_44500_per+$val_150000_per ;
            $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate('11000').'</td><td> '. $currency.$financeHelper->setPriceFormate(11000*0/100) .'    ( 0% )</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_44500).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_44500*20/100) .' (20%)</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_150000).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_150000*40/100) .' (40%)</td></tr>';
            $rr .= '<tr><td><b>Total Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
            return $rr ;
        }elseif('150000' < $amount){ // 45%
            $val_44500 = 44500 - 11000 ; // 20%
            $val_150000 = 150000 - 44500 ; // 40%
            $val_150000_above = $amount - 150000;
            $val_44500_per =  $val_44500 * 20/100 ;
            $val_150000_per =  $val_150000 * 40/100 ;
            $val_150000_above_per = $val_150000_above * 45 /100 ;
            $totaltax = $val_44500_per+$val_150000_per+$val_150000_above_per ;

            $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate('11000').'</td><td> '. $currency.$financeHelper->setPriceFormate(11000*0/100) .'    ( 0% )</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_44500).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_44500*20/100) .' (20%)</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_150000).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_150000*40/100) .' (40%)</td></tr>';
            $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_150000_above).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_150000_above*45/100) .' (45%)</td></tr>';
            $rr .= '<tr><td><b>Total Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
            return $rr ;
        }
    }
    
    
    
    public function taxclaculation_help($amount = 0 , $finusertype = null, $finyear=null, $userid = null) {
        
        $financeHelper = new FinanceHelper();        
        $financeTaxController = new FinanceTaxController();
        if($finyear == null){
            $finyear = date('Y');
        }
        $financeyear = $financeHelper->getMonthFinancialYear($userid , $finyear) ;     
        $taxdata = $financeTaxController->taxListByinyear($financeyear);


        /*Net Profit*/
        if($userid == null){
            $userid = $_SESSION['user_id'];
        }
        $overallincome_curryear = $financeHelper->getIncomeByuser($userid , $finyear);
        $overallexpence_curryear = $financeHelper->getExpenceByuser($userid , $finyear);
        $amount = $overallincome_curryear['job_rate'] - $overallexpence_curryear['cost'];
     
        $currency = '£';
        $rr = '';
    
        if($finusertype!=null && $finusertype=='limitedcompany'){
        
            if($taxdata){
                $taxper = $taxdata->getCompanyLimitedTax();
            }else{
                $taxper = '20';
            }
            $totaltax = $amount*$taxper / 100 ;
            
            $rr  = '<tr><td>£'.$amount.'</td><td>£'.$totaltax.' ('.$taxper.'%)</td></tr>' ;
            $rr .= '<tr><td><b>Total Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
            return $rr ;
               
        }else{
    
            if($taxdata){
                $basicrate_amt =  $taxdata->getPersonalAllowanceRate() ;
                $higherrate_amt = $taxdata->getBasicRate() ;
                $additionalrate_amt =  $taxdata->getHigherRate() ;
                   
                $basicrate_per = $taxdata->getBasicRateTax() ;
                $higherrate_per = $taxdata->getHigherRateTax() ;
                $additionalrate_per = $taxdata->getAdditionalRateTax() ;
            }else{    
                $basicrate_amt =  '11000' ;
                $higherrate_amt = '44500' ;
                $additionalrate_amt = '150000' ;
                   
                $basicrate_per = '20' ;
                $higherrate_per = '40' ;
                $additionalrate_per = '45' ;      
            }     

            if($basicrate_amt >= $amount){ // 0%
                $totaltax = 0 ;
                $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td> '. $currency.$financeHelper->setPriceFormate($basicrate_amt*0/100) .'    ( 0% )</td></tr>';
                $rr .= '<tr><td><b><b>Total Normal Tax</b></b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
                
            }elseif($basicrate_amt < $amount && $amount <= $higherrate_amt){ // 20%
                $val_higherrate_amt = $amount - $basicrate_amt; // 20%
                $val_higherrate_amt_per =  $val_higherrate_amt * $basicrate_per /100 ;
                $totaltax = $val_higherrate_amt_per ;
                $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($basicrate_amt).'</td><td> '. $currency.$financeHelper->setPriceFormate($basicrate_amt*0/100) .'    ( 0% )</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_higherrate_amt).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_higherrate_amt*$basicrate_per/100) .' ('.$basicrate_per.'%)</td></tr>';
                $rr .= '<tr><td><b>Total Normal Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';

            }elseif($higherrate_amt < $amount && $amount <= $additionalrate_amt){  // 40%
                $val_higherrate_amt = $higherrate_amt - $basicrate_amt ;    // 20%
                $val_additionalrate_amt = $amount - $higherrate_amt ; // 40%
                $val_higherrate_amt_per =  $val_higherrate_amt * $basicrate_per/100 ;
                $val_additionalrate_amt_per =  $val_additionalrate_amt * $higherrate_per /100 ;
                 $totaltax = $val_higherrate_amt_per+$val_additionalrate_amt_per ;
                $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($basicrate_amt).'</td><td> '. $currency.$financeHelper->setPriceFormate($basicrate_amt*0/100) .'    ( 0% )</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_higherrate_amt).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_higherrate_amt*$basicrate_per/100) .' ('.$basicrate_per.'%)</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_additionalrate_amt).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_additionalrate_amt*$higherrate_per/100) .' ('.$higherrate_per.'%)</td></tr>';
                $rr .= '<tr><td><b>Total Normal Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($totaltax).'</td></tr>';
                
            }elseif($additionalrate_amt < $amount){ // 45%
                $val_higherrate_amt = $higherrate_amt - $basicrate_amt ; // 20%
                $val_additionalrate_amt = $additionalrate_amt - $higherrate_amt ; // 40%
                $val_additionalrate_amt_above = $amount - $additionalrate_amt;
                $val_higherrate_amt_per =  $val_higherrate_amt * $basicrate_per/100 ;
                $val_additionalrate_amt_per =  $val_additionalrate_amt * $higherrate_per/100 ;
                $val_additionalrate_amt_above_per = $val_additionalrate_amt_above * $additionalrate_per/100 ;
                $totaltax = $val_higherrate_amt_per+$val_additionalrate_amt_per+$val_additionalrate_amt_above_per ;

                $rr = '<tr><td>'.$currency.$financeHelper->setPriceFormate($amount).'</td><td>Tax Rate</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($basicrate_amt).'</td><td> '. $currency.$financeHelper->setPriceFormate($basicrate_amt*0/100) .'    ( 0% )</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_higherrate_amt).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_higherrate_amt*$basicrate_per/100) .' ('.$basicrate_per.'%)</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_additionalrate_amt).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_additionalrate_amt*$higherrate_per/100) .' ('.$higherrate_per.'%)</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_additionalrate_amt_above).'</td><td>'. $currency.$financeHelper->setPriceFormate($val_additionalrate_amt_above*$additionalrate_per/100) .' ('.$additionalrate_per.'%)</td></tr>';
                
            }

            /* Ni Tax */
            $niTaxData = $this->getNiTaxSetting($financeyear);
            if ($niTaxData) {                
                $c4_min_amount_1    = $niTaxData->getC4MinAmmount_1(); // Nil
                $c4_min_amount_2    = $niTaxData->getC4MinAmmount_2(); // 9%
                $c4_above_amount_3  = $niTaxData->getC4MinAmmount_3(); // 2%
                $c2_amount          = $niTaxData->getC2MinAmount(); // 2.85 % per week of year                                  
                
                $c4_min_amount_2_tax    = $niTaxData->getC4MinAmmountTax_2();
                $c4_above_amount_3_tax  = $niTaxData->getC4MinAmmountTax_3();
                $c2_amount_tax          = $niTaxData->getC2Tax();

            }else{
                $c4_min_amount_1    = '8000'; // Nil
                $c4_min_amount_2    = '45000'; // 9%
                $c4_above_amount_3  = '45001'; // 2%
                $c2_amount          = '6025'; // 2.85 % per week of year
                   
                $c4_min_amount_2_tax    = '2' ;
                $c4_above_amount_3_tax  = '9' ;
                $c2_amount_tax          = '148.2';
            }

            $rr .= "<tr><td colspan='2' bgcolor='#e6f9ff'><b>NI Tax</b></td></tr>";
            $rr .= "<tr><td>Class 4 NI Amount</td><td>Tax Rate</td></tr>";
            if($c4_min_amount_1 >= $amount){ // 0% Personal Allowance
                $nitotaltax = 0 ;
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($c4_min_amount_1).'</td><td>'.$currency.'0 (0%)</td></tr>';
            }elseif($c4_min_amount_1 < $amount && $amount <= $c4_min_amount_2){ // 9% 
                $val_45000      = $amount - $c4_min_amount_1 ; // 9%
                $val_45000_per  = $val_45000 * $c4_min_amount_2_tax /100 ;
                $nitotaltax     = $val_45000_per ;
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($c4_min_amount_1).'</td><td>'.$currency.'0 (0%)</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_45000).'</td><td> '. $currency.$financeHelper->setPriceFormate($val_45000_per) .'    ( '.$c4_min_amount_2_tax.'% )</td></tr>';

            }elseif($c4_min_amount_2 < $amount ){  // 2% 
                $val_45000          = $c4_min_amount_2 - $c4_min_amount_1 ;    // 9%
                $val_45k_plus       = $amount - $c4_min_amount_2 ; // 2%
                $val_45000_per      = $val_45000 * $c4_min_amount_2_tax /100 ;
                $val_45k_plus_per   = $val_45k_plus * $c4_above_amount_3_tax /100 ;
                $nitotaltax         = $val_45000_per + $val_45k_plus_per ;
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($c4_min_amount_1).'</td><td>'.$currency.'0.00 (0%)</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_45000).'</td><td> '. $currency.$financeHelper->setPriceFormate($val_45000_per) .'    ( '.$c4_min_amount_2_tax.'% )</td></tr>';
                $rr .= '<tr><td>'.$currency.$financeHelper->setPriceFormate($val_45k_plus).'</td><td> '. $currency.$financeHelper->setPriceFormate($val_45k_plus_per) .'    ( '.$c4_above_amount_3_tax.'% )</td></tr>';
            }           
            
            
            $rr .= '<tr><td><b>Class 4 NI Total Tax</b></td><td> '. $currency.$financeHelper->setPriceFormate($nitotaltax) .'</td></tr>';
            $c2_tax = $currency.'0.00';
            if($c2_amount < $amount){ // add yearlly charge
                $nitotaltax = $nitotaltax + $c2_amount_tax;
                $c2_tax = $currency.$financeHelper->setPriceFormate($c2_amount_tax);
            }
            $rr .= '<tr><td><b>Class 2 NI Total Tax</b></td><td> '. $c2_tax .'</td></tr>';

            $fullTotalTax = $totaltax + $nitotaltax;

            $rr .= '<tr><td><b>Total Income Tax</b></td><td>'.$currency.$financeHelper->setPriceFormate($fullTotalTax).'</td></tr>';

            return $rr;
        }        
    }

    
    

    /**
     * This action is used when user has no access to display one page
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forbiddenAction()
    {
        $this->getResponse()->setStatusCode(403);
        $this->getResponse()->isForbidden(true);
        $this->layout()->setVariable('module', null);
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

    /* Get Ni tax setting of admin */
    public function getNiTaxSetting($financeyear)
    {        
        $financeTaxYear = FinanceNiTax\Model::fromFinyear($financeyear);              
        return $financeTaxYear;
    }
}