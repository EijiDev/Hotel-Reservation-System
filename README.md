# 🏨 Hotel Reservation System  

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue?logo=php)](https://www.php.net/)  
[![MySQL](https://img.shields.io/badge/MySQL-Database-orange?logo=mysql)](https://www.mysql.com/)  
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)  
[![Status](https://img.shields.io/badge/Project-Active-success)](#)  

A full-stack **Hotel Reservation System** built by **EijiDev**.  
This project allows users to **browse rooms, make bookings, and admins to manage inventory, reservations, and users**.  

---

## 📑 Table of Contents
- [✨ Features](#-features)  
- [🛠 Tech Stack](#-tech-stack)  
- [🚀 Getting Started](#-getting-started)  
  - [Prerequisites](#prerequisites)  
  - [Installation](#installation)  
  - [Running the App](#running-the-app)  
- [📂 Project Structure](#-project-structure)  
- [📡 API Endpoints / Functionality](#-api-endpoints--functionality)  
- [🤝 Contributing](#-contributing)  
- [📜 License](#-license)  
- [📬 Contact](#-contact)  

---

## ✨ Features
✅ Browse available rooms (by type, price, availability)  
✅ Book a room for a given date range  
✅ User registration / login / authentication  
✅ Admin dashboard: manage rooms, reservations, and users  
✅ Booking history for users  
✅ Validation (dates, availability, permissions)  
✅ Responsive design for desktop + mobile  

---

## 🛠 Tech Stack
| Layer            | Technology            |
|------------------|-----------------------|
| **Backend**      | PHP 8+               |
| **Frontend**     | HTML / CSS / JavaScript |
| **Database**     | MySQL / MariaDB       |
| **Auth**         | PHP Sessions          |

---

## 🚀 Getting Started  

### ✅ Prerequisites
Make sure you have installed:  
- [PHP 8+](https://www.php.net/)  
- [Composer](https://getcomposer.org/)  
- [MySQL / MariaDB](https://www.mysql.com/)  
- [XAMPP / Laragon / WAMP]  

### 📥 Installation
```bash
# Clone repository
git clone https://github.com/yourusername/hotel-reservation-system.git
cd hotel-reservation-system

# Install dependencies
composer install
Import the hotel_reservation.sql file into MySQL.

Configure your .env file (DB connection, base URL, etc.).

▶️ Run the App
Using PHP’s built-in server:

bash
Copy code
php -S localhost:8000 -t app/public
Or place the project inside htdocs (if using XAMPP) and open:

perl
Copy code
http://localhost/hotel-reservation-system/app/public/
📂 Project Structure
bash
Copy code
Hotel_Reservation_System/
│── app/
│   ├── Controllers/    # Handles requests
│   ├── Models/         # Database models
│   ├── Views/          # HTML templates
│   ├── Config/         # DB connection
│   └── public/         # Assets (index.php, css, js, images)
│
├── vendor/             # Composer dependencies
├── .env                # Environment variables
├── composer.json
└── README.md


📡 API Endpoints / Functionality
GET /rooms → List all rooms

GET /room/{id} → Show room details

POST /booking → Create a booking

POST /login → User login

POST /register → User registration

GET /admin/dashboard → Admin panel

🤝 Contributing
Contributions are welcome!

Fork the repository

Create a feature branch (feature/my-feature)

Commit your changes

Push and create a Pull Request

📜 License
This project is licensed under the MIT License.

📬 Contact
👤 EijiDev

GitHub: @yourusername

Email: jaysolis697@gmail.com