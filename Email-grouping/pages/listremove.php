<?php
if($user['State'] != 1){
    header("Location: $site_url/login");
    return;
}
   $ID = $_GET['id'];
   $success=mysqli_query($conn,"DELETE FROM maillist WHERE ID='$ID'")or die(mysqli_error());
   if($success){
    header("Location: $site_url");
    }
?>