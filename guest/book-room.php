
<?php
session_start();
require_once "../config/database.php";

// Check guest login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "guest") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];

$room_id = isset($_POST["room_id"])
    ? (int)$_POST["room_id"]
    : (int)($_GET["room_id"] ?? 0);

$error = "";
$success = "";

$check_in = "";
$check_out = "";
$guests = 1;

// Get room details
$stmt = $conn->prepare("
    SELECT room_id, room_name, room_type, description,
           price_per_night, capacity, image, status
    FROM rooms
    WHERE room_id = ?
");

$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$stmt->close();

// Process booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && $room) {

    $check_in = trim($_POST["check_in"] ?? "");
    $check_out = trim($_POST["check_out"] ?? "");
    $guests = (int)($_POST["guests"] ?? 0);

    if ($room["status"] !== "available") {
        $error = "This room is currently unavailable.";
    } elseif (!$check_in || !$check_out || $guests < 1) {
        $error = "Please complete all booking details.";
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_in) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_out)
    ) {
        $error = "Please enter valid dates.";
    } else {

        $startDate = DateTime::createFromFormat("!Y-m-d", $check_in);
        $endDate = DateTime::createFromFormat("!Y-m-d", $check_out);
        $today = new DateTime("today");

        if (
            !$startDate || $startDate->format("Y-m-d") !== $check_in ||
            !$endDate || $endDate->format("Y-m-d") !== $check_out
        ) {
            $error = "Please enter valid booking dates.";
        } elseif ($startDate <= $today) {
            $error = "Check-in date must be a future date.";
        } elseif ($endDate <= $startDate) {
            $error = "Check-out date must be after check-in date.";
        } elseif ($guests > (int)$room["capacity"]) {
            $error = "This room can accommodate only " .
                     (int)$room["capacity"] . " guests.";
        } else {

            $nights = $startDate->diff($endDate)->days;
            $total_price = $nights * (float)$room["price_per_night"];

            // Check existing confirmed bookings
            $checkStmt = $conn->prepare("
                SELECT booking_id
                FROM bookings
                WHERE room_id = ?
                AND status = 'confirmed'
                AND check_in < ?
                AND check_out > ?
            ");

            $checkStmt->bind_param(
                "iss",
                $room_id,
                $check_out,
                $check_in
            );

            $checkStmt->execute();
            $conflict = $checkStmt->get_result();

            if ($conflict->num_rows > 0) {
                $error = "Sorry, this room is already booked for the selected dates.";
            } else {

                // Save booking as pending
                $insertStmt = $conn->prepare("
                    INSERT INTO bookings
                    (user_id, room_id, check_in, check_out,
                     guests, total_price, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");

                $insertStmt->bind_param(
                    "iissid",
                    $user_id,
                    $room_id,
                    $check_in,
                    $check_out,
                    $guests,
                    $total_price
                );

                if ($insertStmt->execute()) {
                    $insertStmt->close();
                    $checkStmt->close();

                    header("Location: my-bookings.php?booking=success");
                    exit();
                } else {
                    $error = "Booking failed. Please try again.";
                }

                $insertStmt->close();
            }

            $checkStmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Book a Room | Ocean Pearl Villa</title>

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

.container {
    max-width: 1050px;
    margin: 45px auto;
    padding: 0 20px;
}

h1 {
    margin-bottom: 10px;
}

.subtitle {
    color: #71858a;
    margin-bottom: 30px;
}

.booking-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
    align-items: start;
}

.room-card, .form-card {
    background: white;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.07);
}

.room-card img {
    width: 100%;
    height: 240px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 18px;
}

.room-card h2 {
    color: #07505a;
    margin-bottom: 12px;
}

.room-card p {
    margin: 10px 0;
    color: #61777c;
    line-height: 1.5;
}

.price {
    color: #087b78 !important;
    font-size: 21px;
    font-weight: bold;
}

.form-card h2 {
    margin-bottom: 22px;
    color: #07505a;
}

label {
    display: block;
    font-weight: bold;
    margin: 16px 0 8px;
}

input, select {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccd9dc;
    border-radius: 7px;
    font-size: 15px;
}

input:focus, select:focus {
    outline: 2px solid #75c9c3;
    border-color: #087b78;
}

.total {
    margin-top: 22px;
    padding: 16px;
    background: #e5f5f3;
    border-radius: 8px;
}

.total strong {
    color: #087b78;
    font-size: 21px;
}

button {
    width: 100%;
    padding: 14px;
    background: #087b78;
    color: white;
    border: none;
    border-radius: 7px;
    font-size: 16px;
    font-weight: bold;
    margin-top: 22px;
    cursor: pointer;
}

button:hover {
    background: #065e5b;
}

.message {
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #ffe0e0;
    color: #a32626;
}

.notice {
    margin-top: 15px;
    color: #71858a;
    font-size: 13px;
    line-height: 1.5;
}

.unavailable {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
}

footer {
    background: #073b46;
    color: white;
    text-align: center;
    padding: 25px;
    margin-top: 60px;
}

@media(max-width: 750px) {
    .booking-layout {
        grid-template-columns: 1fr;
    }

    header {
        flex-direction: column;
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
        <a href="my-bookings.php">My Bookings</a>
    </nav>
</header>

<div class="container">

    <h1>Book Your Stay</h1>
    <p class="subtitle">Choose your dates and reserve your room at Ocean Pearl Villa.</p>

    <?php if ($error): ?>
        <div class="message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!$room): ?>

        <div class="unavailable">
            <h2>Room Not Found</h2>
            <p>Please select a valid room from the dashboard.</p>
            <br>
            <a href="dashboard.php">Back to Dashboard</a>
        </div>

    <?php elseif ($room["status"] !== "available"): ?>

        <div class="unavailable">
            <h2>Room Unavailable</h2>
            <p>This room is currently unavailable.</p>
        </div>

    <?php else: ?>

        <div class="booking-layout">

            <div class="room-card">

                <?php
                $roomImages = [
                    "https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=85",
                    "https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85",
                    "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=900&q=85"
                ];

                $image = $roomImages[($room_id - 1) % 3];
                ?>

                <img
                    src="<?php echo htmlspecialchars($image); ?>"
                    alt="Room Image"
                >

                <h2><?php echo htmlspecialchars($room["room_name"]); ?></h2>

                <p>
                    <strong>Room Type:</strong>
                    <?php echo htmlspecialchars($room["room_type"]); ?>
                </p>

                <p><?php echo htmlspecialchars($room["description"]); ?></p>

                <p>
                    <strong>Maximum Guests:</strong>
                    <?php echo (int)$room["capacity"]; ?>
                </p>

                <p class="price">
                    LKR <?php echo number_format((float)$room["price_per_night"], 2); ?>
                    / Night
                </p>

                <p>
                    <strong>Facilities:</strong>
                    <?php echo htmlspecialchars($room["facilities"] ?? ""); ?>
                </p>

            </div>

            <div class="form-card">

                <h2>Reservation Details</h2>

                <form method="POST" id="bookingForm">

                    <input type="hidden" name="room_id"
                           value="<?php echo (int)$room["room_id"]; ?>">

                    <label for="check_in">Check-in Date</label>
                    <input type="date" id="check_in" name="check_in"
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                           value="<?php echo htmlspecialchars($check_in); ?>"
                           required>

                    <label for="check_out">Check-out Date</label>
                    <input type="date" id="check_out" name="check_out"
                           min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>"
                           value="<?php echo htmlspecialchars($check_out); ?>"
                           required>

                    <label for="guests">Number of Guests</label>
                    <select id="guests" name="guests" required>
                        <?php for ($i = 1; $i <= (int)$room["capacity"]; $i++): ?>
                            <option value="<?php echo $i; ?>"
                                <?php echo ($guests === $i) ? "selected" : ""; ?>>
                                <?php echo $i; ?>
                                <?php echo $i === 1 ? "Guest" : "Guests"; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <div class="total">
                        <p>Number of Nights: <strong id="nights">0</strong></p>
                        <br>
                        <p>Estimated Total Price:</p>
                        <strong id="totalPrice">LKR 0.00</strong>
                    </div>

                    <button type="submit">Submit Booking Request</button>

                    <p class="notice">
                        Your reservation will be submitted as a pending request.
                        The booking will need confirmation by the administrator.
                    </p>

                </form>

            </div>

        </div>

    <?php endif; ?>

</div>

<footer>
    © 2026 Ocean Pearl Villa. All Rights Reserved.
</footer>

<script>
const checkIn = document.getElementById("check_in");
const checkOut = document.getElementById("check_out");
const nightsDisplay = document.getElementById("nights");
const totalDisplay = document.getElementById("totalPrice");

const pricePerNight = <?php echo $room ? (float)$room["price_per_night"] : 0; ?>;

function calculateTotal() {

    if (!checkIn || !checkOut) return;

    const start = new Date(checkIn.value + "T00:00:00");
    const end = new Date(checkOut.value + "T00:00:00");

    if (!checkIn.value || !checkOut.value || end <= start) {
        nightsDisplay.textContent = "0";
        totalDisplay.textContent = "LKR 0.00";
        return;
    }

    const nights = Math.round((end - start) / 86400000);
    const total = nights * pricePerNight;

    nightsDisplay.textContent = nights;
    totalDisplay.textContent = "LKR " +
        total.toLocaleString("en-LK", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
}

if (checkIn && checkOut) {

    checkIn.addEventListener("change", function() {
        checkOut.min = checkIn.value
            ? new Date(new Date(checkIn.value + "T00:00:00").getTime() + 86400000)
                .toISOString().split("T")[0]
            : "";

        calculateTotal();
    });

    checkOut.addEventListener("change", calculateTotal);

    calculateTotal();
}
</script>

</body>
</html>

<?php
$conn->close();
?>
