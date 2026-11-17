
<?php
session_start();

require_once "../config/database.php";

$full_name = "";
$email = "";
$phone = "";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all fields.";

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strpos($email, '@') === false
    ) {

        $error = "Please enter a valid email address.";

    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {

        $error = "Phone number must contain exactly 10 digits.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 8) {

        $error = "Password must contain at least 8 characters.";

    } else {

        // Check whether email already exists
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "This email address is already registered.";

        } else {

            // Securely hash the password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Register guest account
            $stmt = $conn->prepare(
                "INSERT INTO users
                (full_name, email, phone, password, role)
                VALUES (?, ?, ?, ?, 'guest')"
            );

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $phone,
                $hashed_password
            );

            if ($stmt->execute()) {

                $success = "Registration successful! You can now login.";

                $full_name = "";
                $email = "";
                $phone = "";

            } else {

                $error = "Registration failed. Please try again.";

            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Guest Registration | Ocean Pearl Villa</title>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;

    background:
        linear-gradient(rgba(4, 39, 56, .65), rgba(4, 39, 56, .65)),
        url('../assets/images/villa/ocean-pearl-hero.png');

    background-size: cover;
    background-position: center;
    background-attachment: fixed;

    display: flex;
    justify-content: center;
    align-items: center;
    padding: 35px 15px;
}

.register-container {
    width: 100%;
    max-width: 520px;
    background: white;
    padding: 42px;
    border-radius: 14px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, .22);
}

.logo {
    text-align: center;
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #07536a;
    letter-spacing: 1px;
}

.logo span {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    letter-spacing: 7px;
    margin-top: 4px;
    color: #0a929c;
}

.heading {
    text-align: center;
    margin: 30px 0;
}

.heading h1 {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    color: #123f50;
    margin-bottom: 9px;
}

.heading p {
    color: #7a9098;
    font-size: 14px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    color: #244d5b;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 14px 15px;
    border: 1px solid #d8e3e7;
    border-radius: 6px;
    outline: none;
    font-family: inherit;
    font-size: 14px;
    transition: .3s;
}

.form-group input:focus {
    border-color: #078b96;
    box-shadow: 0 0 0 3px rgba(7, 139, 150, .10);
}

.register-btn {
    width: 100%;
    padding: 15px;
    margin-top: 8px;
    border: none;
    border-radius: 6px;
    background: #087e91;
    color: white;
    font-family: inherit;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: .3s;
}

.register-btn:hover {
    background: #07536a;
}

.message {
    padding: 13px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 13px;
    text-align: center;
}

.error {
    background: #fff0f0;
    color: #c0392b;
    border: 1px solid #f5c6c6;
}

.success {
    background: #eaf8f0;
    color: #187a45;
    border: 1px solid #b9e5ca;
}

.login-link {
    text-align: center;
    margin-top: 23px;
    font-size: 14px;
    color: #71858a;
}

.login-link a {
    color: #087e91;
    text-decoration: none;
    font-weight: 700;
}

.login-link a:hover {
    text-decoration: underline;
}

.back-home {
    display: block;
    text-align: center;
    margin-top: 17px;
    font-size: 13px;
    color: #71858a;
    text-decoration: none;
}

.back-home:hover {
    color: #087e91;
}

@media (max-width: 500px) {
    .register-container {
        padding: 28px 22px;
    }

    .heading h1 {
        font-size: 26px;
    }
}

</style>
</head>

<body>

<div class="register-container">

    <div class="logo">
        OCEAN PEARL
        <span>V I L L A</span>
    </div>

    <div class="heading">
        <h1>Create Your Account</h1>
        <p>Join us and experience your perfect coastal escape.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label>Full Name</label>
            <input
                type="text"
                name="full_name"
                placeholder="Enter your full name"
                value="<?php echo htmlspecialchars($full_name); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input
                type="email"
                name="email"
                placeholder="Enter your email address"
                value="<?php echo htmlspecialchars($email); ?>"
                pattern="[^@\s]+@[^@\s]+\.[^@\s]+"
                title="Please enter a valid email address containing @"
                required
            >
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input
                type="tel"
                name="phone"
                placeholder="Enter your phone number"
                value="<?php echo htmlspecialchars($phone); ?>"
                maxlength="10"
                pattern="[0-9]{10}"
                inputmode="numeric"
                title="Enter exactly 10 digits"
                required
            >
        </div>

        <div class="form-group">
            <label>Password</label>
            <input
                type="password"
                name="password"
                placeholder="Create a password (minimum 8 characters)"
                minlength="8"
                required
            >
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input
                type="password"
                name="confirm_password"
                placeholder="Enter your password again"
                minlength="8"
                required
            >
        </div>

        <button type="submit" class="register-btn">
            Create Account
        </button>

    </form>

    <div class="login-link">
        Already have an account?
        <a href="login.php">Login</a>
    </div>

    <a href="../index.php" class="back-home">
        ← Back to Home
    </a>

</div>

</body>
</html>
