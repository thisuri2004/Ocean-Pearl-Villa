
<?php
session_start();
require_once "../config/database.php";

// Admin access protection
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";
$error = "";

// Update booking status
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $booking_id = (int)($_POST["booking_id"] ?? 0);
    $new_status = $_POST["status"] ?? "";

    if ($booking_id <= 0 || !in_array($new_status, ["confirmed", "cancelled"], true)) {
        $error = "Invalid booking request.";
    } else {

        // Get booking details
        $stmt = $conn->prepare(
            "SELECT room_id, check_in, check_out, status
             FROM bookings
             WHERE booking_id = ?"
        );

        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$booking) {
            $error = "Booking not found.";
        } elseif ($booking["status"] !== "pending") {
            $error = "Only pending bookings can be updated.";
        } else {

            // Check room availability before confirmation
            if ($new_status === "confirmed") {

                $check = $conn->prepare(
                    "SELECT booking_id
                     FROM bookings
                     WHERE room_id = ?
                     AND status = 'confirmed'
                     AND check_in < ?
                     AND check_out > ?
                     AND booking_id != ?"
                );

                $check->bind_param(
                    "issi",
                    $booking["room_id"],
                    $booking["check_out"],
                    $booking["check_in"],
                    $booking_id
                );

                $check->execute();
                $conflict = $check->get_result()->num_rows > 0;
                $check->close();

                if ($conflict) {
                    $error = "This room already has a confirmed booking for the selected dates.";
                }
            }

            if ($error === "") {

                $update = $conn->prepare(
                    "UPDATE bookings
                     SET status = ?
                     WHERE booking_id = ? AND status = 'pending'"
                );

                $update->bind_param("si", $new_status, $booking_id);

                if ($update->execute() && $update->affected_rows > 0) {
                    $message = $new_status === "confirmed"
                        ? "Booking confirmed successfully!"
                        : "Booking cancelled successfully!";
                } else {
                    $error = "Booking status could not be updated.";
                }

                $update->close();
            }
        }
    }
}

// Get all bookings
$result = $conn->query(
    "SELECT
        b.booking_id,
        b.check_in,
        b.check_out,
        b.guests,
        b.total_price,
        b.status,
        b.created_at,
        u.full_name,
        u.email,
        u.phone,
        r.room_name,
        r.room_type
     FROM bookings b
     INNER JOIN users u ON b.user_id = u.user_id
     INNER JOIN rooms r ON b.room_id = r.room_id
     ORDER BY b.created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Bookings | Ocean Pearl Villa</title>

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
    color: white;
    padding: 22px 5%;
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
    width: 92%;
    max-width: 1250px;
    margin: 35px auto;
}

h1 {
    margin-bottom: 10px;
}

.subtitle {
    color: #718080;
    margin-bottom: 25px;
}

.panel {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #0000000d;
}

.panel h2 {
    color: #07565a;
    margin-bottom: 20px;
}

.message, .error {
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 18px;
}

.message {
    background: #e1f5e9;
    color: #176b39;
}

.error {
    background: #fde9e7;
    color: #a3261c;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1050px;
}

th, td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #e5eeee;
}

th {
    background: #edf5f4;
    color: #07565a;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: capitalize;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.confirmed {
    background: #e1f5e9;
    color: #176b39;
}

.cancelled {
    background: #fde9e7;
    color: #a3261c;
}

.actions {
    display: flex;
    gap: 7px;
}

button {
    border: none;
    padding: 9px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.confirm-btn {
    background: #087c78;
    color: white;
}

.cancel-btn {
    background: #fde9e7;
    color: #a3261c;
}

.no-data {
    text-align: center;
    padding: 30px;
    color: #718080;
}

@media(max-width: 650px) {
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
        <a href="dashboard.php">Dashboard</a>
        <a href="rooms.php">Manage Rooms</a>
        <a href="bookings.php">Bookings</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <h1>Booking Management</h1>

    <p class="subtitle">
        View guest reservations and manage booking requests.
    </p>

    <?php if ($message): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <section class="panel">

        <h2>All Reservations</h2>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Details</th>
                        <th>Room</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Guests</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($booking = $result->fetch_assoc()): ?>

                        <tr>

                            <td>#<?= (int)$booking["booking_id"] ?></td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($booking["full_name"]) ?>
                                </strong>
                                <br>
                                <?= htmlspecialchars($booking["email"]) ?>
                                <br>
                                <?= htmlspecialchars($booking["phone"] ?? "") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($booking["room_name"]) ?>
                                <br>
                                <small>
                                    <?= htmlspecialchars($booking["room_type"]) ?>
                                </small>
                            </td>

                            <td><?= htmlspecialchars($booking["check_in"]) ?></td>

                            <td><?= htmlspecialchars($booking["check_out"]) ?></td>

                            <td><?= (int)$booking["guests"] ?></td>

                            <td>
                                LKR <?= number_format((float)$booking["total_price"], 2) ?>
                            </td>

                            <td>
                                <span class="status <?= htmlspecialchars($booking["status"]) ?>">
                                    <?= ucfirst(htmlspecialchars($booking["status"])) ?>
                                </span>
                            </td>

                            <td>

                                <?php if ($booking["status"] === "pending"): ?>

                                    <div class="actions">

                                        <form method="POST"
                                              onsubmit="return confirm('Confirm this booking?');">

                                            <input type="hidden" name="booking_id"
                                                   value="<?= (int)$booking["booking_id"] ?>">

                                            <input type="hidden" name="status"
                                                   value="confirmed">

                                            <button type="submit" class="confirm-btn">
                                                Confirm
                                            </button>

                                        </form>

                                        <form method="POST"
                                              onsubmit="return confirm('Cancel this booking?');">

                                            <input type="hidden" name="booking_id"
                                                   value="<?= (int)$booking["booking_id"] ?>">

                                            <input type="hidden" name="status"
                                                   value="cancelled">

                                            <button type="submit" class="cancel-btn">
                                                Cancel
                                            </button>

                                        </form>

                                    </div>

                                <?php else: ?>

                                    <span>—</span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="no-data">
                            No bookings found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>
