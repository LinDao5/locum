<?php 
	function send_sms($mobile_numbers, $content) { 
	
           function post_to_url($url, $data) {
           $fields = '';
           foreach($data as $key => $value) { 
              $fields .= $key . '=' . $value . '&'; 
           }
           rtrim($fields, '&');

           $post = curl_init();

           curl_setopt($post, CURLOPT_URL, $url);
           curl_setopt($post, CURLOPT_POST, count($data));
           curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
           curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

           echo $result = curl_exec($post);

           curl_close($post);
        }

        $data = array(
           "action" => "sendsms",	 //user
		   
           "user" => "q9fxpxx6",	 //user
		   
           "password" => "QxG8XBgB",	             //type your password

           "from" =>"Test",	             //type your senderID

           "to" => $mobile_numbers,         //*Note*  for single sms enter  ”singlemsg” , for bulk   		enter “bulkmsg”

           "text"  => urlencode($content)	             //type the message.
            
        );

      echo  $ret = post_to_url("https://www.smsglobal.com/http-api.php", $data);
		if($ret) { 
			echo  'Message send successfully!';
		}
    }
	
	send_sms ('+918461001174', 'Hi raginee');	
	

?>