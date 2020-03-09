<?php
/**
* Config class for mailchimp, develope by SURAJ WASNIK at FUDUGO
*/
namespace Gc\User\Mailchimp;
class MailchimpConfig 
{
    
    public function get_config($var)
    {
        switch ($var) {
            case 'API':
                //API Key - see http://admin.mailchimp.com/account/api
                $apikey = '38de432ffb572e9da931d3d6a6f9fa26-us13';
                return $apikey;
                break;
            case 'LISTID':
                // A List Id to run examples against. use lists() to view all
                // Also, login to MC account, go to List, then List Tools, and look for the List ID entry
                $listId = 'fb441ef5f1';
                return $listId;
                break;
            case 'MYEMAIL':
                //some email addresses used in the examples:
                $my_email = 'locumkit@fudugosolutions.com';
                return $my_email;
                break;
            case 'BOSSMANEMAIL':
                $boss_man_email = 'locumkit@fudugosolutions.com';
                return $boss_man_email;
                break;
        }
        
        // A Campaign Id to run examples against. use campaigns() to view all
        $campaignId = 'YOUR MAILCHIMP CAMPAIGN ID - see campaigns() method';
        
        //just used in xml-rpc examples
        $apiUrl = 'http://api.mailchimp.com/1.3/';
    }
    
}
