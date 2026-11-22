
<?php
session_start();
require_once "../config/database.php";

$check_in = $_GET["check_in"] ?? "";
$check_out = $_GET["check_out"] ?? "";
$guests = (int)($_GET["guests"] ?? 1);

$rooms = [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["search"])) {

    $today = date("Y-m-d");

    if (
        !$check_in ||
        !$check_out ||
        !strtotime($check_in) ||
        !strtotime($check_out)
    ) {
        $error = "Please select valid check-in and check-out dates.";
    } elseif ($check_in < $today) {
        $error = "Check-in date must be today or a future date.";
    } elseif ($check_out <= $check_in) {
        $error = "Check-out date must be after check-in date.";
    } elseif ($guests < 1) {
        $error = "Please select at least one guest.";
    } else {

        // Find rooms without overlapping confirmed bookings
        $stmt = $conn->prepare(
            "SELECT r.*
             FROM rooms r
             WHERE r.status = 'available'
             AND r.capacity >= ?
             AND NOT EXISTS (
                 SELECT 1
                 FROM bookings b
                 WHERE b.room_id = r.room_id
                 AND b.status = 'confirmed'
                 AND b.check_in < ?
                 AND b.check_out > ?
             )
             ORDER BY r.price_per_night ASC"
        );

        $stmt->bind_param(
            "iss",
            $guests,
            $check_out,
            $check_in
        );

        $stmt->execute();
        $result = $stmt->get_result();

        while ($room = $result->fetch_assoc()) {
            $rooms[] = $room;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Room Availability | Ocean Pearl Villa</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f3f7f7;
    color: #263b3b;
}

header {
    background: #073f43;
    padding: 22px 5%;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

header a {
    color: white;
    text-decoration: none;
    margin-left: 18px;
}

.container {
    max-width: 1100px;
    width: 92%;
    margin: 40px auto;
}

h1 {
    margin-bottom: 10px;
    color: #07565a;
}

.subtitle {
    color: #718080;
    margin-bottom: 25px;
}

.search-box {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #0000000d;
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input, select {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccd8d8;
    border-radius: 7px;
    font-size: 14px;
}

button {
    margin-top: 22px;
    padding: 13px 25px;
    background: #087c78;
    color: white;
    border: none;
    border-radius: 7px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #07565a;
}

.error {
    background: #fde9e7;
    color: #a3261c;
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 22px;
}

.room-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 15px #0000000d;
}

.room-card img {
    width: 100%;
    height: 190px;
    object-fit: cover;
}

.room-info {
    padding: 20px;
}

.room-info h3 {
    color: #07565a;
    margin-bottom: 10px;
}

.room-info p {
    margin: 8px 0;
    color: #647575;
}

.price {
    color: #087c78 !important;
    font-size: 18px;
    font-weight: bold;
}

.book-btn {
    display: inline-block;
    margin-top: 12px;
    padding: 11px 18px;
    background: #087c78;
    color: white;
    border-radius: 7px;
    text-decoration: none;
}

.empty {
    background: white;
    padding: 25px;
    border-radius: 10px;
    color: #718080;
    text-align: center;
}

@media(max-width: 700px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    header {
        align-items: flex-start;
        flex-direction: column;
    }

    header a {
        margin: 0 12px 0 0;
    }
}
</style>
</head>

<body>

<header>
    <h2>Ocean Pearl Villa</h2>

    <nav>
        <a href="../index.php">Home</a>
        <a href="../auth/login.php">Login</a>
        <a href="../auth/register.php">Register</a>
    </nav>
</header>

<div class="container">

    <h1>Find Your Perfect Room</h1>

    <p class="subtitle">
        Check room availability and plan your stay at Ocean Pearl Villa.
    </p>

    <section class="search-box">

        <form method="GET" action="availability.php">

            <div class="form-grid">

                <div>
                    <label>Check-in Date</label>
                    <input type="date"
                           name="check_in"
                           min="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($check_in) ?>"
                           required>
                </div>

                <div>
                    <label>Check-out Date</label>
                    <input type="date"
                           name="check_out"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= htmlspecialchars($check_out) ?>"
                           required>
                </div>

                <div>
                    <label>Number of Guests</label>
                    <select name="guests" required>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>"
                                <?= $guests === $i ? 'selected' : '' ?>>
                                <?= $i ?> Guest<?= $i > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

            </div>

            <button type="submit" name="search" value="1">
                Check Availability
            </button>

        </form>

    </section>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php elseif (isset($_GET["search"])): ?>

        <h2 style="margin-bottom:20px;">Available Rooms</h2>

        <?php if (count($rooms) > 0): ?>

            <div class="room-grid">

                <?php foreach ($rooms as $room): ?>

                    <?php
                    $image = !empty($room["image"])
                        ? "../assets/images/rooms/" . rawurlencode(basename($room["image"]))
                        : "../assets/images/villa/ocean-pearl-hero.png";

                    $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
                    $total = $nights * (float)$room["price_per_night"];
                    ?>

                    <article class="room-card">

                       <?php
                        $image = !empty($room['image'])
                        ? "../assets/images/rooms/" . $room['image']
                        : "https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=85";
                       ?>

    <img src="<?= htmlspecialchars($image) ?>"
         alt="<?= htmlspecialchars($room["room_name"]) ?>">
                        <div class="room-info">

                            <h3><?= htmlspecialchars($room["room_name"]) ?></h3>

                            <p><?= htmlspecialchars($room["room_type"]) ?></p>

                            <p>
                                Capacity: <?= (int)$room["capacity"] ?> Guests
                            </p>

                            <p>
                                <?= htmlspecialchars($room["description"] ?? "") ?>
                            </p>

                            <p class="price">
                                LKR <?= number_format((float)$room["price_per_night"], 2) ?> / Night
                            </p>

                            <p>
                                Estimated Total:
                                <strong>LKR <?= number_format($total, 2) ?></strong>
                            </p>

                            <a class="book-btn"
                               href="../guest/book-room.php?room_id=<?= (int)$room["room_id"] ?>&check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>&guests=<?= $guests ?>">
                                Book This Room
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty">
                No available rooms found for your selected dates and guest count.
                Please try different dates or fewer guests.
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
