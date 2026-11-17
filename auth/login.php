
<?php
session_start();

require_once "../config/database.php";

$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Find guest account using email
        $stmt = $conn->prepare(
            "SELECT user_id, full_name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "admin") {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../guest/dashboard.php");
                }
                exit();

            } else {

                $error = "Incorrect email or password.";

            }

        } else {

            $error = "Incorrect email or password.";

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

<title>Guest Login | Ocean Pearl Villa</title>

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

.login-container {
    width: 100%;
    max-width: 480px;
    background: white;
    padding: 45px;
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
    margin: 32px 0;
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
    margin-bottom: 20px;
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
    padding: 15px;
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

.login-btn {
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

.login-btn:hover {
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

.register-link {
    text-align: center;
    margin-top: 25px;
    font-size: 14px;
    color: #71858a;
}

.register-link a {
    color: #087e91;
    text-decoration: none;
    font-weight: 700;
}

.register-link a:hover {
    text-decoration: underline;
}

.back-home {
    display: block;
    text-align: center;
    margin-top: 18px;
    font-size: 13px;
    color: #71858a;
    text-decoration: none;
}

.back-home:hover {
    color: #087e91;
}

@media (max-width: 500px) {
    .login-container {
        padding: 30px 22px;
    }

    .heading h1 {
        font-size: 26px;
    }
}

</style>
</head>

<body>

<div class="login-container">

    <div class="logo">
        OCEAN PEARL
        <span>V I L L A</span>
    </div>

    <div class="heading">
        <h1>Welcome Back</h1>
        <p>Login to manage your villa bookings.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label>Email Address</label>
            <input
                type="email"
                name="email"
                placeholder="Enter your email address"
                value="<?php echo htmlspecialchars($email); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Password</label>
            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >
        </div>

        <button type="submit" class="login-btn">
            Login to Account
        </button>

    </form>

    <div class="register-link">
        Don't have an account?
        <a href="register.php">Create Account</a>
    </div>

    <a href="../index.php" class="back-home">
        ← Back to Home
    </a>

</div>

</body>
</html>
