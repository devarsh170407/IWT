<?php
$change_bg = $_GET['change_bg'] ?? 'white';
$next_bg = $change_bg === 'white' ? 'black' : 'white';
?>
<html>
<body style="background:<?php echo htmlspecialchars($change_bg); ?>">
<a href="?change_bg=<?php echo $next_bg; ?>">Change Background Color</a>
</body>