<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

define('MAIL_NAME', "iwajaki1994220@gmail.com");
define('MAIL_PASSWORD', "AHSUSahsus_19942200");

define('SENDER_MAIL_ADDRESS', "admin@localhost.com");
define('SENDER_NAME', "Administrator of locumkit");

define('REPLY_MAIL_ADDRESS', "admin@localhost.com");


function getMailObj(){
    $mail = new PHPMailer(true);
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    
    $mail->Username   = MAIL_NAME;                     // SMTP username
    $mail->Password   = MAIL_PASSWORD;                               // SMTP password
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
    $mail->Port       = 587;                                    // TCP port to connect to

    $mail->setFrom(SENDER_MAIL_ADDRESS, SENDER_NAME);
    $mail->addReplyTo(REPLY_MAIL_ADDRESS);
    
    $mail->isHTML(true);                                  // Set email format to HTML
    return $mail;
}

date_default_timezone_set('Europe/London');
echo "current time is ".date("Y-m-d H:i")."<br>";

try {
    //Server settings
    
    define('HOST', "localhost");
    define('USERNAME', "umairc65_locum");
    define('PASSWORD', "Zxcvbnm123.*");
    define('DB', "umairc65_locumkit");
    $connect_main = mysqli_connect(HOST, USERNAME, PASSWORD, DB);

    define('USERNAME1', "umairc65_logbook");
    define('PASSWORD1', "Logbook10");
    define('DB1', "umairc65_locumkit_qs");
    $connect = mysqli_connect(HOST, USERNAME1, PASSWORD1, DB1);
    
    
    if ($connect) {
        // questionaire1 search to reminder
        $sql = "select * from tbl_questionaire_1 where completed_tick != 1 AND  !ISNULL(reminder_datetime)";
        $result = $connect->query($sql);

        foreach ($result as $item ) {
            $current_date = date("Y-m-d H:i");
            $reminder_date = date_create($item['reminder_datetime']);
            $reminder_date = date_format($reminder_date,"Y-m-d H:i");
            if ($current_date == $reminder_date){
                //get user_email by user_id
                $temp = $connect_main->query('select email from user where id='.$item['user_id']);
                $user_email = "";
    		foreach($temp as $item1){
    			$user_email = $item1['email'];
    		}
                $mail = getMailObj();
                $mail->addAddress($user_email);     // Add a recipient
                $mail->Subject = 'Message from locumkit';
                $mail->Body    = '  <div style="color:black" >
                                    <h3>Hi</h3>
                                    <h4>This is a reminder email that you had requested for the below mentioned log</h4>
                                    <br>
                                    <span>Practice Name : </span>
                                    <span style="font-weight:bold">'
                                    .$item['practice_name']
                                    .'</span>
                                    <br>
                                    <span>Date : </span>
                                    <span style="font-weight:bold">'
                                    .$item['date']
                                    .'</span>
                                    <br>
                                    <span>Patient ID : </span>
                                    <span style="font-weight:bold">'
                                    .$item['patient_id']
                                    .'</span>
                                    <br>
                                    <span>Referred to : </span>
                                    <span style="font-weight:bold">'
                                    .$item['referred_to']
                                    .'</span>
                                    <br>
                                    <span>Issue in hand : </span>
                                    <span style="font-weight:bold">'
                                    .$item['issue_hand']
                                    .'</span>
                                    <br>
                                    <span>Action Required : </span>
                                    <span style="font-weight:bold">'
                                    .$item['action_required']
                                    .'</span>
                                    <br>
                                    <span>Notes : </span>
                                    <span style="font-weight:bold">'
                                    .$item['notes']
                                    .'</span>
                                    <br>
                                    </div>
                                    ';


                $mail->send();
                echo 'Message has been sent to user id : '.$item['user_id'].'<br>';
            }
        }

        // questionaire2 search to reminder
        $sql = "select * from tbl_questionaire_2 where completed_tick != 1 AND !ISNULL(reminder_datetime)";
        $result = $connect->query($sql);
        foreach ($result as $item ) {
            $current_date = date("Y-m-d H:i");
            $reminder_date = date_create($item['reminder_datetime']);
            $reminder_date = date_format($reminder_date,"Y-m-d H:i");
            if ($current_date == $reminder_date){
                //get user_email by user_id
                $temp = $connect_main->query('select email from user where id='.$item['user_id']);
                $user_email = "";
    		foreach($temp as $item1){
    			$user_email = $item1['email'];
    		}
                $mail = getMailObj();
                $mail->addAddress($user_email);     // Add a recipient
                $mail->Subject = 'Message from locumkit';
                $mail->Body    = '  <div style="color:black" >
                                    <h3>Hi</h3>
                                    <h4>This is a reminder email that you had requested for the below mentioned log</h4>
                                    <br>
                                    <span>Practice Name : </span>
                                    <span style="font-weight:bold">'
                                    .$item['practice_name']
                                    .'</span>
                                    <br>
                                    <span>Date : </span>
                                    <span style="font-weight:bold">'
                                    .$item['date']
                                    .'</span>
                                    <br>
                                    <span>Issue : </span>
                                    <span style="font-weight:bold">'
                                    .$item['issue']
                                    .'</span>
                                    <br>
                                    <span>Notes : </span>
                                    <span style="font-weight:bold">'
                                    .$item['notes']
                                    .'</span>
                                    <br>
                                    </div>
                                    ';
                $mail->send();
                echo 'Message has been sent to user id : '.$item['user_id'].'<br>';
            }
        }

        // questionaire3 search to reminder
        $sql = "select * from tbl_questionaire_3 where completed_tick != 1 AND !ISNULL(reminder_datetime)";
        $result = $connect->query($sql);
        foreach ($result as $item ) {
            $current_date = date("Y-m-d H:i");
            $reminder_date = date_create($item['reminder_datetime']);
            $reminder_date = date_format($reminder_date,"Y-m-d H:i");
            if ($current_date == $reminder_date){
                //get user_email by user_id
                $temp = $connect_main->query('select email from user where id='.$item['user_id']);
                $user_email = "";
    		foreach($temp as $item1){
    			$user_email = $item1['email'];
    		}
                $mail = getMailObj();
                $mail->addAddress($user_email);     // Add a recipient
                $mail->Subject = 'Message from locumkit';
                $mail->Body    = '  <div style="color:black" >
                                    <h3>Hi</h3>
                                    <h4>This is a reminder email that you had requested for the below mentioned log</h4>
                                    <br>
                                    <span>Practice Name : </span>
                                    <span style="font-weight:bold">'
                                    .$item['practice_name']
                                    .'</span>
                                    <br>
                                    <span>Date : </span>
                                    <span style="font-weight:bold">'
                                    .$item['date']
                                    .'</span>
                                    <br>
                                    <span>Issue : </span>
                                    <span style="font-weight:bold">'
                                    .$item['issue']
                                    .'</span>
                                    <br>
                                    <span>Notes : </span>
                                    <span style="font-weight:bold">'
                                    .$item['notes']
                                    .'</span>
                                    <br>
                                    <span>Action Required : </span>
                                    <span style="font-weight:bold">'
                                    .$item['action_required']
                                    .'</span>
                                    <br>
                                    </div>
                                    ';
                $mail->send();
                echo 'Message has been sent to user id : '.$item['user_id'].'<br>';
            }
        }

        
        // questionaire6 search to reminder
        $sql = "select * from tbl_questionaire_6 where completed_tick != 1 AND !ISNULL(reminder_datetime)";
        $result = $connect->query($sql);
        foreach ($result as $item ) {
            $current_date = date("Y-m-d H:i");
            $reminder_date = date_create($item['reminder_datetime']);
            $reminder_date = date_format($reminder_date,"Y-m-d H:i");
            if ($current_date == $reminder_date){
                //get user_email by user_id
                $temp = $connect_main->query('select email from user where id='.$item['user_id']);
                $user_email = "";
    		foreach($temp as $item1){
    			$user_email = $item1['email'];
    		}
                $mail = getMailObj();
                $mail->addAddress($user_email);     // Add a recipient
                $mail->Subject = 'Message from locumkit';
                $mail->Body    = '  <div style="color:black" >
                                    <h3>Hi</h3>
                                    <h4>This is a reminder email that you had requested for the below mentioned log</h4>
                                    <br>
                                    <span>Practice name : </span>
                                    <span style="font-weight:bold">'
                                    .$item['practice_name']
                                    .'</span>
                                    <br>
                                    <span>Date : </span>
                                    <span style="font-weight:bold">'
                                    .$item['date']
                                    .'</span>
                                    <br>
                                    <span>Patient ID : </span>
                                    <span style="font-weight:bold">'
                                    .$item['patient_id']
                                    .'</span>
                                    <br>
                                    <span>Notes : </span>
                                    <span style="font-weight:bold">'
                                    .$item['notes']
                                    .'</span>
                                    <br>
                                    <span>Action Required : </span>
                                    <span style="font-weight:bold">'
                                    .$item['action_required']
                                    .'</span>
                                    <br>
                                    </div>
                                    ';
                $mail->send();
                echo 'Message has been sent to user id : '.$item['user_id'].'<br>';
            }
        }

    } else {
        die(mysqli_error($connect));
    }
    
    echo 'finish successfully'.'<br>';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}