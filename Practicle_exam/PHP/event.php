<form method="POST">
    <input type="text" name="title" placeholder="Title"><br><br>
    <textarea name="desc"></textarea><br><br>
    <input type="date" name="date"><br><br>

    <select name="status">
        <option>upcoming</option>
        <option>ongoing</option>
        <option>completed</option>
        <option>cancelled</option>
    </select><br><br>

    <select name="priority">
        <option>low</option>
        <option>medium</option>
        <option>high</option>
    </select><br><br>

    <button name="add">Add Event</button>
</form>

<?php
session_start();
$conn = new mysqli("localhost", "root", "", "event_db");

// $_SESSION['user_id'] = 1;

if(isset($_POST['add'])){
    if(empty($_POST['title'])){
        echo "Title required";
    } else {
        $conn->query("INSERT INTO events
        (user_id, title, description, event_date, status, priority)
        VALUES
        ('{$_SESSION['user_id']}', '{$_POST['title']}', '{$_POST['desc']}', '{$_POST['date']}', '{$_POST['status']}', '{$_POST['priority']}')");

        echo "event added";
    }
}
?>

