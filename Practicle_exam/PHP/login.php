<form method="POST">
    <input type="email" name="email" placeholder="Email"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <button name="login">Login</button>
</form>

<?php
session_start();
$conn = new mysqli("localhost", "root", "", "event_db");

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE email='$email'");
    $user = $res->fetch_assoc();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        header("Location: event.php"); 
        exit();

        } else {
        echo "invalid email or password";
    }
}
?>