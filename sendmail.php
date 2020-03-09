   <?php

$headers = 'From: <locumkit@fudugosolutions.com>' . "\r\n";

$text="hello";
$text = str_replace("\n.", "\n..", $text);
mail('suraj.work0126@hotmail.com','Leos Realm account verification!',$text,$headers);
?>