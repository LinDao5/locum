<?php
if($user['State'] != 1){
    header("Location: $site_url/login");
    return;
}
$ID = $_GET['ID'];
$getpro = mysqli_query($conn,"SELECT * FROM users WHERE UID = '$ID'");
$user = mysqli_fetch_assoc($getpro);
if (isset($_POST['edituser']))
{
	$UserNew = $_POST['Username'];
    $Password = $_POST['Password'];
    $State = $_POST['state'];
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
       if ($State == 0)
        {
            $State_error = '
            <div class="alert alert-danger" role="alert">
            <i class="tl-close"></i>
            This Field is Required.
            </div> 
            ';
            $error .='1'; 
            }
        if(empty($error))
            {		
                $addpro = mysqli_query($conn,"UPDATE users SET UserName='$UserNew', Password='$Password' , State='$State' WHERE UID='$ID'")or die(mysqli_error($conn));
                $done =  '
                <div class="alert alert-success" role="alert">
                <i class="fa fa-check"></i>
                Updated successfuly !
                </div> 
                ';
            }
	
}
?>
<title><?php echo $user['UserName']; ?> - <?php echo $title; ?></title>
    <!-- Begin Page Content -->
     <div class="container-fluid">

         <!-- Page Heading -->
         <div class="d-sm-flex align-items-center justify-content-between mb-4">
             <h1 class="h3 mb-0 text-gray-800">Edit (<?php echo $user['UserName']; ?>) User</h1>
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
                         <h6 class="m-0 font-weight-bold text-primary"><?php echo $user['UserName']; ?></h6>
                     </div>
                     <div class="card-body">
                         <form class="user add-user" method="post">
                             <div class="form-group">
                                 <input type="text" class="form-control form-control-user" name="Username"
                                     aria-describedby="emailHelp" value="<?php echo $user['UserName']; ?>" placeholder="Enter Username...">
                             </div>
                             <? echo $user_error; ?>
                             <div class="form-group">
                                 <input type="password" class="form-control form-control-user" name="Password"
                                     placeholder="Password" value="<?php echo $user['Password']; ?>">
                             </div>
                             <? echo $pass_error; ?>
                             <div class="form-group">
                             <select class="custom-select" name="state" style="margin-bottom:40px;">
                                <option value="0"selected>Select User Staue</option>
                                <option value="2">Normal User</option>
                                <option value="1">Admin User</option>
                            </select>
                            </div>
                            <? echo $State_error; ?>
                             <button name="edituser" class="btn btn-primary btn-user btn-block">
                                 Update
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