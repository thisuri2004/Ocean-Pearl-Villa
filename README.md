
# 🌊 Ocean Pearl Villa
### Dynamic Web-Based Coastal Villa and Room Booking System

Ocean Pearl Villa is a web-based villa management and room booking system designed to provide guests with a convenient way to explore rooms, check availability, and manage reservations. It also allows administrators to manage rooms and guest reservations efficiently.

## ✨ Features

### Guest Features
- Guest Registration and Login
- View Available Rooms
- Search Room Availability
- Book Rooms
- View My Bookings
- Cancel Pending Bookings
- View Booking Status

### Admin Features
- Admin Login
- Admin Dashboard
- Add, Edit and Delete Rooms
- Upload Room Images
- Manage Reservations
- Confirm and Cancel Bookings
- View Booking Information

### Booking Management
- Check Room Availability
- Prevent Duplicate Bookings
- Calculate Total Booking Price
- Validate Check-in and Check-out Dates
- Manage Booking Status

## 🛠️ Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- WAMP Server

## 💻 Software Requirements

- Operating System: Windows 10 or later
- Web Server: WAMP Server
- PHP: PHP 8.0 or later
- Database: MySQL
- Database Management: phpMyAdmin
- Code Editor: Visual Studio Code
- Web Browser: Google Chrome or Microsoft Edge

## 📁 Project Structure

```text
OceanPearlVilla/
│
├── admin/
├── auth/
├── config/
├── guest/
├── pages/
├── includes/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│       ├── villa/
│       ├── rooms/
│       ├── facilities/
│       └── gallery/
│
├── index.php
├── db_structure.sql
├── README.md
└── .gitignore
```

## ⚙️ Installation and Setup

1. Install WAMP Server.
2. Copy the OceanPearlVilla project folder into:

   `C:\wamp64\www\`

3. Start WAMP Server.
4. Open phpMyAdmin.
5. Create a database named:

   `ocean_pearl_villa`

6. Import the `db_structure.sql` file.
7. Configure the database connection in:

   `config/database.php`

8. Open the website in your browser:

   `http://localhost/OceanPearlVilla/`

**Note:** The `db_structure.sql` file includes database table structures and sample records for testing. Import it into a newly created database.

## 🔐 User Roles

### Guest
- Register and log in to the system.
- Explore available rooms.
- Search room availability.
- Make room reservations.
- View and manage pending bookings.

### Admin
- Log in to the administration panel.
- Manage room information.
- Upload room images.
- View and manage guest reservations.
- Confirm or cancel bookings.

## 🔑 Sample Login Credentials

### Guest Account

- Email: `guest.demo@oceanpearlvilla.com`
- Password: `Guest@123`

### Admin Account

- Email: `admin@oceanpearlvilla.com`
- Password: `Admin@123`

**Note:** These demo accounts are included in the sample data provided in `db_structure.sql`.

## 👩‍💻 Developer

**H.N.M.T.D. Rathnapriya**

## 📌 Project Purpose

This project was developed as an academic project to demonstrate web development concepts, database management, user authentication, room management, image uploading, and online room booking functionality.

---

© 2026 Ocean Pearl Villa. All Rights Reserved.
