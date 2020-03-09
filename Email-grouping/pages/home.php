<title>Dashboard - <?php echo $title; ?></title>
     <!-- Begin Page Content -->
     <div class="container-fluid">

         <!-- Page Heading -->
         <div class="d-sm-flex align-items-center justify-content-between mb-4">
             <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
             <a  href="#" data-toggle="modal" data-target="#logoutModal"
                 class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                     class="fas fa-sign-out-alt fa-sm text-white-50"></i> Logout</a>
         </div>
         <!-- Content Row -->
         <div class="row">
         <?php if($user['State'] == 1){ ?>
             <div class="col-lg-4 mb-4">
                 <a href="<?php echo $site_url; ?>/users">
                     <div class="card bg-primary text-white shadow">
                         <div class="card-body"  style="padding:30px 20px; font-size:20px">
                             Users
                         </div>
                     </div>
                 </a>
             </div>
         <?php } ?>
             <div class="col-lg-4 mb-4">
                 <a href="<?php echo $site_url; ?>/mailing">
                     <div class="card bg-success text-white shadow">
                     <div class="card-body"  style="padding:30px 20px; font-size:20px">
                             Send Mails
                         </div>
                     </div>
                 </a>
             </div>
             <?php if($user['State'] == 1){ ?>
             <div class="col-lg-4 mb-4">
                 <a href="<?php echo $site_url; ?>/mail-list">
                     <div class="card bg-info text-white shadow">
                     <div class="card-body"  style="padding:30px 20px; font-size:20px">
                             Mail List
                         </div>
                     </div>
                 </a>
             </div>
             <?php } ?>
             <!-- Content Row -->

         </div>
         <!-- /.container-fluid -->

     </div>
     <!-- End of Main Content -->