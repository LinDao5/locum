<title>Send Mail - <?php echo $title; ?></title>
<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Load Composer's autoloader
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';
if (isset($_POST['sendmessage']))
{
    $title = $_POST['title'];
    $list = $_POST['list'];
    $message = $_POST['message'];
    if (empty($title))
    {
        $title_error = '
        <div class="alert alert-danger" role="alert">
        <i class="tl-close"></i>
        This Field is Required.
        </div> 
        ';
        $error .='1'; 
    }
        if ($list == 0)
        {
            $list_error = '
            <div class="alert alert-danger" role="alert">
            <i class="tl-close"></i>
            This Field is Required.
            </div> 
            ';
            $error .='1'; 
            }
            if (empty($message))
            {
                $message_error = '
                <div class="alert alert-danger" role="alert">
                <i class="tl-close"></i>
                This Field is Required.
                </div> 
                ';
                $error .='1'; 
            }
        if(empty($error))
            {		
                
                $getMails = mysqli_query($conn,"SELECT * FROM mails WHERE listid='$list'") or die(mysqli_error());
                while ($maile = mysqli_fetch_array($getMails)) {
                    // Instantiation and passing `true` enables exceptions
                    $mail = new PHPMailer(true);
                    $gmail = $maile['mail'];
                    try {
                        //Server settings
                        $mail->SMTPDebug = 2;                                       // Enable verbose debug output
                        $mail->isSMTP();                                            // Set mailer to use SMTP
                        $mail->Host       = $hostname;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = $hostmail;                     // SMTP username
                        $mail->Password   = $hostmailPass;                               // SMTP password
                        $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
                        $mail->Port       = 465;                                    // TCP port to connect to


                        //Recipients
                        $mail->setFrom($hostmail, $SenderName);
                        $mail->addAddress($gmail, $gmail);     // Add a recipient
                        $mail->addAddress($gmail);               // Name is optional
                        $mail->addReplyTo($hostmail, 'Information');
                        $mail->addCC($gmail);
                        $mail->addBCC($gmail);


                        // Attachments
                        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

                        // Content
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $title;
                        $mail->Body    = $message;
                        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                        $mail->isHTML(true);    

                        $mail->send();
                    } catch (Exception $e) {
                    }
                    }
                $done =  '
                <div class="alert alert-success" role="alert">
                <i class="fa fa-check"></i>
                Message Sent successfuly !
                </div> 
                ';
                header("Location: $site_url/mailing");
            }
	
}
?>
<style>
.note-popover.popover{
    display:none;
}
</style>

    <!-- Begin Page Content -->
     <div class="container-fluid">

         <!-- Page Heading -->
         <div class="d-sm-flex align-items-center justify-content-between mb-4">
             <h1 class="h3 mb-0 text-gray-800">Send Mails By List</h1>
             <a href="#" data-toggle="modal" data-target="#logoutModal"
                 class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                     class="fas fa-sign-out-alt fa-sm text-white-50"></i> Logout</a>
         </div>
         <!-- Content Row -->
         <div class="row">
             <div class="col-12">
                 <!-- Area Chart -->
                 <div class="card shadow mb-4">
                     <div class="card-header py-3">
                         <h6 class="m-0 font-weight-bold text-primary">Send Mails</h6>
                     </div>
                     <div class="card-body">
                         <form class="user add-user" method="post">
                             <div class="form-group">
                                 <input type="text" class="form-control form-control-user" name="title"
                                     aria-describedby="emailHelp" placeholder="Enter message title...">
                             </div>
                             <? echo $title_error; ?>
                             <div class="form-group">
                             <select class="custom-select" name="list">
                            <option selected value="0">Select List</option>
                            <? 
                                    $getLists = mysqli_query($conn,"SELECT * FROM maillist")or die(mysqli_error());
                                    while ($list = mysqli_fetch_array($getLists)) {
                            ?>
                            <option value="<?php echo $list['ID']; ?>"><?php echo $list['ListName']; ?></option>
                            <?php } ?>
                            </select>
                                    </div>
                                    <? echo $list_error; ?>
                                    <script src="<?php echo $site_url; ?>/ckeditor/ckeditor.js"></script>
                                    <div class="form-group">
                                    <textarea id="summernote" class="form-control" name="message"
                                     aria-describedby="emailHelp" placeholder="Enter Message..."></textarea>
                                    </div>
                                    <? echo $message_error; ?>
                                    <script>
                                    CKEDITOR.replace( 'message' );
                                    </script>
                             <button name="sendmessage" class="btn btn-primary btn-user btn-block">
                                 Send
                             </button>
                             <hr>
                             <? echo $done; ?>
                         </form>
                     </div>
                 </div>
             </div>
             <!-- Content Row -->
         </div>
         <!-- /.container-fluid -->
     </div>
     <!-- End of Main Content -->
