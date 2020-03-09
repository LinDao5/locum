<?php
/**
* Controller to Fetch the list of user in mailchimp list, develope by     * SURAJ WASNIK at FUDUGO
*/
namespace Gc\User\Mailchimp;
use Gc\User\Mailchimp\MailchimpConfig as MConfig;
use Gc\User\Mailchimp\MCAPI as MCAPI;

class Mailchimp 
{
    public function getSubscribeUserList()
    {
        $config = new MConfig();
        $apikey = $config->get_config('API');
        //echo "<br/>";
        $listId = $config->get_config('LISTID');
        // "<br/>";
        $my_email = $config->get_config('MYEMAIL');
        //echo "<br/>";
        $boss_man_email = $config->get_config('BOSSMANEMAIL');
        //echo "<br/>";
        $api = new MCAPI();
        $api1 = $api->MCAPI($apikey);
        $retval = $api->listMembers($listId);
        if ($api->errorCode){
            echo "Unable to load listMemberInfo()!\n";
            echo "\tCode=".$api->errorCode."\n";
            echo "\tMsg=".$api->errorMessage."\n";
        } else {
            if (isset($retval['success'])) {
                echo "Success:".$retval['success']."\n";
            }
            if (isset($retval['error'])) {
                echo "Errors:".sizeof($retval['error'])."\n";
            }
            
            /*echo "<pre>";
            print_r($retval);
            echo "</pre>";*/
            return $retval;
        }
    }
}

?>