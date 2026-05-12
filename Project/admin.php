<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'sanjivani_db');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'checkout') {
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($data['cart']) && isset($_SESSION['user_id'])) {
        $details = mysqli_real_escape_string($conn, json_encode($data['cart']));
        $pay  = mysqli_real_escape_string($conn, $data['payment'] ?? 'COD');
        $type = mysqli_real_escape_string($conn, $data['order_type'] ?? 'Dine-in');
        mysqli_query($conn, "INSERT INTO orders (user_id, customer_name, order_details, total_amount, payment_method, order_type, status) VALUES (".$_SESSION['user_id'].", '".$_SESSION['full_name']."', '$details', ".floatval($data['total']).", '$pay', '$type', 'Pending')");
        echo json_encode(["message" => "Order Placed Successfully!"]);
        exit;
    }
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php"); exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy(); header("Location: index.php"); exit;
}

$message = "";
if (isset($_POST["import_csv"])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if ($file) {
        $handle = fopen($file, "r"); fgetcsv($handle); $count = 0;
        while ($row = fgetcsv($handle)) {
            if (!empty($row[0])) {
                $name = mysqli_real_escape_string($conn, $row[0]);
                $desc = mysqli_real_escape_string($conn, $row[1] ?? '');
                $price = floatval($row[2] ?? 0);
                $img  = mysqli_real_escape_string($conn, $row[3] ?? '');
                $cat  = mysqli_real_escape_string($conn, $row[4] ?? 'Main Course');
                mysqli_query($conn, "INSERT INTO menu_items (name, description, price, image_url, category) VALUES ('$name', '$desc', $price, '$img', '$cat')");
                $count++;
            }
        }
        fclose($handle);
        $message = "<div style='color:#27ae60;background:#d1e7dd;padding:10px 16px;border-radius:8px;margin-bottom:14px;'>✅ Imported $count items!</div>";
    }
}
if (isset($_POST["add_menu_manual"])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $img  = mysqli_real_escape_string($conn, $_POST['image_url']);
    $cat  = mysqli_real_escape_string($conn, $_POST['category']);
    mysqli_query($conn, "INSERT INTO menu_items (name, description, price, image_url, category) VALUES ('$name', '$desc', $price, '$img', '$cat')");
    $message = "<div style='color:#27ae60;background:#d1e7dd;padding:10px 16px;border-radius:8px;margin-bottom:14px;'>✅ Item added!</div>";
}
if (isset($_POST["update_menu_manual"])) {
    $id   = intval($_POST['edit_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $img  = mysqli_real_escape_string($conn, $_POST['image_url']);
    $cat  = mysqli_real_escape_string($conn, $_POST['category']);
    mysqli_query($conn, "UPDATE menu_items SET name='$name', description='$desc', price=$price, image_url='$img', category='$cat' WHERE id=$id");
    $message = "<div style='color:#0a58ca;background:#cfe2ff;padding:10px 16px;border-radius:8px;margin-bottom:14px;'>✏️ Item updated!</div>";
}
if (isset($_GET['delete_menu']))  { mysqli_query($conn, "DELETE FROM menu_items WHERE id=".intval($_GET['delete_menu'])); header("Location: admin.php?view=menu"); exit; }
if (isset($_GET['archive_order'])){ mysqli_query($conn, "UPDATE orders SET status='Archived' WHERE id=".intval($_GET['archive_order'])); header("Location: admin.php?view=orders"); exit; }
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $st = mysqli_real_escape_string($conn, $_GET['update_status']);
    mysqli_query($conn, "UPDATE orders SET status='$st' WHERE id=".intval($_GET['id']));
    header("Location: admin.php?view=orders"); exit;
}

$view = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sanjivani Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body class="admin-body">

<!-- ADMIN NAVBAR -->
<nav class="admin-navbar">
    <h1>🍽️ Sanjivani <span>Admin</span></h1>
    <div class="admin-nav-actions">
        <a href="index.php" class="admin-nav-btn home">🏠 Home</a>
        <a href="admin.php?action=logout" class="admin-nav-btn logout">⏻ Logout</a>
    </div>
</nav>

<div class="admin-container">

    <!-- SIDEBAR — only shows extended menu after login (already guarded by PHP above) -->
    <aside class="sidebar">
        <div class="sidebar-section-label">Main</div>
        <ul class="sidebar-menu">
            <li><a href="admin.php?view=dashboard" class="<?= $view=='dashboard'?'active':'' ?>"><span class="menu-icon">📊</span> Dashboard</a></li>
        </ul>

        <div class="sidebar-section-label">Management</div>
        <ul class="sidebar-menu">
            <li><a href="admin.php?view=menu"     class="<?= $view=='menu'?'active':'' ?>"><span class="menu-icon">🍔</span> Manage Menu</a></li>
            <li><a href="admin.php?view=orders"   class="<?= $view=='orders'?'active':'' ?>"><span class="menu-icon">📦</span> Orders</a></li>
            <li><a href="admin.php?view=bookings" class="<?= $view=='bookings'?'active':'' ?>"><span class="menu-icon">📅</span> Table Bookings</a></li>
            <li><a href="admin.php?view=reviews"  class="<?= $view=='reviews'?'active':'' ?>"><span class="menu-icon">⭐</span> Reviews</a></li>
        </ul>

        <div class="sidebar-footer">Logged in as <strong style="color:#a0aec0"><?= htmlspecialchars($_SESSION['full_name']) ?></strong></div>
    </aside>

    <main class="main-content">

    <?php /* ─── DASHBOARD ─── */ if($view == 'dashboard') {
        $u   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='customer'"))['t'];
        $m   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM menu_items"))['t'];
        $o   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM orders"))['t'];
        $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as t FROM orders WHERE DATE(order_date)=CURDATE()"))['t'] ?? 0;
        $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM orders WHERE status='Pending'"))['t'];
    ?>
        <div class="page-header">
            <h2>Dashboard Overview</h2>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>! Here's what's happening today.</p>
        </div>

        <div class="stat-cards">
            <div class="stat-card green">
                <div class="stat-icon green">💰</div>
                <div class="stat-info"><h3>₹<?= number_format($rev,2) ?></h3><p>Today's Revenue</p></div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">👥</div>
                <div class="stat-info"><h3><?= $u ?></h3><p>Customers</p></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon orange">🍽️</div>
                <div class="stat-info"><h3><?= $m ?></h3><p>Menu Items</p></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon purple">📦</div>
                <div class="stat-info"><h3><?= $o ?></h3><p>Total Orders</p></div>
            </div>
        </div>

        <div class="dashboard-row">
            <!-- Recent Orders -->
            <div class="admin-panel-box">
                <h3>🕒 Recent Orders</h3>
                <table>
                    <thead><tr><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                    $recent = mysqli_query($conn, "SELECT * FROM orders WHERE status!='Archived' ORDER BY id DESC LIMIT 5");
                    if(mysqli_num_rows($recent)==0) echo "<tr><td colspan='3' style='color:#aaa;text-align:center'>No orders yet</td></tr>";
                    while($r = mysqli_fetch_assoc($recent)) {
                        $badges = ['Pending'=>'badge-pending','Preparing'=>'badge-preparing','Ready'=>'badge-ready','On the Way'=>'badge-onway','Completed'=>'badge-completed'];
                        $bc = $badges[$r['status']] ?? 'badge-pending';
                        echo "<tr><td>{$r['customer_name']}</td><td>₹{$r['total_amount']}</td><td><span class='badge $bc'>{$r['status']}</span></td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Reviews Preview -->
            <div class="admin-panel-box">
                <h3>⭐ Recent Customer Reviews</h3>
                <?php
                $rev_rows = mysqli_query($conn, "SELECT * FROM reviews ORDER BY id DESC LIMIT 4");
                if(mysqli_num_rows($rev_rows)==0) echo "<p style='color:#aaa;text-align:center;padding:1rem'>No reviews yet.</p>";
                while($rv = mysqli_fetch_assoc($rev_rows)) {
                    $stars = str_repeat('★', $rv['rating']) . str_repeat('☆', 5-$rv['rating']);
                    echo "<div style='padding:10px 0;border-bottom:1px solid #eef0f4;'>
                        <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:4px'>
                            <strong style='font-size:0.9rem'>" . htmlspecialchars($rv['name']) . "</strong>
                            <span style='color:#f39c12;font-size:0.9rem'>$stars</span>
                        </div>
                        <p style='color:#718096;font-size:0.82rem;margin:0'>" . htmlspecialchars(mb_substr($rv['message'],0,80)) . (strlen($rv['message'])>80?'…':'') . "</p>
                    </div>";
                }
                ?>
                <div style="margin-top:10px;text-align:right">
                    <a href="admin.php?view=reviews" style="font-size:0.82rem;color:var(--primary-color);text-decoration:none;font-weight:600">View All →</a>
                </div>
            </div>
        </div>

    <?php } /* ─── MENU ─── */ elseif($view == 'menu') {
        $edit_id=''; $edit_name=''; $edit_desc=''; $edit_price=''; $edit_img=''; $edit_cat='Main Course';
        if(isset($_GET['edit_menu'])) {
            $q = mysqli_query($conn, "SELECT * FROM menu_items WHERE id=".intval($_GET['edit_menu']));
            if($r = mysqli_fetch_assoc($q)) { $edit_id=$r['id']; $edit_name=$r['name']; $edit_desc=$r['description']; $edit_price=$r['price']; $edit_img=$r['image_url']; $edit_cat=$r['category']; }
        }
    ?>
        <div class="page-header"><h2>🍔 Manage Menu</h2><p>Add, edit or remove items from the menu.</p></div>
        <?= $message ?>

        <div class="admin-panel-box">
            <h3><?= $edit_id ? '✏️ Edit Item' : '➕ Add New Item' ?></h3>
            <form action="admin.php?view=menu" method="post" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #eef0f4;">
                <?php if($edit_id) echo "<input type='hidden' name='edit_id' value='$edit_id'>"; ?>
                <input class="admin-form-input" type="text"   name="name"        placeholder="Item Name"          value="<?= htmlspecialchars($edit_name) ?>"  required>
                <input class="admin-form-input" type="text"   name="description" placeholder="Description"        value="<?= htmlspecialchars($edit_desc) ?>">
                <select class="admin-form-input" name="category" required>
                    <?php foreach(['Starter','Main Course','Desserts','Drinks','Fast Food'] as $c) echo "<option value='$c'".($edit_cat==$c?' selected':'').">$c</option>"; ?>
                </select>
                <input class="admin-form-input" type="number" name="price"       placeholder="Price (₹)"          value="<?= htmlspecialchars($edit_price) ?>" required>
                <input class="admin-form-input" type="text"   name="image_url"   placeholder="Image URL (optional)"value="<?= htmlspecialchars($edit_img) ?>">
                <?php if($edit_id) { ?>
                    <button type="submit" name="update_menu_manual" class="btn" style="padding:0.75rem 1.4rem;flex:none">Update</button>
                    <a href="admin.php?view=menu" class="btn" style="padding:0.75rem 1.4rem;flex:none;background:#95a5a6;text-decoration:none">Cancel</a>
                <?php } else { ?>
                    <button type="submit" name="add_menu_manual" class="btn" style="padding:0.75rem 1.4rem;flex:none">+ Add Item</button>
                <?php } ?>
            </form>
            <h3>📂 Bulk CSV Upload</h3>
            <p style="font-size:0.8rem;color:#718096;margin-bottom:10px">Format: Name, Description, Price, ImageURL, Category</p>
            <form action="admin.php?view=menu" method="post" enctype="multipart/form-data" style="display:flex;align-items:center;gap:12px;">
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" name="import_csv" class="btn btn-small" style="padding:8px 16px">Import CSV</button>
            </form>
        </div>

        <div class="admin-panel-box">
            <h3>📋 All Menu Items</h3>
            <table>
                <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Price</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY id DESC");
                while($r = mysqli_fetch_assoc($res)) {
                    echo "<tr><td>#{$r['id']}</td><td>".htmlspecialchars($r['name'])."</td><td><span class='badge badge-preparing'>".htmlspecialchars($r['category'])."</span></td><td>₹{$r['price']}</td><td>
                    <a href='admin.php?view=menu&edit_menu={$r['id']}' class='btn-small' style='background:#dbeafe;color:#2563eb;margin-right:5px'>✏️ Edit</a>
                    <a href='admin.php?delete_menu={$r['id']}' class='btn-small btn-danger' onclick=\"return confirm('Delete this item?')\">🗑 Delete</a></td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    <?php } /* ─── ORDERS ─── */ elseif($view == 'orders') { ?>
        <div class="page-header"><h2>📦 Incoming Orders</h2><p>Manage and update order statuses.</p></div>
        <div class="admin-panel-box" style="overflow-x:auto">
            <table>
                <thead><tr><th>#</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $orders = mysqli_query($conn, "SELECT * FROM orders WHERE status != 'Archived' ORDER BY id DESC");
                while($r = mysqli_fetch_assoc($orders)) {
                    $items = "";
                    foreach(json_decode($r['order_details'], true) as $i) $items .= htmlspecialchars($i['name'])." (x{$i['qty']})<br>";
                    $status = $r['status'] ?? 'Pending';
                    $type   = $r['order_type'] ?? 'Dine-in';
                    $badges = ['Pending'=>'badge-pending','Preparing'=>'badge-preparing','Ready'=>'badge-ready','On the Way'=>'badge-onway','Completed'=>'badge-completed'];
                    $bc = $badges[$status] ?? 'badge-pending';

                    $action = "";
                    if($status=='Pending')    $action = "<a href='admin.php?update_status=Preparing&id={$r['id']}' class='btn-small' style='background:#dbeafe;color:#2563eb'>Prepare</a>";
                    elseif($status=='Preparing') {
                        $action = $type=='Home Delivery'
                            ? "<a href='admin.php?update_status=On%20the%20Way&id={$r['id']}' class='btn-small' style='background:#e9d5ff;color:#7c3aed'>Dispatch</a>"
                            : "<a href='admin.php?update_status=Ready&id={$r['id']}' class='btn-small' style='background:#fef3c7;color:#d97706'>Mark Ready</a>";
                    }
                    elseif($status=='Ready'||$status=='On the Way')
                        $action = "<a href='admin.php?update_status=Completed&id={$r['id']}' class='btn-small' style='background:#d1fae5;color:#059669'>Complete</a>";
                    else
                        $action = "<a href='admin.php?archive_order={$r['id']}' class='btn-small btn-danger'>Clear</a>";

                    echo "<tr><td>#{$r['id']}</td><td>{$r['customer_name']}</td><td>$items</td><td>₹{$r['total_amount']}</td><td>{$r['payment_method']}</td><td>{$type}</td><td><span class='badge $bc'>$status</span></td><td>$action</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    <?php } /* ─── BOOKINGS ─── */ elseif($view == 'bookings') { ?>
        <div class="page-header"><h2>📅 Table Bookings</h2><p>View all upcoming table reservations.</p></div>
        <div class="admin-panel-box" style="overflow-x:auto">
            <table>
                <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Date</th><th>Time</th><th>Guests</th><th>Booked On</th></tr></thead>
                <tbody>
                <?php
                $bookings = mysqli_query($conn, "SELECT * FROM table_bookings ORDER BY date ASC, time ASC");
                if(mysqli_num_rows($bookings)==0) echo "<tr><td colspan='7' style='text-align:center;color:#aaa;padding:2rem'>No bookings yet.</td></tr>";
                while($b = mysqli_fetch_assoc($bookings))
                    echo "<tr><td>#{$b['id']}</td><td>".htmlspecialchars($b['name'])."</td><td>".htmlspecialchars($b['phone']??"")."</td><td>{$b['date']}</td><td>{$b['time']}</td><td>👥 {$b['guests']}</td><td>{$b['created_at']}</td></tr>";
                ?>
                </tbody>
            </table>
        </div>

    <?php } /* ─── REVIEWS ─── */ elseif($view == 'reviews') {
        // Delete review
        if(isset($_GET['delete_review'])) {
            mysqli_query($conn, "DELETE FROM reviews WHERE id=".intval($_GET['delete_review']));
            header("Location: admin.php?view=reviews"); exit;
        }
        $all_reviews = mysqli_query($conn, "SELECT * FROM reviews ORDER BY id DESC");
        $avg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as a, COUNT(*) as c FROM reviews"));
    ?>
        <div class="page-header">
            <h2>⭐ Customer Reviews & Feedback</h2>
            <p>See what your customers are saying about Sanjivani.</p>
        </div>

        <!-- Summary Stats -->
        <div class="stat-cards" style="margin-bottom:2rem">
            <div class="stat-card green">
                <div class="stat-icon green">⭐</div>
                <div class="stat-info"><h3><?= $avg['a'] ? number_format($avg['a'],1) : '—' ?>/5</h3><p>Average Rating</p></div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">💬</div>
                <div class="stat-info"><h3><?= $avg['c'] ?></h3><p>Total Reviews</p></div>
            </div>
            <?php
            $dist = [];
            for($s=5;$s>=1;$s--) {
                $d = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM reviews WHERE rating=$s"));
                $dist[$s] = $d['c'];
            }
            ?>
            <div class="stat-card orange" style="grid-column:span 2;flex-direction:column;align-items:flex-start">
                <h3 style="font-size:0.85rem;margin-bottom:10px;color:#718096">RATING BREAKDOWN</h3>
                <?php for($s=5;$s>=1;$s--) {
                    $pct = $avg['c']>0 ? round($dist[$s]/$avg['c']*100) : 0;
                    echo "<div style='display:flex;align-items:center;gap:10px;margin-bottom:6px;width:100%'>
                        <span style='font-size:0.82rem;color:#f39c12;width:20px'>{'$s'}★</span>
                        <div style='flex:1;background:#eef0f4;border-radius:20px;height:8px;overflow:hidden'>
                            <div style='width:{$pct}%;background:linear-gradient(90deg,#f39c12,#e67e22);height:100%;border-radius:20px;transition:width 0.5s'></div>
                        </div>
                        <span style='font-size:0.78rem;color:#718096;width:25px'>{$dist[$s]}</span>
                    </div>";
                } ?>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="admin-panel-box">
            <h3>💬 All Reviews</h3>
            <table>
                <thead><tr><th>#</th><th>Name</th><th>Rating</th><th>Message</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                if(mysqli_num_rows($all_reviews)==0)
                    echo "<tr><td colspan='6' style='text-align:center;color:#aaa;padding:2rem'>No reviews submitted yet.</td></tr>";
                while($rv = mysqli_fetch_assoc($all_reviews)) {
                    $stars = str_repeat('★',$rv['rating']).str_repeat('☆',5-$rv['rating']);
                    echo "<tr>
                        <td>#{$rv['id']}</td>
                        <td><strong>".htmlspecialchars($rv['name'])."</strong><br><small style='color:#aaa'>".htmlspecialchars($rv['email']??'')."</small></td>
                        <td><span style='color:#f39c12'>$stars</span></td>
                        <td style='max-width:280px'>".htmlspecialchars($rv['message'])."</td>
                        <td style='white-space:nowrap'>{$rv['created_at']}</td>
                        <td><a href='admin.php?view=reviews&delete_review={$rv['id']}' class='btn-small btn-danger' onclick=\"return confirm('Delete this review?')\">🗑 Delete</a></td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    <?php } ?>

    </main>
</div>
</body>
</html>
