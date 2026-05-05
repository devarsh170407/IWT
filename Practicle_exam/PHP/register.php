<form method="POST">
    Username: <input type="text" name="username" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    Organization: <input type="text" name="org"><br><br>
    
    <button type="submit" name="register">Register</button>
</form>

<?php
$conn = new mysqli("localhost", "root", "", "event_db");

if(isset($_POST['register'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "invalid email";
    }
    elseif(strlen($password) < 8){
        echo "password length >= 8";
    }
    else{
        $password = password_hash($password, PASSWORD_DEFAULT);

        $conn->query("INSERT INTO users 
        (username, email, password, organization_name)
        VALUES 
        ('$_POST[username]', '$email', '$password', '$_POST[org]')");

        echo "registered successfully";
    }
}
?>
