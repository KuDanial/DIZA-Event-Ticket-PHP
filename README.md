# 🎫 DIZA Event Ticket - PHP Ticketing & Management Portal

<p align="center">
<img src="./images/DIZAET_Logo.png" alt="DIZAET Logo" width="500">
</p>

A relational, database-driven event ticket booking and administration portal prototype. This system acts as a public storefront for browsing and booking ticketing routines while offering a restricted, role-based workspace for event **Organizers** to publish events and track sales, and **HQ Administrators** to manage users, events, and generate operational analytics reports.

[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://html.spec.whatwg.org/)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://www.w3.org/Style/CSS/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![XAMPP](https://img.shields.io/badge/XAMPP-FB3E44?style=for-the-badge&logo=xampp&logoColor=white)](https://www.apachefriends.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-purple?style=for-the-badge)](https://opensource.org/licenses/MIT)
[![Status: Complete](https://img.shields.io/badge/Status-Complete-008f7a?style=for-the-badge)](#)

---

## 🚀 Key Features

### 1. 🌐 Public Customer Storefront
* **Event Search & Filter:** Filter by search keyword, location (Kuala Lumpur, kelantan, online, etc.), and Category (Concert, Seminar, Sports, Workshop).
* **Trending Events Grid:** Responsive event lists displaying custom uploaded banner images, ticket prices, dates, and venue capacity.
* **Seamless Booking flow:** Guided FPX/eWallet payment simulation forms with dynamic pricing calculations.

### 2. 🎟️ Attendee Workspace
* **Booking & Ticket History:** Full transaction logs displaying status (confirmed, past) and student details.
* **Print Invoice & QR Code:** Action triggers to simulate invoice downloads and retrieve booking QR codes.
* **Account Settings:** Self-service profile updates (Gender, Student ID, Phone Number) and secure MD5 password modifications.

### 3. 💼 Organizer Dashboard
* **Real-time Event Creation:** Multi-step event creation module supporting dynamic image/banner uploads.
* **Tickets Volume Analytics:** Interactive visual charts powered by **Chart.js** displaying confirmed bookings per event.
* **Print Reports:** Custom print styles automatically clean the screen for formal paper/PDF business reports.

### 4. 🔐 System Administration Panel
* **Operational Demographics:** Visual distribution bar charts of active platform roles (Attendees vs Organizers).
* **Payment Preference Analytics:** Relational doughnut charts displaying transaction channels (FPX vs eWallet).
* **System Modulators:** Full role-based CRUD management for User Accounts (Admins, Attendees, Organizers) and Events.

---

## 🛠️ Technology Stack

* **Structure:** Semantic HTML5
* **Styling:** Custom Vanilla CSS3 with premium Glassmorphism and responsive design tokens.
* **Interactions & Analytics:** Chart.js CDN, Pure ES6+ JavaScript client controllers.
* **Backend Engine:** Relational PHP 8+ procedural database queries.
* **Database & Security:** MySQL (relational indexing) and MD5 authentication hashing.

---

## 📁 Project Structure

```bash
DIZA-Event-Ticket-PHP/
├── index.php                 # Public Homepage & Trending Events
├── events.php                # Full Categorized Events List
├── booking.php               # Ticket Booking Transaction Form
├── tickets.php               # Attendee Booking Logs & QR Codes
├── login.php                 # Central Role-Based Authorization
├── register.php              # Attendee Sign-Up Form
├── admin-dashboard.php       # HQ System Administration Dashboard
├── organizer-dashboard.php   # Organizer Ticketing Sales Dashboard
├── connection.php            # Relational MySQL DB Connection Handler
├── diza_ticketing.sql        # MySQL Database Schema Import File
├── css/
│   ├── style.css             # Main Customer Styling Rules
│   └── styledashboard.css    # Admin/Organizer Portal Styles
├── images/                   # Asset logos, icons, and uploaded banners
└── README.md                 # Project Documentation & Badges
```

---

## ⚙️ How to Run Locally

### Prerequisites:
Make sure you have **XAMPP** installed on your system.

### Steps:
1. Clone or copy the project folder to your XAMPP htdocs directory:
   ```bash
   C:\\xampp\\htdocs\\DIZA-Event-Ticket-PHP
   ```
2. Start the **Apache** and **MySQL** modules inside the XAMPP Control Panel.
3. Open PHPMyAdmin (`http://localhost/phpmyadmin`) in your browser.
4. Create a new database named **`diza_ticketing_db`**.
5. Import the database schema file **`diza_ticketing.sql`** located in the root of the project.
6. Open your browser and navigate to:
   ```text
   http://localhost/DIZA-Event-Ticket-PHP
   ```

---

## 🔑 Test Credentials & Access Codes

Use the default password **`1234`** (or matching MD5 hash) to log in as any role:

| Profile Name | Username | Assigned Role | Access Level | Scope / Workspace |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `admin` | Administrator | System-wide | HQ Management / System Audit |
| **Tech Organizer** | `org_tech` | Organizer | Partner Outlet | Tech Giants Corp Event Manager |
| **Tengku Danial** | `Danial` | Attendee | Student / User | Personal Ticket Purchases |

---
*Developed for academic evaluation under course code ICT608 (Semester March - August 2026).*
