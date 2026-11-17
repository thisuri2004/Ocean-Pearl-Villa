
<?php
session_start();
require_once "../config/database.php";

// Check guest login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "guest") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$success = "";
$error = "";

// Cancel pending booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_booking"])) {

    $booking_id = intval($_POST["booking_id"]);

    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'cancelled'
        WHERE booking_id = ?
        AND user_id = ?
        AND status = 'pending'
    ");

    $stmt->bind_param("ii", $booking_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "Your booking has been cancelled successfully.";
    } else {
        $error = "Unable to cancel this booking. Only pending bookings can be cancelled.";
    }

    $stmt->close();
}

// Get guest bookings
$stmt = $conn->prepare("
    SELECT b.booking_id, b.check_in, b.check_out,
       b.guests, b.total_price, b.status,
       r.room_id, r.room_name, r.room_type, r.image
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings | Ocean Pearl Villa</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f2f7f7;
    color: #173b43;
}

header {
    background: #073b46;
    color: white;
    padding: 22px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.logo {
    font-size: 23px;
    font-weight: bold;
}

nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
}

nav a:hover {
    color: #75d6d0;
}

.container {
    max-width: 1150px;
    margin: 45px auto;
    padding: 0 20px;
}

h1 {
    font-size: 32px;
    margin-bottom: 10px;
}

.subtitle {
    color: #71858a;
    margin-bottom: 30px;
}

.message {
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #d8f5e5;
    color: #17643a;
}

.error {
    background: #ffe0e0;
    color: #a32626;
}

.booking-card {
    background: white;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 22px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.07);
    display: flex;
    gap: 25px;
    align-items: center;
    flex-wrap: wrap;
}

.booking-image {
    width: 220px;
    height: 165px;
    object-fit: cover;
    border-radius: 10px;
}

.booking-details {
    flex: 1;
    min-width: 230px;
}

.booking-details h2 {
    margin-bottom: 12px;
    color: #07505a;
}

.booking-details p {
    margin: 9px 0;
    color: #52686d;
}

.status {
    display: inline-block;
    padding: 7px 13px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    text-transform: capitalize;
}

.pending {
    background: #fff0c7;
    color: #946000;
}

.confirmed {
    background: #d9f5e5;
    color: #167345;
}

.cancelled {
    background: #ffe0e0;
    color: #a32626;
}

.price {
    font-size: 19px;
    font-weight: bold;
    color: #087b78;
}

.cancel-btn {
    background: #d94b4b;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 7px;
    cursor: pointer;
    margin-top: 12px;
}

.cancel-btn:hover {
    background: #b82f2f;
}

.empty {
    background: white;
    padding: 45px 20px;
    text-align: center;
    border-radius: 12px;
    color: #71858a;
}

.empty a {
    display: inline-block;
    margin-top: 18px;
    background: #087b78;
    color: white;
    padding: 12px 22px;
    border-radius: 7px;
    text-decoration: none;
}

footer {
    text-align: center;
    padding: 25px;
    background: #073b46;
    color: white;
    margin-top: 60px;
}

@media(max-width: 600px) {
    header {
        flex-direction: column;
    }

    nav a {
        margin: 5px;
        display: inline-block;
    }

    .booking-image {
        width: 100%;
        height: 200px;
    }
}
</style>
</head>

<body>

<header>
    <div class="logo">Ocean Pearl Villa</div>

    <nav>
        <a href="../index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <h1>My Bookings</h1>
    <p class="subtitle">View and manage your room reservations.</p>

    <?php if ($success): ?>
        <div class="message">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($bookings->num_rows > 0): ?>

        <?php while ($booking = $bookings->fetch_assoc()): ?>

            <?php
            $roomImages = [
    1 => "https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=85",
    2 => "https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85",
    3 => "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=900&q=85"
];

$roomId = (int)$booking["room_id"];

$image = $roomImages[$roomId] ?? $roomImages[1];
            ?>

            <div class="booking-card">

                <img
                    class="booking-image"
                    src="<?php echo htmlspecialchars($image); ?>"
                    alt="Room Image"
                    onerror="this.src='../assets/images/villa/ocean-pearl-hero.png'"
                >

                <div class="booking-details">

                    <h2>
                        <?php echo htmlspecialchars($booking["room_name"]); ?>
                    </h2>

                    <p>
                        <strong>Room Type:</strong>
                        <?php echo htmlspecialchars($booking["room_type"]); ?>
                    </p>

                    <p>
                        <strong>Check-in:</strong>
                        <?php echo htmlspecialchars($booking["check_in"]); ?>
                    </p>

                    <p>
                        <strong>Check-out:</strong>
                        <?php echo htmlspecialchars($booking["check_out"]); ?>
                    </p>

                    <p>
                        <strong>Guests:</strong>
                        <?php echo (int)$booking["guests"]; ?>
                    </p>

                    <p class="price">
                        LKR <?php echo number_format((float)$booking["total_price"], 2); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>

                        <span class="status <?php echo htmlspecialchars($booking["status"]); ?>">
                            <?php echo htmlspecialchars($booking["status"]); ?>
                        </span>
                    </p>

                    <?php if ($booking["status"] === "pending"): ?>

                        <form method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this booking?');">

                            <input type="hidden"
                                   name="booking_id"
                                   value="<?php echo (int)$booking["booking_id"]; ?>">

                            <button type="submit"
                                    name="cancel_booking"
                                    class="cancel-btn">
                                Cancel Booking
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            <h2>No Bookings Yet</h2>
            <p>You haven't made any room reservations yet.</p>

            <a href="dashboard.php">Explore Rooms</a>
        </div>

    <?php endif; ?>

</div>

<footer>
    © 2026 Ocean Pearl Villa. All Rights Reserved.
</footer>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
