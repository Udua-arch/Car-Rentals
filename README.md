# 🚗 Car Rental Management System

A **full-featured Car Rental web application** built with PHP, CSS, and MySQL.  
This project allows users to register, log in, browse available cars, view details, make bookings, and manage their profiles — while admins can add, edit, or delete cars from the inventory.  

It was developed as part of a **university coursework project**, and showcases both front-end and back-end development skills, including authentication, data persistence, and AJAX interactions.

---

## 🧠 Features Overview

### 🏠 Homepage
- Displays a list of all available cars with images and key attributes.
- Each car card links to a detailed page showing full specifications and availability.
- Includes filters for car brand, type, and availability date ranges.

### 🚘 Car Details Page
- Shows car attributes such as model, price, image, and description.
- Allows users to **book a car** for specific dates.
- Displays custom booking confirmation via **AJAX modal (no page reload)**.

### 👤 User Authentication
- Registration and login with full error handling.
- Successful login updates the interface to reflect user state.
- Secure **logout** available from the profile and other pages.

### 📅 Bookings
- Users can select only **available dates** for booking (integrated with a calendar view).
- Successful bookings are saved in the database.
- Booking details and feedback appear dynamically on the same page.

### 🧾 Profile Page
- Displays user information and booking history.
- Allows users to manage or review past bookings.

### 🧑‍💼 Admin Dashboard
- Add new cars with validation and error handling.
- Edit or delete car records.
- View all cars and manage inventory.
- No login required for demo purposes (for ease of testing).

### 💄 Design & UX
- Clean, mobile-friendly interface using custom CSS.
- Responsive layout suitable for all screen sizes.
- Dynamic feedback and modal confirmations using AJAX.

---

## 🛠️ Tech Stack

| Component | Technology |
|------------|-------------|
| **Frontend** | HTML5, CSS3, JavaScript (AJAX) |
| **Backend** | PHP (Procedural) |
| **Database** | MySQL |
| **Server** | XAMPP / Apache |
| **Version Control** | Git & GitHub |


