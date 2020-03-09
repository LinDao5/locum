<?php
    namespace Gc\User; 
    use Gc\Db\AbstractTable;
    use Zend\Db\Sql\Select;

    class PackageRateReport extends AbstractTable
    {
        protected $name = 'package_rate_report';

        public function getPackageRates($start_date = null, $end_date = null)
        {      
            //check if user upgrade package in during session
            if ($start_date != null AND $end_date != null) {
                $select_user_payment = "SELECT uid,price,package_id,created_date FROM user_payment_info WHERE  payment_status = 1 AND created_date BETWEEN '$start_date' AND '$end_date' AND price != '0.00' ORDER BY package_id ASC";
            }else{
                $select_user_payment = "SELECT uid,price,package_id,created_date FROM user_payment_info WHERE  payment_status = 1 AND price != '0.00' ORDER BY package_id ASC";
            }
            
            $user_payment_record = $this->fetchAll($select_user_payment);            
            
            $user_package_records = '';
            $packages_report = $packages_rate = array();
            if (!empty($user_payment_record)) {
                foreach ($user_payment_record as $key => $payment_record){
                    $user_id = $payment_record['uid'];
                    $user_package_id = $payment_record['package_id'];
                    $record_created_date = $payment_record['created_date'];

                    $packages_report[$user_package_id]['name'] = $this->getPackageNameById($user_package_id);

                    $packages_report[$user_package_id]['package_rate'] = $payment_record['price'];
                    $packages_report[$user_package_id]['total_rate'] += (float)$payment_record['price'];
                    $packages_report[$user_package_id]['count'] ++;

                    $select = "SELECT * FROM package_rate_report WHERE created_at <= '$record_created_date' AND (updated_at >= '$record_created_date' OR status = 1 ) AND package_id = '$user_package_id'";
                    $rows  = $this->fetchAll($select);
                    
                    if ($rows[0]['package_rate']) {
                        $packages_rate[$user_package_id][] = array(                        
                                'rate' => $rows[0]['package_rate'],
                                'created_at' => $rows[0]['created_at'],
                                'updated_at' => $rows[0]['updated_at'],
                            );  
                    }                    
                }  
                
                foreach ($packages_rate as $key => $records) { 
                    $group_array = $this->array_group_by($records, "rate");                 
                    foreach ($group_array as $key_rate => $value) {
                        $packages_report[$key]['details'][] = array(
                                'rate' => $key_rate,
                                'no_records' => count($value),
                            );
                    }               
                }
            }  
            return $packages_report;
        }

        public function getUserCollectionByDateRange($start_date = null, $end_date = null){
            if ($start_date != null && $end_date != null) {
                $select = "SELECT id, created_at,user_acl_package_id  FROM user WHERE created_at BETWEEN '$start_date' AND '$end_date' AND user_acl_role_id = '2' AND user_acl_package_id != 0 ORDER BY user_acl_package_id ASC";
            }else{
                $select = "SELECT id, created_at, user_acl_package_id  FROM user WHERE user_acl_role_id = '2' AND user_acl_package_id != 0 ORDER BY user_acl_package_id ASC";
            }
            $rows  = $this->fetchAll($select);
            return $rows;
        }

        public function getPackageNameById($pkg_id){
            $select = "SELECT name FROM user_acl_package WHERE id = '$pkg_id'";
            $rows  = $this->fetchAll($select);
            return $rows[0]['name'];
        }

        public function array_group_by(array $array, $key)
        {
            if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key) ) {
                trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
                return null;
            }
            $func = (!is_string($key) && is_callable($key) ? $key : null);
            $_key = $key;
            // Load the new array, splitting by the target key
            $grouped = [];
            foreach ($array as $value) {
                $key = null;
                if (is_callable($func)) {
                    $key = call_user_func($func, $value);
                } elseif (is_object($value) && isset($value->{$_key})) {
                    $key = $value->{$_key};
                } elseif (isset($value[$_key])) {
                    $key = $value[$_key];
                }
                if ($key === null) {
                    continue;
                }
                $grouped[$key][] = $value;
            }
            // Recursively build a nested grouping if more parameters are supplied
            // Each grouped array value is grouped according to the next sequential key
            if (func_num_args() > 2) {
                $args = func_get_args();
                foreach ($grouped as $key => $value) {
                    $params = array_merge([ $value ], array_slice($args, 2, func_num_args()));
                    $grouped[$key] = call_user_func_array('array_group_by', $params);
                }
            }
            return $grouped;
        }

        public function addIntoPackageReport($pkg_id,$rate)
        {
            //Insert into package report table            
            $select = "SELECT id,package_id,package_rate FROM package_rate_report WHERE package_id = '$pkg_id' AND status = '1'";
            $pkg_report_records = $this->fetchAll($select);
            $current_date = date('Y-m-d H:i:s');
            if (!empty($pkg_report_records)) {
                $record_id = $pkg_report_records[0]['id'];
                $package_rate = $pkg_report_records[0]['package_rate'];
                if ($package_rate != $rate) {
                    $array_update = array(
                        'status' => 0,
                        'updated_at' => $current_date
                    );
                    $where_array = array(
                            'package_id' => $pkg_id,
                            'id'    => $record_id
                        );                
                    $this->update($array_update,$where_array);

                    $add_array = array(
                        'package_id' => $pkg_id,
                        'package_rate' => $rate,
                        'created_at' => $current_date,
                        'status' => 1
                    );
                    $this->insert($add_array);   
                }
            }else{
                $add_array = array(
                    'package_id' => $pkg_id,
                    'package_rate' => $rate,
                    'created_at' => $current_date,
                    'status' => 1
                );
                $this->insert($add_array);   
            }        
        }
    }