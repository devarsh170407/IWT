<!DOCTYPE html>
<html>
<head>
    <title>Online Clipboard</title>
</head>

<body>

<h2>Online Clipboard</h2>

<form method="post">
    <textarea name="msg" rows="5" cols="40" placeholder="Enter text here"></textarea><br><br>
    <input type="submit" name="save" value="Save Text">
</form>

<br>

<form method="post">
    Enter Code: <input type="number" name="code"><br><br>
    <input type="submit" name="get" value="Get Text">
</form>

<hr>

<?php

$conn = mysqli_connect("localhost", "root", "", "clipboard");

if (isset($_POST['save'])) {
    $msg = $_POST['msg'];
    $code = rand(1000,9999);

    mysqli_query($conn, "INSERT INTO data(code, text) VALUES('$code','$msg')");

    echo "Your Code is: <b>$code</b>";
}

if (isset($_POST['get'])) {
    $code = $_POST['code'];

    $res = mysqli_query($conn, "SELECT * FROM data WHERE code='$code'");

    if ($row = mysqli_fetch_assoc($res)) {
        echo "<h3>Saved Text:</h3>";
        echo $row['text'];
    } else {
        echo "Invalid Code!";
    }
}

?>

</body>
</html>