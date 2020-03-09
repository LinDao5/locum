<title>Add list - <?php echo $title; ?></title>
<?php
if($user['State'] != 1){
    header("Location: $site_url/login");
    return;
}
if (isset($_POST['addlist']))
{
    $getID = mysqli_query($conn,"SELECT * FROM maillist ORDER BY ID DESC");
	$lastID = mysqli_fetch_assoc($getID);
	$D = $lastID['ID'] + 1;
	$name = $_POST['ListName'];
    $getUser = mysqli_query($conn,"SELECT * FROM maillist WHERE ListName='$name'");
    $UserIf = mysqli_fetch_assoc($getUser);
    if (empty($name))
    {
        $name_error = '
        <div class="alert alert-danger" role="alert">
        <i class="tl-close"></i>
        This Field is Required.
        </div> 
        ';
        $error .='1'; 
        }
       if (!empty($UserIf))
        {
          $name_error = ' <div class="alert alert-danger" role="alert">
          <i class="fa fa-times"></i>
          Name Is Already Registed !
          </div> 
          ';
          $error .='1';
        }
        if(empty($error))
            {		
                $adduser = mysqli_query($conn,"insert into maillist (`ID`,`ListName`) 
                Values('$D' , '$name')") or die(mysqli_error($conn));
                $done =  '
                <div class="alert alert-success" role="alert">
                <i class="fa fa-check"></i>
                Added successfuly !
                </div> 
                ';
            }
	
}
?>
    <!-- Begin Page Content -->
     <div class="container-fluid">

         <!-- Page Heading -->
         <div class="d-sm-flex align-items-center justify-content-between mb-4">
             <h1 class="h3 mb-0 text-gray-800">Add Mail List</h1>
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
                         <h6 class="m-0 font-weight-bold text-primary">Add List</h6>
                     </div>
                     <div class="card-body">
                         <form class="user add-user" method="post">
                             <div class="form-group">
                                 <input type="text" class="form-control form-control-user" name="ListName"
                                     aria-describedby="emailHelp" placeholder="Enter List Name...">
                             </div>
                             <? echo $name_error; ?>
                             <button name="addlist" class="btn btn-primary btn-user btn-block">
                                 Add List
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