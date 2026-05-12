<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'sanjivani_db');

if(isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php"); exit;
}

if(isset($_POST['register'])) {
    $fn = mysqli_real_escape_string($conn, $_POST['full_name']);
    $un = mysqli_real_escape_string($conn, $_POST['username']);
    $pw = mysqli_real_escape_string($conn, $_POST['password']);
    $ph = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $ad = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    mysqli_query($conn, "INSERT INTO users (full_name, username, password, phone, address) VALUES ('$fn','$un','$pw','$ph','$ad')");
    echo "<script>alert('Registered! Please login.'); window.location.href='index.php?view=login';</script>";
    exit;
}

if(isset($_POST['submit_review'])) {
    $uid    = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';
    $rname  = mysqli_real_escape_string($conn, $_POST['review_name']);
    $remail = mysqli_real_escape_string($conn, $_POST['review_email'] ?? '');
    $rating = max(1, min(5, intval($_POST['rating'])));
    $msg    = mysqli_real_escape_string($conn, $_POST['review_message']);
    if($rname && $msg) {
        mysqli_query($conn, "INSERT INTO reviews (user_id, name, email, rating, message) VALUES ($uid,'$rname','$remail',$rating,'$msg')");
        echo "<script>alert('Thank you for your feedback!'); window.location.href='index.php#feedback';</script>";
        exit;
    }
}

if(isset($_POST['book_table'])) {
    $uid    = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    $name   = mysqli_real_escape_string($conn, $_POST['name']);
    $phone  = mysqli_real_escape_string($conn, $_POST['phone']);
    $date   = mysqli_real_escape_string($conn, $_POST['date']);
    $time   = mysqli_real_escape_string($conn, $_POST['time']);
    $guests = intval($_POST['guests']);
    mysqli_query($conn, "INSERT INTO table_bookings (user_id, name, phone, date, time, guests) VALUES ($uid,'$name','$phone','$date','$time',$guests)");
    echo "<script>alert('Table Booked!'); window.location.href='index.php';</script>";
    exit;
}

if(isset($_POST['login'])) {
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='{$_POST['username']}' AND password='{$_POST['password']}'");
    if(mysqli_num_rows($q) > 0) {
        $u = mysqli_fetch_array($q);
        $_SESSION['user_id']   = $u['id'];
        $_SESSION['full_name'] = $u['full_name'];
        $_SESSION['role']      = $u['role'];
        header("Location: " . ($u['role'] == 'admin' ? 'admin.php' : 'index.php'));
    } else {
        echo "<script>alert('Invalid login!');</script>";
    }
}

$view = $_GET['view'] ?? 'home';
if(($view == 'login' || $view == 'register') && isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
if($view == 'orders' && !isset($_SESSION['user_id'])) { header("Location: index.php?view=login"); exit; }

$isUser  = isset($_SESSION['user_id']);
$isAdmin = $isUser && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanjivani Restaurant</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="nav-brand">
        <div class="nav-logo">S</div>
        <h1 class="nav-title">Sanjivani <span>Restaurant</span></h1>
    </a>
    <div class="nav-links">
        <?php if($view != 'login' && $view != 'register'): ?>
            <a href="index.php"              class="nav-link <?= $view=='home'  ?'active-link':'' ?>">Home</a>
            <a href="index.php?view=menu"    class="nav-link <?= $view=='menu'  ?'active-link':'' ?>">Menu</a>
            <a href="index.php?view=book"    class="nav-link <?= $view=='book'  ?'active-link':'' ?>">Book a Table</a>
            <a href="index.php#feedback"     class="nav-link">Feedback</a>
        <?php endif; ?>
        <div class="nav-auth">
            <?php if($isUser && !$isAdmin): ?>
                <button class="cart-btn" onclick="openCart()" id="cartNavBtn" title="Your Cart">
                    🛒 <span class="cart-badge" id="cartBadge">0</span>
                </button>
            <?php endif; ?>
            <?php if($isUser): ?>
                <?php if($isAdmin): ?>
                    <a href="admin.php" class="btn-outline">Dashboard</a>
                <?php else: ?>
                    <a href="index.php?view=orders" class="btn-outline <?= $view=='orders'?'active':'' ?>">My Orders</a>
                <?php endif; ?>
                <a href="index.php?action=logout" class="btn-solid logout-btn">Logout</a>
            <?php else: ?>
                <a href="index.php?view=login" class="btn-solid">Login / Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- CART DRAWER (only for logged-in customers) -->
<?php if($isUser && !$isAdmin): ?>
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer-header">
        <h3>🛒 Your Cart</h3>
        <button class="cart-close-btn" onclick="closeCart()">✕</button>
    </div>
    <div class="cart-drawer-body">
        <div class="cart-empty-state" id="cartEmpty">
            <span class="empty-icon">🛒</span>
            <p>Your cart is empty.<br>Add items from the menu!</p>
            <a href="index.php?view=menu" class="btn-solid cart-browse-btn">Browse Menu</a>
        </div>
        <div id="cartItemsList" style="display:none"></div>
    </div>
    <div class="cart-drawer-footer" id="cartFooter" style="display:none">
        <div class="cart-total-row">
            <span>Total</span>
            <span id="drawerTotal" class="cart-total-amount">₹0</span>
        </div>
        <div class="cart-section-label">Order Type</div>
        <div class="cart-options-grid">
            <label class="cart-option-card selected" onclick="selectOption('order_type','Dine-in',this)">
                <input type="radio" name="order_type" value="Dine-in" checked> 🍽️ Dine-in
            </label>
            <label class="cart-option-card" onclick="selectOption('order_type','Home Delivery',this)">
                <input type="radio" name="order_type" value="Home Delivery"> 🚚 Delivery
            </label>
        </div>
        <div class="cart-section-label">Payment Method</div>
        <div class="cart-options-grid">
            <label class="cart-option-card selected" onclick="selectOption('payment_method','UPI',this); document.getElementById('drawerQR').style.display='block'">
                <input type="radio" name="payment_method" value="UPI" checked> 📱 UPI
            </label>
            <label class="cart-option-card" onclick="selectOption('payment_method','COD',this); document.getElementById('drawerQR').style.display='none'">
                <input type="radio" name="payment_method" value="COD"> 💵 COD
            </label>
        </div>
        <div class="qr-box" id="drawerQR">
            <img id="drawerQRImg" src="" alt="UPI QR">
            <p>Scan to pay ₹<span id="drawerQRAmount">0</span></p>
        </div>
        <button class="checkout-btn" onclick="submitOrder()">✅ Checkout & Pay</button>
        <button class="clear-cart-btn" onclick="clearCart()">🗑️ Clear Cart</button>
    </div>
</div>
<?php endif; ?>

<!-- LOGIN / REGISTER -->
<?php if($view == 'login' || $view == 'register'): ?>

<div class="auth-section-wrapper">
    <div class="auth-card-v2">
        <div class="auth-visual-side">
            <div class="auth-visual-content">
                <h2>Welcome to<br>Sanjivani</h2>
                <p>Experience the finest traditional flavors and premium hospitality in every bite.</p>
            </div>
        </div>
        
        <div class="auth-form-side">
            <!-- LOGIN FORM -->
            <div id="loginForm" <?= $view != 'login' ? 'style="display:none"' : '' ?>>
                <h3>Welcome Back</h3>
                <p class="subtitle">Please enter your credentials to access your account.</p>
                <form method="POST">
                    <div class="auth-input-group">
                        <label>Username</label>
                        <input type="text" name="username" class="auth-input" placeholder="Enter your username" required>
                    </div>
                    <div class="auth-input-group">
                        <label>Password</label>
                        <input type="password" name="password" class="auth-input" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" name="login" class="auth-btn">Login to Account</button>
                    <p class="auth-footer">New User? <a href="index.php?view=register">Create an Account</a></p>
                </form>
            </div>

            <!-- REGISTER FORM -->
            <div id="registerForm" <?= $view != 'register' ? 'style="display:none"' : '' ?>>
                <h3>Create Account</h3>
                <p class="subtitle">Join us to order delicious meals and track your history.</p>
                <form method="POST">
                    <div class="auth-input-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="auth-input" placeholder="Your Full Name" required>
                    </div>
                    <div class="auth-input-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="auth-input" placeholder="Username" required>
                    </div>
                    <div class="auth-input-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="auth-input" placeholder="Password" required>
                    </div>
                    <div class="auth-input-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="auth-input" placeholder="Phone Number">
                    </div>
                    <div class="auth-input-group">
                        <label>Delivery Address</label>
                        <textarea name="address" rows="2" class="auth-input" placeholder="Address" style="resize:none"></textarea>
                    </div>
                    <button type="submit" name="register" class="auth-btn">🚀 Register Now</button>
                    <p class="auth-footer">Already have an account? <a href="index.php?view=login">Login Here</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MY ORDERS -->
<?php elseif($view == 'orders'): ?>

<div class="section active section-pt">
    <h2 class="page-title">My Order History</h2>
    <div class="orders-wrap">
        <table class="orders-table">
            <tr>
                <th>Order ID</th><th>Items</th><th>Type</th><th>Total</th><th>Status</th>
            </tr>
            <?php
            $my_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id={$_SESSION['user_id']} ORDER BY id DESC");
            if(mysqli_num_rows($my_orders) > 0):
                while($o = mysqli_fetch_assoc($my_orders)):
                    $items = '';
                    foreach(json_decode($o['order_details'], true) as $i)
                        $items .= "{$i['name']} (x{$i['qty']})<br>";
                    $colors = ['Completed'=>'green','Ready'=>'#e67e22','Preparing'=>'#3498db','On the Way'=>'#8e44ad'];
                    $sc = $colors[$o['status']] ?? '#333';
            ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= $items ?></td>
                <td><?= $o['order_type'] ?></td>
                <td>₹<?= $o['total_amount'] ?></td>
                <td style="font-weight:bold;color:<?= $sc ?>"><?= $o['status'] ?></td>
            </tr>
            <?php if($o['status'] == 'On the Way'): ?>
            <tr>
                <td colspan="5" class="rider-track-row">
                    <div style="display:flex;align-items:stretch;flex-wrap:wrap">
                        <!-- Animated Map -->
                        <div class="rider-map-panel">
                            <div class="rider-map-grid"></div>
                            <svg style="position:absolute;inset:0;width:100%;height:100%" viewBox="0 0 400 185" preserveAspectRatio="none">
                                <line x1="0" y1="65" x2="400" y2="65" stroke="rgba(255,255,255,0.07)" stroke-width="12"/>
                                <line x1="0" y1="135" x2="400" y2="135" stroke="rgba(255,255,255,0.07)" stroke-width="10"/>
                                <line x1="110" y1="0" x2="110" y2="185" stroke="rgba(255,255,255,0.07)" stroke-width="10"/>
                                <line x1="280" y1="0" x2="280" y2="185" stroke="rgba(255,255,255,0.07)" stroke-width="10"/>
                                <polyline points="110,135 110,65 280,65 280,38 350,38" stroke="#f39c12" stroke-width="2.5" fill="none" stroke-dasharray="10 6" stroke-linecap="round" style="animation:dashAnim 1.2s linear infinite"/>
                                <circle cx="110" cy="135" r="8" fill="#3498db" stroke="white" stroke-width="2"/>
                                <text x="110" y="160" text-anchor="middle" fill="#7fb3d3" font-size="9" font-family="sans-serif">Restaurant</text>
                                <circle cx="350" cy="38" r="8" fill="#2ecc71" stroke="white" stroke-width="2"/>
                                <text x="350" y="24" text-anchor="middle" fill="#7dcea0" font-size="9" font-family="sans-serif">You</text>
                            </svg>
                            <!-- Rider pin -->
                            <div style="position:absolute;left:46%;top:35%;transform:translate(-50%,-50%);animation:riderBounce 2s ease-in-out infinite">
                                <div style="position:relative;display:flex;flex-direction:column;align-items:center">
                                    <div class="rider-ripple"></div>
                                    <div class="rider-icon-wrap"><span>🛵</span></div>
                                    <div class="rider-shadow"></div>
                                </div>
                            </div>
                            <div class="rider-badge-left"><span class="rider-dot"></span> Rider On the Way</div>
                            <div class="rider-badge-right">⏱ ~15 min</div>
                        </div>
                        <!-- Info -->
                        <div class="rider-info-panel">
                            <h4>🛵 Your Order is On the Way!</h4>
                            <p>Your delivery rider is heading to your location. Keep your phone nearby.</p>
                            <div class="rider-eta">⏱ Estimated Arrival: 15–20 Mins</div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php endwhile; else: ?>
            <tr><td colspan="5" class="no-orders">No orders found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- BOOK A TABLE -->
<?php elseif($view == 'book'): ?>

<div class="booking-section-wrapper">
    <div class="booking-card-v2">
        <div class="booking-visual-side">
            <div class="booking-visual-content">
                <span class="badge">Fine Dining</span>
                <h2>Reserve Your<br>Preferred Table</h2>
                <p>Join us for an exceptional culinary journey. Secure your spot and experience the authentic flavors of Sanjivani.</p>
            </div>
        </div>
        <div class="booking-form-side">
            <h3>Book a Table</h3>
            <p class="subtitle">Please fill in the details below to confirm your reservation.</p>
            
            <form method="POST">
                <div class="booking-grid">
                    <div class="booking-input-group booking-full-w">
                        <label>Your Full Name</label>
                        <input type="text" name="name" class="booking-input" value="<?= $_SESSION['full_name'] ?? '' ?>" placeholder="Full Name" required>
                    </div>
                    <div class="booking-input-group booking-full-w">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="booking-input" placeholder="Phone Number" required>
                    </div>
                    <div class="booking-input-group">
                        <label>Date</label>
                        <input type="date" name="date" class="booking-input" required>
                    </div>
                    <div class="booking-input-group">
                        <label>Preferred Time</label>
                        <input type="time" name="time" class="booking-input" required>
                    </div>
                    <div class="booking-input-group booking-full-w">
                        <label>Number of Guests</label>
                        <input type="number" name="guests" class="booking-input" min="1" max="20" placeholder="Number of Guests" required>
                    </div>
                </div>
                
                <button type="submit" name="book_table" class="booking-btn">✨ Confirm Reservation</button>
            </form>
        </div>
    </div>
</div>

<!-- MENU PAGE -->
<?php elseif($view == 'menu'): ?>

<div class="section active section-pt">
    <h2 id="menu" class="page-title">Our Full Menu</h2>
    <div class="menu-categories-wrapper">
    <?php
    $categories = ['Starter','Main Course','Desserts','Drinks','Fast Food'];
    $has_items  = false;

    foreach($categories as $cat):
        $res = mysqli_query($conn, "SELECT * FROM menu_items WHERE category='$cat' ORDER BY id ASC");
        if(mysqli_num_rows($res) == 0) continue;
        $has_items = true;
    ?>
        <div class="category-section">
            <h3 class="cat-title"><?= $cat ?></h3>
            <div class="menu-grid-fixed">
            <?php while($item = mysqli_fetch_assoc($res)): ?>
                <?php
                $img = !empty($item['image_url']) ? $item['image_url']
                     : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80';
                ?>
                <div class="mcard">
                    <div class="mcard-img-wrap">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="mcard-img">
                        <span class="mcard-cat"><?= $cat ?></span>
                    </div>
                    <div class="mcard-body">
                        <h3 class="mcard-name"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="mcard-desc"><?= htmlspecialchars($item['description']) ?></p>
                        <div class="mcard-footer">
                            <span class="mcard-price">₹<?= $item['price'] ?></span>
                            <?php if($isUser): ?>
                                <button class="menu-add-btn" onclick="addToOrderDirect('<?= addslashes($item['name']) ?>')">🛒 Add to Cart</button>
                            <?php else: ?>
                                <a href="index.php?view=login" class="menu-add-btn">Login to Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php
    // Uncategorized items
    $res = mysqli_query($conn, "SELECT * FROM menu_items WHERE category IS NULL OR category NOT IN ('Starter','Main Course','Desserts','Drinks','Fast Food') ORDER BY id ASC");
    if(mysqli_num_rows($res) > 0):
        $has_items = true;
    ?>
        <div class="category-section">
            <h3 class="cat-title">Other Specials</h3>
            <div class="menu-grid-fixed">
            <?php while($item = mysqli_fetch_assoc($res)): ?>
                <?php $img = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80'; ?>
                <div class="mcard">
                    <div class="mcard-img-wrap">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="mcard-img">
                        <span class="mcard-cat">Special</span>
                    </div>
                    <div class="mcard-body">
                        <h3 class="mcard-name"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="mcard-desc"><?= htmlspecialchars($item['description']) ?></p>
                        <div class="mcard-footer">
                            <span class="mcard-price">₹<?= $item['price'] ?></span>
                            <?php if($isUser): ?>
                                <button class="menu-add-btn" onclick="addToOrderDirect('<?= addslashes($item['name']) ?>')">🛒 Add to Cart</button>
                            <?php else: ?>
                                <a href="index.php?view=login" class="menu-add-btn">Login to Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if(!$has_items): ?>
        <p>No menu items yet. Ask the admin to add items.</p>
    <?php endif; ?>
    </div>

    <?php if($isUser && !$isAdmin): ?>
    <div class="text-center mt-3">
        <button onclick="openCart()" class="btn view-cart-btn">
            🛒 View Cart <span class="cart-count-pill" id="cartCountBtn">0</span>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Cart JS (only on menu page) -->
<script>
let cart = [];
let prices = { <?php
    $res = mysqli_query($conn, "SELECT name, price FROM menu_items");
    $p = [];
    while($r = mysqli_fetch_assoc($res)) $p[] = "'" . addslashes($r['name']) . "':" . $r['price'];
    echo implode(',', $p);
?> };

function addToOrderDirect(name) {
    let item = cart.find(i => i.name === name);
    if(item) item.quantity++;
    else cart.push({ name, quantity: 1 });
    renderDrawer();
    openCart();
}

function removeFromOrder(name) {
    let item = cart.find(i => i.name === name);
    if(item) { item.quantity--; if(item.quantity <= 0) cart = cart.filter(i => i.name !== name); }
    renderDrawer();
}

function addQty(name) {
    let item = cart.find(i => i.name === name);
    if(item) item.quantity++;
    renderDrawer();
}

function renderDrawer() {
    let total = 0, count = 0, html = '';
    cart.forEach(item => {
        let sub = prices[item.name] * item.quantity;
        total += sub; count += item.quantity;
        html += `<div class="cart-item-card">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">₹${prices[item.name]} each</div>
            </div>
            <div class="cart-item-controls">
                <button class="qty-btn qty-minus" onclick="removeFromOrder('${item.name}')">−</button>
                <span class="qty-num">${item.quantity}</span>
                <button class="qty-btn qty-plus" onclick="addQty('${item.name}')">+</button>
                <span class="cart-item-sub">₹${sub}</span>
            </div>
        </div>`;
    });

    const empty = cart.length === 0;
    document.getElementById('cartEmpty').style.display    = empty ? 'block' : 'none';
    document.getElementById('cartItemsList').style.display = empty ? 'none' : 'block';
    document.getElementById('cartItemsList').innerHTML     = html;
    document.getElementById('cartFooter').style.display   = empty ? 'none' : 'block';
    document.getElementById('drawerTotal').innerHTML      = '₹' + total;
    document.getElementById('drawerQRAmount').textContent = total;
    document.getElementById('drawerQRImg').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=devarshbhatt1747@oksbi%26pn=Sanjivani%26am=${total}`;

    const badge    = document.getElementById('cartBadge');
    const countBtn = document.getElementById('cartCountBtn');
    if(badge)    { badge.textContent = count; badge.classList.toggle('show', count > 0); }
    if(countBtn) countBtn.textContent = count;
}

function openCart()  { document.getElementById('cartOverlay').classList.add('open'); document.getElementById('cartDrawer').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeCart() { document.getElementById('cartOverlay').classList.remove('open'); document.getElementById('cartDrawer').classList.remove('open'); document.body.style.overflow = ''; }
function clearCart() { cart = []; renderDrawer(); }

let selectedOrderType = 'Dine-in', selectedPayment = 'UPI';
function selectOption(group, val, el) {
    if(group === 'order_type') selectedOrderType = val;
    if(group === 'payment_method') selectedPayment = val;
    el.closest('.cart-options-grid').querySelectorAll('.cart-option-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}

function submitOrder() {
    let total = cart.reduce((s, i) => s + prices[i.name] * i.quantity, 0);
    fetch('admin.php?action=checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart: cart.map(i => ({ name: i.name, qty: i.quantity, total: prices[i.name] * i.quantity })), total, payment: selectedPayment, order_type: selectedOrderType })
    }).then(r => r.json()).then(d => { alert(d.message); cart = []; renderDrawer(); closeCart(); location.reload(); });
}

document.addEventListener('keydown', e => { if(e.key === 'Escape') closeCart(); });
</script>

<!-- HOME PAGE -->
<?php else: ?>

<!-- Hero -->
<div class="home-hero">
    <div class="hero-inner">
        <div class="hero-badge"><span></span> Open Now — Serving 10AM to 11PM</div>
        <h1 class="hero-h1">Authentic Taste,<br><em>Unforgettable</em> Experience</h1>
        <p class="hero-sub">From traditional Indian delicacies to fast delivery at your door — Sanjivani brings generations of flavor straight to your table.</p>
        <div class="hero-actions">
            <a href="index.php?view=menu" class="hero-cta primary">🍽️ Order Online</a>
            <a href="index.php?view=book" class="hero-cta outline">📅 Book a Table</a>
        </div>
    </div>
</div>

<!-- Stats Bar -->
<div class="stats-bar">
    <?php
    $total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders"))['c'] ?? 0;
    $avg_rating   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(rating),1) a FROM reviews"))['a'] ?? '5.0';
    ?>
    <div class="stat-item"><div class="stat-num"><?= number_format($total_orders + 1200) ?>+</div><div class="stat-lbl">Happy Orders</div></div>
    <div class="stat-item"><div class="stat-num">100%</div><div class="stat-lbl">Pure Veg</div></div>
    <div class="stat-item"><div class="stat-num"><?= $avg_rating ?: '5.0' ?>⭐</div><div class="stat-lbl">Avg Rating</div></div>
    <div class="stat-item"><div class="stat-num">15 min</div><div class="stat-lbl">Avg Delivery</div></div>
</div>

<!-- Why Us -->
<div class="home-section grey">
    <div class="section-head">
        <div class="section-eyebrow">Why Sanjivani</div>
        <h2 class="section-title">We Cook with <span>Love & Tradition</span></h2>
        <p class="section-sub">Every dish tells a story rooted in authentic recipes passed down through generations.</p>
    </div>
    <div class="why-grid">
        <div class="why-card"><div class="why-icon">🌿</div><h3>Fresh Ingredients</h3><p>Locally sourced vegetables and hand-picked spices every morning.</p></div>
        <div class="why-card"><div class="why-icon">👨‍🍳</div><h3>Expert Chefs</h3><p>Masters of traditional culinary arts who pour passion into every plate.</p></div>
        <div class="why-card"><div class="why-icon">🚀</div><h3>Fast Delivery</h3><p>Hot, fresh meals at your doorstep in 15 minutes or less — guaranteed.</p></div>
        <div class="why-card"><div class="why-icon">💳</div><h3>Easy Payment</h3><p>Pay via UPI QR or Cash on Delivery — your comfort, your choice.</p></div>
    </div>
</div>

<!-- Featured Dishes -->
<div class="home-section white">
    <div class="section-head">
        <div class="section-eyebrow">From Our Kitchen</div>
        <h2 class="section-title">Featured <span>Delicacies</span></h2>
    </div>
    <div class="home-menu-grid">
    <?php
    $feat = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY RAND() LIMIT 3");
    while($item = mysqli_fetch_assoc($feat)):
        $img = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80';
    ?>
        <div class="home-menu-card">
            <div class="img-wrap">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <span class="card-cat-tag"><?= htmlspecialchars($item['category']) ?></span>
            </div>
            <div class="card-body">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><?= htmlspecialchars($item['description']) ?></p>
                <div class="card-footer">
                    <span class="price">₹<?= $item['price'] ?></span>
                    <a href="index.php?view=menu" class="order-now-btn">Order Now →</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
    <div style="display: flex; justify-content: center; margin-top: 2.5rem; margin-bottom: 1rem;">
        <a href="index.php?view=menu" class="hero-cta primary">🍴 View Full Menu</a>
    </div>
</div>

<!-- Customer Reviews -->
<div class="home-section grey">
    <div class="section-head">
        <div class="section-eyebrow">Testimonials</div>
        <h2 class="section-title">What Our <span>Customers Say</span></h2>
    </div>
    <div class="reviews-grid">
    <?php
    $pub    = mysqli_query($conn, "SELECT * FROM reviews ORDER BY id DESC LIMIT 6");
    $clrs   = ['#f39c12','#3498db','#2ecc71','#e74c3c','#9b59b6','#1abc9c'];
    $static = [
        ['name'=>'Rajesh Kumar','init'=>'RK','rating'=>5,'msg'=>'Fantastic flavors and lightning-fast delivery. Highly recommended!','color'=>'#f39c12'],
        ['name'=>'Priya Sharma','init'=>'PS','rating'=>5,'msg'=>'Seamless booking and great ambiance. Paneer Butter Masala is a must-try!','color'=>'#3498db'],
        ['name'=>'Amit Patel',  'init'=>'AP','rating'=>5,'msg'=>'Best dessert selection in town. Easy ordering system — solid 10/10.','color'=>'#2ecc71'],
    ];

    if(mysqli_num_rows($pub) > 0):
        while($rv = mysqli_fetch_assoc($pub)):
            $stars = str_repeat('★', $rv['rating']) . str_repeat('☆', 5 - $rv['rating']);
            $init  = strtoupper(substr($rv['name'], 0, 1)) . (strpos($rv['name'],' ') !== false ? strtoupper(substr(strrchr($rv['name'],' '), 1, 1)) : '');
            $color = $clrs[$rv['id'] % count($clrs)];
    ?>
        <div class="review-card">
            <div class="review-stars"><?= $stars ?></div>
            <p class="review-msg">"<?= htmlspecialchars($rv['message']) ?>"</p>
            <div class="review-author">
                <div class="review-avatar" style="background:<?= $color ?>"><?= $init ?></div>
                <div>
                    <div class="review-name"><?= htmlspecialchars($rv['name']) ?></div>
                    <div class="review-email"><?= htmlspecialchars($rv['email'] ?? 'Valued Customer') ?></div>
                </div>
            </div>
        </div>
    <?php endwhile; else:
        foreach($static as $s):
            $stars = str_repeat('★', $s['rating']);
    ?>
        <div class="review-card">
            <div class="review-stars"><?= $stars ?></div>
            <p class="review-msg">"<?= $s['msg'] ?>"</p>
            <div class="review-author">
                <div class="review-avatar" style="background:<?= $s['color'] ?>"><?= $s['init'] ?></div>
                <div class="review-name"><?= $s['name'] ?></div>
            </div>
        </div>
    <?php endforeach; endif; ?>
    </div>
</div>

<!-- Feedback Form -->
<div id="feedback" class="home-section-dark">
    <div class="feedback-wrap">
        <div class="section-head">
            <div class="section-eyebrow" style="color:rgba(243,156,18,0.7)">Share Your Experience</div>
            <h2 class="section-title light">We'd Love to <span>Hear from You</span></h2>
            <p class="section-sub light">Your feedback helps us serve you better every day.</p>
        </div>
        <div class="feedback-box">
            <form method="POST" action="index.php#feedback">
                <div class="feedback-row">
                    <div class="feedback-col">
                        <label class="fb-label">Your Name *</label>
                        <input type="text" name="review_name" class="fb-input" placeholder="Your Name" required
                                value="<?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : '' ?>">
                    </div>
                    <div class="feedback-col">
                        <label class="fb-label">Email (optional)</label>
                        <input type="email" name="review_email" class="fb-input" placeholder="Email Address">
                    </div>
                </div>
                <div style="margin-bottom:1.2rem">
                    <label class="fb-label">Your Rating *</label>
                    <div class="star-rating" id="starRating">
                        <?php for($i=1;$i<=5;$i++) echo "<span class='star' onclick='setRating($i)' onmouseover='hoverRating($i)' onmouseout='resetHover()'>&#9733;</span>"; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="5">
                </div>
                <div style="margin-bottom:1.4rem">
                    <label class="fb-label">Your Message *</label>
                    <textarea name="review_message" rows="4" class="fb-input" placeholder="Tell us about your experience..." required></textarea>
                </div>
                <button type="submit" name="submit_review" class="fb-submit">✉️ Submit Feedback</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.star').forEach((s, i) => s.classList.toggle('active', i < 5));
function setRating(v) {
    document.getElementById('ratingInput').value = v;
    document.querySelectorAll('.star').forEach((s, i) => { s.classList.toggle('active', i < v); s.classList.remove('hover'); });
}
function hoverRating(v) { document.querySelectorAll('.star').forEach((s, i) => s.classList.toggle('hover', i < v)); }
function resetHover()   { document.querySelectorAll('.star').forEach(s => s.classList.remove('hover')); }
</script>

<?php endif; ?>
</body>
</html>
