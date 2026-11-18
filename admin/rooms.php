
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

// Escape output safely
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Add or Update Room
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "update") {

        $room_name = trim($_POST["room_name"] ?? "");
        $room_type = trim($_POST["room_type"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $price = (float)($_POST["price_per_night"] ?? 0);
        $capacity = (int)($_POST["capacity"] ?? 0);
        $facilities = trim($_POST["facilities"] ?? "");
        $image = trim($_POST["image"] ?? "");
        $status = $_POST["status"] ?? "available";

        if (
            $room_name === "" ||
            $room_type === "" ||
            $price <= 0 ||
            $capacity <= 0 ||
            !in_array($status, ["available", "unavailable"], true)
        ) {
            $error = "Please enter valid room details.";
        } else {

            if ($action === "add") {

                $stmt = $conn->prepare(
                    "INSERT INTO rooms
                    (room_name, room_type, description, price_per_night,
                    capacity, facilities, image, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "sssdisss",
                    $room_name,
                    $room_type,
                    $description,
                    $price,
                    $capacity,
                    $facilities,
                    $image,
                    $status
                );

            } else {

                $room_id = (int)($_POST["room_id"] ?? 0);

                $stmt = $conn->prepare(
                    "UPDATE rooms SET
                    room_name = ?,
                    room_type = ?,
                    description = ?,
                    price_per_night = ?,
                    capacity = ?,
                    facilities = ?,
                    image = ?,
                    status = ?
                    WHERE room_id = ?"
                );

                $stmt->bind_param(
                    "sssdisssi",
                    $room_name,
                    $room_type,
                    $description,
                    $price,
                    $capacity,
                    $facilities,
                    $image,
                    $status,
                    $room_id
                );
            }

            if ($stmt->execute()) {
                $message = $action === "add"
                    ? "Room added successfully!"
                    : "Room updated successfully!";
            } else {
                $error = "Unable to save room details.";
            }

            $stmt->close();
        }
    }

    // Delete Room
    if ($action === "delete") {

        $room_id = (int)($_POST["room_id"] ?? 0);

        $check = $conn->prepare(
            "SELECT COUNT(*) AS total FROM bookings WHERE room_id = ?"
        );
        $check->bind_param("i", $room_id);
        $check->execute();
        $bookingCount = $check->get_result()->fetch_assoc()["total"];
        $check->close();

        if ($bookingCount > 0) {
            $error = "This room cannot be deleted because it has booking records.";
        } else {
            $stmt = $conn->prepare(
                "DELETE FROM rooms WHERE room_id = ?"
            );
            $stmt->bind_param("i", $room_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "Room deleted successfully!";
            } else {
                $error = "Room could not be deleted.";
            }

            $stmt->close();
        }
    }
}

// Get room details for editing
$editRoom = null;

if (isset($_GET["edit"])) {
    $room_id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM rooms WHERE room_id = ?"
    );
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $editRoom = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all rooms
$rooms = $conn->query(
    "SELECT * FROM rooms ORDER BY room_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Rooms | Ocean Pearl Villa</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
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
    max-width: 1200px;
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
    margin-bottom: 30px;
    box-shadow: 0 3px 15px #0000000d;
}

.panel h2 {
    margin-bottom: 20px;
    color: #07565a;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.field label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

.field input,
.field textarea,
.field select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccd8d8;
    border-radius: 7px;
    font-size: 14px;
}

.field textarea {
    min-height: 85px;
    resize: vertical;
}

.full {
    grid-column: 1 / -1;
}

button, .btn {
    border: none;
    padding: 11px 17px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
}

.primary {
    background: #087c78;
    color: white;
}

.edit {
    background: #e5f1f0;
    color: #07565a;
}

.delete {
    background: #fde9e7;
    color: #b42318;
}

.cancel {
    background: #e9eeee;
    color: #333;
    margin-left: 8px;
}

.message {
    background: #e1f5e9;
    color: #176b39;
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 18px;
}

.error {
    background: #fde9e7;
    color: #a3261c;
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 18px;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
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
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.available {
    background: #e1f5e9;
    color: #176b39;
}

.unavailable {
    background: #fde9e7;
    color: #a3261c;
}

.actions {
    display: flex;
    gap: 7px;
    align-items: center;
}

.actions form {
    display: inline;
}

@media(max-width: 650px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .full {
        grid-column: auto;
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
        <a href="dashboard.php">Dashboard</a>
        <a href="rooms.php">Manage Rooms</a>
        <a href="bookings.php">Bookings</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <h1>Room Management</h1>
    <p class="subtitle">Add, update and manage villa rooms.</p>

    <?php if ($message): ?>
        <div class="message"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="panel">

        <h2><?= $editRoom ? "Edit Room" : "Add New Room" ?></h2>

        <form method="POST" action="rooms.php<?= $editRoom ? '?edit=' . (int)$editRoom['room_id'] : '' ?>">

            <input type="hidden" name="action"
                   value="<?= $editRoom ? 'update' : 'add' ?>">

            <?php if ($editRoom): ?>
                <input type="hidden" name="room_id"
                       value="<?= (int)$editRoom['room_id'] ?>">
            <?php endif; ?>

            <div class="form-grid">

                <div class="field">
                    <label>Room Name</label>
                    <input type="text" name="room_name" required
                           value="<?= e($editRoom['room_name'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Room Type</label>
                    <input type="text" name="room_type" required
                           value="<?= e($editRoom['room_type'] ?? '') ?>">
                </div>

                <div class="field full">
                    <label>Description</label>
                    <textarea name="description"><?= e($editRoom['description'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>Price Per Night (LKR)</label>
                    <input type="number" name="price_per_night"
                           min="1" step="0.01" required
                           value="<?= e($editRoom['price_per_night'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Guest Capacity</label>
                    <input type="number" name="capacity"
                           min="1" required
                           value="<?= e($editRoom['capacity'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Facilities</label>
                    <input type="text" name="facilities"
                           placeholder="Wi-Fi, AC, Sea View"
                           value="<?= e($editRoom['facilities'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Room Image Filename</label>
                    <input type="text" name="image"
                           placeholder="ocean-view.jpg"
                           value="<?= e($editRoom['image'] ?? '') ?>">
                </div>

                <div class="field full">
                    <label>Room Status</label>
                    <select name="status">
                        <option value="available"
                            <?= ($editRoom['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>
                            Available
                        </option>
                        <option value="unavailable"
                            <?= ($editRoom['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>
                            Unavailable
                        </option>
                    </select>
                </div>

                <div class="field full">
                    <button type="submit" class="primary">
                        <?= $editRoom ? "Update Room" : "Add Room" ?>
                    </button>

                    <?php if ($editRoom): ?>
                        <a href="rooms.php" class="btn cancel">Cancel Edit</a>
                    <?php endif; ?>
                </div>

            </div>
        </form>
    </section>

    <section class="panel">

        <h2>All Rooms</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room Name</th>
                        <th>Type</th>
                        <th>Price / Night</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($rooms && $rooms->num_rows > 0): ?>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$room['room_id'] ?></td>
                            <td><?= e($room['room_name']) ?></td>
                            <td><?= e($room['room_type']) ?></td>
                            <td>LKR <?= number_format((float)$room['price_per_night'], 2) ?></td>
                            <td><?= (int)$room['capacity'] ?></td>
                            <td>
                                <span class="status <?= e($room['status']) ?>">
                                    <?= ucfirst(e($room['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">

                                    <a class="btn edit"
                                       href="rooms.php?edit=<?= (int)$room['room_id'] ?>">
                                        Edit
                                    </a>

                                    <form method="POST" action="rooms.php"
                                          onsubmit="return confirm('Are you sure you want to delete this room?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="room_id"
                                               value="<?= (int)$room['room_id'] ?>">
                                        <button type="submit" class="delete">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No rooms found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>

            </table>
        </div>

    </section>

</div>

</body>
</html>
