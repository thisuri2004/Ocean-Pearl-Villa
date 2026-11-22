
<?php
require_once "config/database.php";

$roomImages = [
    "https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=85",
    "https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=900&q=85",
    "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=900&q=85"
];

$rooms = [];
$result = $conn->query(
    "SELECT room_id, room_name, room_type, description,
            price_per_night, capacity, image
     FROM rooms
     WHERE status = 'available'
     ORDER BY room_id ASC
     LIMIT 3"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Ocean Pearl Villa | Luxury Coastal Escape</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'DM Sans', sans-serif;
    color: #183c48;
    background: #ffffff;
}

:root {
    --ocean: #087e91;
    --deep: #073b55;
    --aqua: #09b6bd;
    --light: #f2f9fa;
}

/* NAVIGATION */

header {
    width: 100%;
    min-height: 88px;
    padding: 15px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    position: relative;
    z-index: 10;
}

.logo {
    font-family: 'Playfair Display', serif;
    color: var(--deep);
    font-size: 27px;
    font-weight: 700;
    letter-spacing: 1px;
}

.logo span {
    display: block;
    text-align: center;
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    letter-spacing: 7px;
    color: var(--ocean);
}

nav {
    display: flex;
    align-items: center;
    gap: 30px;
}

nav a {
    text-decoration: none;
    color: #183c48;
    font-size: 14px;
    font-weight: 600;
    transition: .3s;
}

nav a:hover {
    color: var(--aqua);
}

nav .book-btn {
    background: var(--ocean);
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
}

nav .book-btn:hover {
    background: var(--deep);
    color: white;
}

/* HERO */

.hero {
    min-height: 650px;
    display: flex;
    align-items: center;
    padding: 80px 8%;
    background:
        linear-gradient(90deg,
        rgba(3, 36, 57, .78) 0%,
        rgba(3, 36, 57, .40) 48%,
        rgba(3, 36, 57, .05) 100%),
        url('assets/images/villa/ocean-pearl-hero.png');

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.hero-content {
    max-width: 750px;
    color: white;
}

.hero-tag {
    color: #c3f5f3;
    font-size: 13px;
    letter-spacing: 5px;
    font-weight: 700;
    margin-bottom: 25px;
}

.hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(43px, 6vw, 76px);
    line-height: 1.12;
    margin-bottom: 25px;
}

.hero h1 span {
    color: #f5c66c;
    font-style: italic;
}

.hero p {
    font-size: 16px;
    line-height: 1.9;
    max-width: 590px;
    color: #f1f8fa;
    margin-bottom: 32px;
}

.hero-buttons {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
}

.primary-btn {
    display: inline-block;
    padding: 16px 28px;
    background: var(--aqua);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 700;
    transition: .3s;
}

.primary-btn:hover {
    background: var(--ocean);
    transform: translateY(-3px);
}

.secondary-btn {
    display: inline-block;
    padding: 15px 27px;
    border: 1px solid white;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: .3s;
}

.secondary-btn:hover {
    background: white;
    color: var(--deep);
}

/* FEATURES */

.features {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    padding: 35px 7%;
    background: #f4fbfc;
    gap: 20px;
}

.feature {
    text-align: center;
    padding: 15px;
    border-right: 1px solid #d2e6e9;
}

.feature:last-child {
    border-right: none;
}

.feature .icon {
    font-size: 30px;
    margin-bottom: 12px;
}

.feature h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    color: var(--deep);
    margin-bottom: 7px;
}

.feature p {
    color: #718b92;
    font-size: 13px;
}

/* ABOUT */

.about {
    padding: 90px 9%;
    text-align: center;
}

.section-tag {
    color: var(--ocean);
    font-size: 12px;
    letter-spacing: 4px;
    font-weight: 800;
    text-transform: uppercase;
}

.about h2,
.rooms h2 {
    font-family: 'Playfair Display', serif;
    font-size: 42px;
    color: var(--deep);
    margin: 16px 0 20px;
}

.about p {
    max-width: 780px;
    margin: auto;
    color: #71858a;
    font-size: 15px;
    line-height: 2;
}

/* ROOMS */

.rooms {
    padding: 85px 8%;
    background: var(--light);
    text-align: center;
}

.room-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    margin-top: 40px;
    text-align: left;
}

.room-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(12, 58, 73, .08);
    transition: .3s;
}

.room-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 14px 35px rgba(12, 58, 73, .14);
}

.room-card img {
    width: 100%;
    height: 230px;
    object-fit: cover;
}

.room-info {
    padding: 24px;
}

.room-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: var(--deep);
    margin-bottom: 12px;
}

.room-info p {
    color: #74888d;
    font-size: 14px;
    line-height: 1.8;
    min-height: 70px;
}

.room-meta {
    color: #68828a;
    font-size: 13px;
    margin-top: 15px;
}

.price {
    color: var(--ocean);
    font-size: 18px;
    font-weight: 800;
    margin-top: 15px;
}

.room-btn {
    display: inline-block;
    margin-top: 17px;
    padding: 11px 18px;
    border-radius: 5px;
    background: var(--deep);
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

/* BOOKING BANNER */

.booking-banner {
    padding: 75px 8%;
    text-align: center;
    color: white;
    background: linear-gradient(120deg, #06465e, #078d9b);
}

.booking-banner h2 {
    font-family: 'Playfair Display', serif;
    font-size: 38px;
    margin-bottom: 15px;
}

.booking-banner p {
    color: #d7f1f1;
    margin-bottom: 28px;
}

.booking-banner a {
    display: inline-block;
    padding: 15px 28px;
    border-radius: 5px;
    color: var(--deep);
    background: white;
    text-decoration: none;
    font-weight: 700;
}

/* FOOTER */

footer {
    padding: 35px 8%;
    background: #082e40;
    text-align: center;
    color: #d3e6e9;
    font-size: 13px;
    line-height: 2;
}

footer strong {
    font-family: 'Playfair Display', serif;
    font-size: 21px;
    color: white;
}

/* RESPONSIVE */

@media (max-width: 900px) {
    header {
        flex-direction: column;
        gap: 20px;
    }

    nav {
        justify-content: center;
        flex-wrap: wrap;
        gap: 18px;
    }

    .room-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .features {
        grid-template-columns: repeat(2, 1fr);
    }

    .feature:nth-child(2) {
        border-right: none;
    }
}

@media (max-width: 600px) {
    .hero {
        min-height: 570px;
        padding: 55px 7%;
        background-position: center;
    }

    .hero h1 {
        font-size: 43px;
    }

    .hero-tag {
        font-size: 11px;
        letter-spacing: 3px;
    }

    .room-grid {
        grid-template-columns: 1fr;
    }

    .features {
        grid-template-columns: 1fr 1fr;
        padding: 25px 5%;
    }

    .feature {
        border-right: none;
    }

    .about h2,
    .rooms h2,
    .booking-banner h2 {
        font-size: 30px;
    }

    nav {
        gap: 14px;
    }

    nav a {
        font-size: 12px;
    }
}
</style>
</head>

<body>

<!-- NAVIGATION -->

<header>
    <div class="logo">
        OCEAN PEARL
        <span>V I L L A</span>
    </div>

    
<nav>
    <a href="index.php">Home</a>
    <a href="#about">About</a>
    <a href="#rooms">Our Rooms</a>
    <a href="pages/availability.php">Check Availability</a>
    <a href="auth/login.php">Login</a>
    <a href="auth/register.php" class="book-btn">Book Now</a>
</nav>

</header>

<!-- HERO SECTION -->

<section class="hero">
    <div class="hero-content">

        <div class="hero-tag">
            WELCOME TO OCEAN PEARL VILLA
        </div>

        <h1>
            Discover Your<br>
            <span>Perfect</span> Coastal Escape
        </h1>

        <p>
            Experience the beauty of the Sri Lankan coast.
            Relax in comfort, enjoy breathtaking ocean views
            and create unforgettable memories by the sea.
        </p>

        <div class="hero-buttons">
            <a href="#rooms" class="primary-btn">
                Explore Our Rooms →
            </a>

            <a href="#about" class="secondary-btn">
                Discover More
            </a>
        </div>

    </div>
</section>

<!-- FEATURES -->

<section class="features">

    <div class="feature">
        <div class="icon">🌴</div>
        <h3>Beautiful Location</h3>
        <p>Steps from the beach</p>
    </div>

    <div class="feature">
        <div class="icon">💎</div>
        <h3>Luxury Rooms</h3>
        <p>Comfort Redefined</p>
    </div>

    <div class="feature">
        <div class="icon">🛡️</div>
        <h3>Secure Booking</h3>
        <p>Your Safety Our Priority</p>
    </div>

    <div class="feature">
        <div class="icon">🎧</div>
        <h3>24/7 Support</h3>
        <p>Always Here for You</p>
    </div>

</section>

<!-- ABOUT -->

<section class="about" id="about">

    <div class="section-tag">A Beautiful Escape</div>

    <h2>Welcome to Ocean Pearl Villa</h2>

    <p>
        Escape from everyday life and discover a peaceful
        coastal retreat. Ocean Pearl Villa offers comfortable
        accommodation, beautiful surroundings and a relaxing
        experience for every guest. Enjoy the ocean breeze,
        explore the beautiful coast and make unforgettable
        memories with your loved ones.
    </p>

</section>

<!-- ROOMS -->

<section class="rooms" id="rooms">

    <div class="section-tag">Stay With Comfort</div>

    <h2>Explore Our Accommodation</h2>

    <div class="room-grid">

        <?php if (count($rooms) > 0): ?>

            <?php foreach ($rooms as $index => $room): ?>

                <?php
                $image = $roomImages[$index % count($roomImages)];

                if (!empty($room['image'])) {
                    $localImage = "assets/images/rooms/" . basename($room['image']);

                    if (file_exists(__DIR__ . "/" . $localImage)) {
                        $image = $localImage;
                    }
                }
                ?>

                <div class="room-card">

                    <img
                        src="<?php echo htmlspecialchars($image); ?>"
                        alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                    >

                    <div class="room-info">

                        <h3>
                            <?php echo htmlspecialchars($room['room_name']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($room['description'] ?? 'Enjoy a comfortable and relaxing stay at Ocean Pearl Villa.'); ?>
                        </p>

                        <div class="room-meta">
                            <?php echo htmlspecialchars($room['room_type']); ?>
                            ·
                            Up to <?php echo (int)$room['capacity']; ?> Guests
                        </div>

                        <div class="price">
                            LKR <?php echo number_format((float)$room['price_per_night'], 2); ?>
                            / Night
                        </div>

                        <a href="auth/register.php" class="room-btn">
                            Book This Room
                        </a>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No rooms are currently available.</p>

        <?php endif; ?>

    </div>

</section>

<!-- BOOKING CALL TO ACTION -->

<section class="booking-banner">

    <h2>Your Coastal Getaway Awaits</h2>

    <p>
        Make your next holiday a memorable experience
        at Ocean Pearl Villa.
    </p>

    <a href="auth/register.php">
        Reserve Your Stay
    </a>

</section>

<!-- FOOTER -->

<footer>

    <strong>OCEAN PEARL VILLA</strong>

    <p>Experience the beauty of the Sri Lankan coast.</p>

    <p>© 2026 Ocean Pearl Villa. All Rights Reserved.</p>

</footer>

</body>
</html>
