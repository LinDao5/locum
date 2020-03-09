<?php


   $list = $_GET['id'];
   $ID = $_GET['mail'];
   $success=mysqli_query($conn,"DELETE FROM mails WHERE ID='$ID' AND listid='$list'")or die(mysqli_error());
   if($success){
    header("Location: $site_url/list/$list/");
    }
    else{
        header("Location: $site_url");
    }

?>