<title>Email List - <?php echo $title; ?></title>
<?php
if($user['State'] != 1){
  header("Location: $site_url/login");
  return;
}
$ID = $_GET['ID'];
$getpro = mysqli_query($conn,"SELECT * FROM maillist WHERE ID = '$ID'");
$list = mysqli_fetch_assoc($getpro);
if (isset($_POST['edituser']))
{
	$UserNew = $_POST['Username'];
    $Password = $_POST['Password'];
	
    if (empty($UserNew))
    {
        $user_error = '
        <div class="alert alert-danger" role="alert">
        <i class="tl-close"></i>
        This Field is Required.
        </div> 
        ';
        $error .='1'; 
        }
       if (empty($Password))
        {
           $pass_error =  '
           <div class="alert alert-danger" role="alert">
           <i class="fa fa-times"></i>
           This Field is Required.
           </div> 
           ';
           $error .='1'; 
       }
        if(empty($error))
            {		
                $addpro = mysqli_query($conn,"UPDATE users SET UserName='$UserNew', Password='$Password' WHERE UID='$ID'")or die(mysqli_error($conn));
                $done =  '
                <div class="alert alert-success" role="alert">
                <i class="fa fa-check"></i>
                Updated successfuly !
                </div> 
                ';
            }
	
}
?>
    <!-- Begin Page Content -->
     <div class="container-fluid">

         <!-- Page Heading -->
         <div class="d-sm-flex align-items-center justify-content-between mb-4">
             <h1 class="h3 mb-0 text-gray-800">Edit (<?php echo $list['ListName']; ?>) User</h1>
             <a href="#" data-toggle="modal" data-target="#logoutModal"
                 class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                     class="fas fa-sign-out-alt fa-sm text-white-50"></i> Logout</a>
         </div>
         <!-- Content Row -->
         <div class="row">
             <div class="col-12">
                   <!-- DataTales Example -->
          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary"><?php echo $list['ListName']; ?> List <a style="float:right" href="<?php echo $site_url; ?>/RemoveList/<? echo $ID; ?>" class="btn btn-danger btn-circle btn-sm">
                        <i class="fas fa-trash btn-sm"></i>
                    </a></h6>
              
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th>Mail</th>
                      <th>List</th>
                      <th>Remove</th>
                    </tr>
                  </thead>
                  <tbody>
                  <? 
						$getList = mysqli_query($conn,"SELECT * FROM mails WHERE listid = '$ID'")or die(mysqli_error());
						while ($mail = mysqli_fetch_array($getList)) {
                ?>
                    <tr>
                      <td><?php echo $mail['mail']; ?></td>
                      <td><?php echo $list['ListName']; ?></td>
                      <td><a href="<?php echo $site_url; ?>/Remove/<? echo $ID; ?>/<? echo $mail["ID"]; ?>" class="btn btn-danger btn-circle">
                        <i class="fas fa-trash"></i>
                    </a></td>
                    </tr>
                        <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
             </div>
             <!-- Content Row -->
         </div>
         <!-- /.container-fluid -->
     </div>
     <!-- End of Main Content -->