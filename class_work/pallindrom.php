<form method="post">
    <input type = "text" name = "myinput" placeholder="Enter you string"/>
    <input type = "submit" name = "btncheck" value="check"/>
</form>
<?php
    $data = $_POST["myinput"];
    $rev = strrev($data);
    if($data == $rev){
        echo "pallindrom String";
    }else{
        echo "Not Pallindrom";
    }
?>