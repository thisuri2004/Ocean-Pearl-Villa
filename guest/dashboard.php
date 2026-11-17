
<?php
session_start();

require_once "../config/database.php";

// Check guest login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "guest") {
    header("Location: ../auth/login.php");
    exit();
}

$full_name = $_SESSION["full_name"];

// Get available rooms
$stmt = $conn->prepare(
    "SELECT room_id, room_name, room_type, description,
            price_per_night, capacity, image
     FROM rooms
     WHERE status = 'available'
     ORDER BY room_id ASC"
);

$stmt->execute();
$rooms = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Guest Dashboard | Ocean Pearl Villa</title>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: #f4f8f9;
    color: #173e4b;
}

header {
    background: #07536a;
    color: white;
    padding: 20px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 25px;
    font-weight: 700;
    letter-spacing: 1px;
}

.logo span {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 9px;
    letter-spacing: 6px;
    color: #8ce0dc;
}

nav {
    display: flex;
    align-items: center;
    gap: 25px;
    flex-wrap: wrap;
}

nav a {
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

nav a:hover {
    color: #8ce0dc;
}

.logout {
    background: white;
    color: #07536a;
    padding: 10px 18px;
    border-radius: 5px;
}

.logout:hover {
    background: #dff5f3;
    color: #07536a;
}

.welcome {
    padding: 65px 7%;
    background:
        linear-gradient(rgba(4, 39, 56, .72), rgba(4, 39, 56, .72)),
        url('../assets/images/villa/ocean-pearl-hero.png');

    background-size: cover;
    background-position: center;
    color: white;
}

.welcome h1 {
    font-family: 'Playfair Display', serif;
    font-size: 38px;
    margin-bottom: 12px;
}

.welcome p {
    font-size: 15px;
    color: #d7eeee;
}

.content {
    padding: 55px 7%;
}

.section-heading {
    margin-bottom: 30px;
}

.section-heading h2 {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    margin-bottom: 8px;
}

.section-heading p {
    color: #82969c;
    font-size: 14px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 22px;
    margin-bottom: 55px;
}

.action-card {
    background: white;
    padding: 28px;
    border-radius: 10px;
    box-shadow: 0 5px 22px rgba(20, 60, 75, .06);
    text-decoration: none;
    color: #173e4b;
    transition: .3s;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 28px rgba(20, 60, 75, .12);
}

.action-icon {
    font-size: 30px;
    margin-bottom: 15px;
}

.action-card h3 {
    font-size: 17px;
    margin-bottom: 8px;
}

.action-card p {
    color: #82969c;
    font-size: 13px;
    line-height: 1.6;
}

.room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.room-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 22px rgba(20, 60, 75, .07);
}

.room-image {
    width: 100%;
    height: 210px;
    object-fit: cover;
}

.room-details {
    padding: 23px;
}

.room-details h3 {
    font-family: 'Playfair Display', serif;
    font-size: 21px;
    margin-bottom: 9px;
}

.room-type {
    color: #078b96;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 12px;
}

.room-description {
    font-size: 13px;
    color: #82969c;
    line-height: 1.6;
    min-height: 42px;
}

.room-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 17px;
    border-top: 1px solid #edf1f2;
}

.price {
    color: #087e91;
    font-size: 17px;
    font-weight: 700;
}

.capacity {
    font-size: 12px;
    color: #82969c;
}

footer {
    text-align: center;
    padding: 25px;
    background: #07536a;
    color: #c9e1e5;
    font-size: 12px;
}

@media (max-width: 600px) {

    header {
        padding: 20px 5%;
    }

    nav {
        gap: 14px;
    }

    .welcome {
        padding: 50px 6%;
    }

    .welcome h1 {
        font-size: 29px;
    }

    .content {
        padding: 40px 6%;
    }
}

</style>
</head>

<body>

<header>

    <div class="logo">
        OCEAN PEARL
        <span>V I L L A</span>
    </div>

    <nav>
        <a href="../index.php">Home</a>
        <a href="#rooms">Explore Rooms</a>
        <a href="my-bookings.php">My Bookings</a>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </nav>

</header>

<section class="welcome">

    <h1>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>

    <p>
        Welcome to your personal guest dashboard.
        Discover your perfect coastal escape with Ocean Pearl Villa.
    </p>

</section>

<main class="content">

    <div class="section-heading">
        <h2>Your Guest Dashboard</h2>
        <p>Manage your stay and explore our beautiful accommodation.</p>
    </div>

    <div class="quick-actions">

        <a href="#rooms" class="action-card">
            <div class="action-icon">🏝️</div>
            <h3>Explore Rooms</h3>
            <p>Discover our comfortable and luxurious accommodation options.</p>
        </a>

        <a href="my-bookings.php" class="action-card">
            <div class="action-icon">📅</div>
            <h3>My Bookings</h3>
            <p>View and manage your room reservation requests.</p>
        </a>

        <a href="../index.php" class="action-card">
            <div class="action-icon">🌊</div>
            <h3>Visit Website</h3>
            <p>Explore Ocean Pearl Villa and learn more about our facilities.</p>
        </a>

    </div>

    <section id="rooms">

        <div class="section-heading">
            <h2>Discover Our Rooms</h2>
            <p>Choose the perfect accommodation for your stay.</p>
        </div>

        <div class="room-grid">

            <?php if ($rooms->num_rows > 0): ?>

            <?php $index = 0; ?>

            <?php while ($room = $rooms->fetch_assoc()): ?>
                    <div class="room-card">

                       <?php
                        $roomImages = [
                            "https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=85",
                            "https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85",
                            "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=900&q=85"
                        ];

                        $image = $roomImages[$index % count($roomImages)];
                        ?>

                        <img
                            src="<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($room["room_name"]); ?>"
                            class="room-image"
                        >

                        <div class="room-details">

                            <h3><?php echo htmlspecialchars($room["room_name"]); ?></h3>

                            <div class="room-type">
                                <?php echo htmlspecialchars($room["room_type"]); ?>
                            </div>

                            <p class="room-description">
                                <?php echo htmlspecialchars($room["description"]); ?>
                            </p>

                            <div class="room-info">

                                <div class="price">
                                    Rs. <?php echo number_format((float)$room["price_per_night"], 2); ?>
                                    <small>/ night</small>
                                </div>

                                <div class="capacity">
                                    Up to <?php echo (int)$room["capacity"]; ?> Guests
                                </div>

                            </div>

                        </div>

                    </div>
                    <?php $index++; ?>

                <?php endwhile; ?>

            <?php else: ?>

                <p>No rooms are currently available.</p>

            <?php endif; ?>

        </div>

    </section>

</main>

<footer>
    © <?php echo date("Y"); ?> Ocean Pearl Villa.
    Experience the beauty of the Sri Lankan coast.
</footer>

</body>
</html>

<?php
$stmt->close();
?>
