<title>Choose User - <?php echo $title; ?></title>
  <?php if($user['State'] != 1){
    header("Location: $site_url/login");
    return;
} ?>
  <!-- Begin Page Content -->
  <div class="container-fluid">

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Users</h1>
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
                <h6 class="m-0 font-weight-bold text-primary">Choose User To Edit</h6>
            </div>
            <div class="card-body">
            <select class="custom-select" onchange="javascript:location.href = this.value;">
                <option selected>Select User</option>
                <? 
						$getUsers = mysqli_query($conn,"SELECT * FROM users")or die(mysqli_error());
						while ($usr = mysqli_fetch_array($getUsers)) {
                ?>
                <option value="<? echo $site_url; ?>/user/<? echo $usr['UID']; ?>"><?php echo $usr['UserName']; ?></option>
                <?php } ?>
                </select>
                  
            </div>
        </div>
    </div>
    <!-- Content Row -->
</div>
<!-- /.container-fluid -->
</div>
<!-- End of Main Content -->