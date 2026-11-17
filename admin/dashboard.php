
<?php
session_start();
require_once "../config/database.php";

// Admin access protection
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Dashboard statistics
$totalRooms = 0;
$totalBookings = 0;
$pendingBookings = 0;
$totalGuests = 0;
$confirmedBookings = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM rooms");
if ($result) {
    $totalRooms = $result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM bookings");
if ($result) {
    $totalBookings = $result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'pending'");
if ($result) {
    $pendingBookings = $result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'guest'");
if ($result) {
    $totalGuests = $result->fetch_assoc()["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'confirmed'");
if ($result) {
    $confirmedBookings = $result->fetch_assoc()["total"];
}

// Recent bookings
$recentBookings = $conn->query("
    SELECT b.booking_id, b.check_in, b.check_out,
           b.guests, b.total_price, b.status,
           u.full_name, r.room_name
    FROM bookings b
    INNER JOIN users u ON b.user_id = u.user_id
    INNER JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.created_at DESC
    LIMIT 6
");

$adminName = htmlspecialchars($_SESSION["full_name"] ?? "Administrator");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard | Ocean Pearl Villa</title>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: #f3f7f7;
    color: #263b3d;
}

.sidebar {
    position: fixed;
    width: 255px;
    height: 100vh;
    background: #073b40;
    color: white;
    padding: 30px 20px;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    color: #e5c58b;
    margin-bottom: 8px;
}

.logo-sub {
    font-size: 11px;
    letter-spacing: 2px;
    color: #b9d0cf;
    margin-bottom: 45px;
}

.menu-title {
    font-size: 11px;
    color: #8caead;
    letter-spacing: 2px;
    margin: 25px 10px 12px;
}

.sidebar a {
    display: block;
    text-decoration: none;
    color: #d2e1df;
    padding: 13px 15px;
    border-radius: 9px;
    margin-bottom: 8px;
    font-size: 14px;
    transition: 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
    background: #155b5c;
    color: #fff;
}

.main {
    margin-left: 255px;
    padding: 35px 40px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    gap: 15px;
}

.topbar h1 {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    color: #073b40;
}

.welcome {
    color: #718384;
    font-size: 13px;
    margin-top: 6px;
}

.admin-info {
    background: white;
    padding: 12px 18px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.04);
    font-size: 14px;
}

.admin-info span {
    color: #b18a4c;
    font-weight: 700;
}

.section-heading {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: #073b40;
    margin-bottom: 20px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 35px;
}

.stat-card {
    background: white;
    padding: 25px 22px;
    border-radius: 15px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    border-bottom: 3px solid #d7b77c;
}

.stat-label {
    color: #809091;
    font-size: 13px;
    margin-bottom: 15px;
}

.stat-number {
    font-size: 30px;
    font-weight: 700;
    color: #073b40;
}

.stat-icon {
    font-size: 23px;
    margin-bottom: 12px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 35px;
}

.action-card {
    background: white;
    border-radius: 14px;
    padding: 22px;
    text-decoration: none;
    color: #073b40;
    border: 1px solid #e7eeee;
    transition: 0.3s;
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 22px rgba(0,0,0,0.07);
}

.action-card h3 {
    font-size: 16px;
    margin: 10px 0 6px;
}

.action-card p {
    color: #819091;
    font-size: 12px;
}

.action-icon {
    font-size: 25px;
}

.table-container {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

th {
    text-align: left;
    padding: 15px 12px;
    color: #849394;
    font-size: 11px;
    letter-spacing: 1px;
    border-bottom: 1px solid #edf1f1;
}

td {
    padding: 16px 12px;
    font-size: 13px;
    border-bottom: 1px solid #f0f3f3;
}

.status {
    display: inline-block;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
}

.pending {
    background: #fff1d6;
    color: #a36d14;
}

.confirmed {
    background: #dff5e9;
    color: #24794b;
}

.cancelled {
    background: #fde5e5;
    color: #b43e3e;
}

.empty {
    text-align: center;
    padding: 35px;
    color: #879697;
}

.footer {
    text-align: center;
    margin-top: 35px;
    color: #9aabab;
    font-size: 12px;
}

@media(max-width: 1050px) {
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 750px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .logo-sub {
        margin-bottom: 20px;
    }

    .main {
        margin-left: 0;
        padding: 25px 18px;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    .topbar {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media(max-width: 450px) {
    .stats {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<aside class="sidebar">

    <div class="logo">Ocean Pearl</div>
    <div class="logo-sub">VILLA ADMINISTRATION</div>

    <div class="menu-title">MAIN MENU</div>

    <a href="dashboard.php" class="active">▦ &nbsp; Dashboard</a>
    <a href="rooms.php">▣ &nbsp; Manage Rooms</a>
    <a href="bookings.php">▤ &nbsp; Reservations</a>

    <div class="menu-title">OTHER</div>

    <a href="../index.php">⌂ &nbsp; Visit Website</a>
    <a href="../auth/logout.php">↪ &nbsp; Logout</a>

</aside>

<main class="main">

    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p class="welcome">Welcome back! Here's your villa overview.</p>
        </div>

        <div class="admin-info">
            Welcome, <span><?php echo $adminName; ?></span>
        </div>
    </div>

    <h2 class="section-heading">Overview</h2>

    <div class="stats">

        <div class="stat-card">
            <div class="stat-icon">🏡</div>
            <div class="stat-label">Total Rooms</div>
            <div class="stat-number"><?php echo $totalRooms; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-label">Total Bookings</div>
            <div class="stat-number"><?php echo $totalBookings; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-label">Pending Bookings</div>
            <div class="stat-number"><?php echo $pendingBookings; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-label">Registered Guests</div>
            <div class="stat-number"><?php echo $totalGuests; ?></div>
        </div>

    </div>

    <h2 class="section-heading">Quick Actions</h2>

    <div class="quick-actions">

        <a href="rooms.php" class="action-card">
            <div class="action-icon">🛏️</div>
            <h3>Manage Rooms</h3>
            <p>Add, update and manage villa rooms.</p>
        </a>

        <a href="bookings.php" class="action-card">
            <div class="action-icon">📋</div>
            <h3>Manage Reservations</h3>
            <p>Review and manage guest bookings.</p>
        </a>

        <a href="../index.php" class="action-card">
            <div class="action-icon">🌊</div>
            <h3>View Website</h3>
            <p>Visit the Ocean Pearl Villa website.</p>
        </a>

    </div>

    <h2 class="section-heading">Recent Reservations</h2>

    <div class="table-container">

        <table>
            <thead>
                <tr>
                    <th>BOOKING ID</th>
                    <th>GUEST</th>
                    <th>ROOM</th>
                    <th>CHECK IN</th>
                    <th>CHECK OUT</th>
                    <th>AMOUNT</th>
                    <th>STATUS</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($recentBookings && $recentBookings->num_rows > 0): ?>

                <?php while ($booking = $recentBookings->fetch_assoc()): ?>

                    <tr>
                        <td>#<?php echo (int)$booking["booking_id"]; ?></td>

                        <td>
                            <?php echo htmlspecialchars($booking["full_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($booking["room_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($booking["check_in"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($booking["check_out"]); ?>
                        </td>

                        <td>
                            Rs. <?php echo number_format((float)$booking["total_price"], 2); ?>
                        </td>

                        <td>
                            <span class="status <?php echo htmlspecialchars($booking["status"]); ?>">
                                <?php echo htmlspecialchars($booking["status"]); ?>
                            </span>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" class="empty">
                        No reservations available yet.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

    <div class="footer">
        © <?php echo date("Y"); ?> Ocean Pearl Villa. All Rights Reserved.
    </div>

</main>

</body>
</html>
