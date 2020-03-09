<?
session_start();
ob_start(); 
include('./inc/config.php');
include ('./pages/header.php');
/* File Name */
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
// The PHP file extension
// this global constant is deprecated.
define('EXT', '.php');
$act = isset($_REQUEST['pages']) ? trim($_REQUEST['pages']) : '';
$array = array(
    'home' => 'pages',
    'login' => 'pages',
    'logout' => 'pages',
    'users' => 'pages',
    'add-user' => 'pages',
    'edit-user' => 'pages',
    'user' => 'pages',
    'add-list' => 'pages',
    'add-mail' => 'pages',
    'mail-list' => 'pages',
    'list' => 'pages',
    'mailremove' => 'pages',
    'listremove' => 'pages',
    'mailing' => 'pages',
    'changepassword' => 'pages',
    '404' => 'pages',
);  
$path = !empty($array[$act]) ? $array[$act] . '/' : '';
if(array_key_exists($act, $array) && file_exists($path . $act . '.php'))
{
require_once($path . $act . '.php');
} 
else
{
include './pages/home.php';
}
include './pages/footer.php';
    
?>
<? ob_end_flush(); ?>