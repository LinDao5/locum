<title>Choose User - <?php echo $title; ?></title>
    <?php 
if($user['State'] != 1){
    header("Location: $site_url/login");
    return;
}
    ?>
    <!-- Begin Page Content -->
     <div class="container-fluid">

         <!-- Page Heading -->
         <div class="d-sm-flex align-items-center justify-content-between mb-4">
             <h1 class="h3 mb-0 text-gray-800">Users</h1>
             <a  href="#" data-toggle="modal" data-target="#logoutModal"
                 class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                     class="fas fa-sign-out-alt fa-sm text-white-50"></i> Logout</a>
         </div>
         <!-- Content Row -->
         <div class="row">

             <div class="col-lg-6 mb-4">
             <a href="<?php echo $site_url; ?>/add-user">
                     <div class="card bg-primary text-white shadow">
                         <div class="card-body"  style="padding:40px 20px; font-size:20px">
                            Add User
                         </div>
                     </div>
                 </a>
             </div>
             <div class="col-lg-6 mb-4">
             <a href="<?php echo $site_url; ?>/edit-user">
                     <div class="card bg-success text-white shadow">
                     <div class="card-body"  style="padding:40px 20px; font-size:20px">
                             Edit User
                         </div>
                     </div>
                 </a>
             </div>
            

             <!-- Content Row -->

         </div>
         <!-- /.container-fluid -->

     </div>
     <!-- End of Main Content -->