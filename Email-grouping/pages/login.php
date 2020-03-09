<title>Mailing Dashboard Login - <?php echo $title; ?></title>
<? 
	if (isset($_POST['login']))
	{
		$UserName = $_POST['Username'];
		$Password = $_POST['Password'];
		$query = mysqli_query($conn,"SELECT * FROM `users` WHERE UserName='$UserName' and Password='$Password'")or die(mysqli_error());
		$count = mysqli_num_rows($query);
		$info = mysqli_fetch_assoc($query);
		if (empty($UserName))
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
            if($info['UserName'] != $UserName)
            {
              $user_error = ' <div class="alert alert-danger" role="alert">
			  <i class="fa fa-times"></i>
			  UserName Not Found.
			  </div> 
			  ';
              $error .='1';
            }
            if($info['Password'] != $Password)
            {
              $pass_error = ' <div class="alert alert-danger" role="alert">
			  <i class="fa fa-times"></i>
			  Password Not Correct
			  </div> 
			  ';
              $error .='1'; 
            }
		       
            if(empty($error))
            {		
                if ($count==1)
                {
                    $logued=1;
                    $_SESSION['acc'] = $UserName;
                    header("Location: $site_url");
                }
            }
	}
    ?>

<body class="bg-gradient-primary">
    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center" style="margin-top:6%;">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="bg-login-image" style="    height: 80%;
    width: 80%;
    margin: 0 auto; margin-top:40px;"></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user" method="post">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" name="Username"
                                                aria-describedby="emailHelp" placeholder="Enter Email Address...">                                    
                                        </div>
                                        <? echo $user_error; ?>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                name="Password" placeholder="Password">                                         
                                        </div>
                                        <? echo $pass_error; ?>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <button name="login" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <hr>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</body>