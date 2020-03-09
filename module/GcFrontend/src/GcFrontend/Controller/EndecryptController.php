<?php
/**
 * PHP Version >=5.3
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 */

 namespace GcFrontend\Controller;
 use Gc\Mvc\Controller\Action;

 class EndecryptController extends Action
 {
   /*public function encryptIt( $q ) {
    $cryptKey  = '1234567890abcdefghijklmnopqrstuvwxyz';
    $qEncoded  = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    return( $qEncoded );
   }

   public function decryptIt( $q ) {
    $cryptKey  = '1234567890abcdefghijklmnopqrstuvwxyz';
    $qDecoded  = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
   }*/

function encryptIt($sData){
    $secretKey="1234567890abcdefghijklmnopqrstuvwxyz";
    $sResult = '';
    for($i=0;$i<strlen($sData);$i++){
        $sChar    = substr($sData, $i, 1);
        $sKeyChar = substr($secretKey, ($i % strlen($secretKey)) - 1, 1);
        $sChar    = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;

    }
    return base64_encode($sResult);
} 

function decryptIt($sData){
    $secretKey="1234567890abcdefghijklmnopqrstuvwxyz";
    $sResult = '';
    $sData   = base64_decode($sData);
    for($i=0;$i<strlen($sData);$i++){
        $sChar    = substr($sData, $i, 1);
        $sKeyChar = substr($secretKey, ($i % strlen($secretKey)) - 1, 1);
        $sChar    = chr(ord($sChar) - ord($sKeyChar));
        $sResult .= $sChar;
    }
    return $sResult;
}
 }
