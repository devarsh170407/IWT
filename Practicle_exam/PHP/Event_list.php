<?php
$conn = new mysqli("localhost", "root", "", "event_db");

if(isset($_GET['delete'])){
    $conn->query("DELETE FROM events WHERE event_id=".$_GET['delete']);
}

if(isset($_POST['update'])){
    $conn->query("UPDATE events SET 
        title='".$_POST['title']."',
        description='".$_POST['desc']."',
        event_date='".$_POST['date']."',
        status='".$_POST['status']."',
        priority='".$_POST['priority']."'
        WHERE event_id=".$_POST['id']);
}

$res = $conn->query("SELECT * FROM events");
?>
    
<h3>Event List</h3>

<table border="1">
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Date</th>
    <th>Status</th>
    <th>Priority</th>
    <th>Edit</th>
    <th>Delete</th>
</tr>

<?php
while($row = $res->fetch_assoc()){
    echo "<tr>";
    echo "<td>".$row['event_id']."</td>";
    echo "<td>".$row['title']."</td>";
    echo "<td>".$row['event_date']."</td>";
    echo "<td>".$row['status']."</td>";
    echo "<td>".$row['priority']."</td>";
    echo "<td><a href='?edit=".$row['event_id']."'>Edit</a></td>";
    echo "<td><a href='?delete=".$row['event_id']."'>Delete</a></td>";
    echo "</tr>";
}
?>
</table>


<?php
if(isset($_GET['edit'])){
    $res = $conn->query("SELECT * FROM events WHERE event_id=".$_GET['edit']);
    $row = $res->fetch_assoc();
?>

<h3>Edit Event</h3>

<form method="POST">
    <input type="hidden" name="id" value="<?php echo $row['event_id']; ?>">

    <input type="text" name="title" value="<?php echo $row['title']; ?>"><br><br>

    <textarea name="desc"><?php echo $row['description']; ?></textarea><br><br>

    <input type="date" name="date" value="<?php echo $row['event_date']; ?>"><br><br>

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

    <button name="update">Update</button>
</form>

<?php } ?>