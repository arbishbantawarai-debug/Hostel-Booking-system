# Hostel Booking System

A PHP-based hostel booking system that allows users to search for rooms, check availability, and manage reservations.

## Folder Structure

### Root Directory
- **database_schema.sql** - Database schema and initial setup script
- **README.md** - Project documentation

### `/assets`
Frontend static files for styling and client-side functionality
- **css/** - Stylesheets
  - `style.css` - Main stylesheet for the application
- **js/** - JavaScript files
  - `app.js` - Client-side application logic

### `/config`
Application configuration files
- **db.php** - Database connection configuration

### `/includes`
Reusable PHP components and utilities
- **header.php** - Page header template
- **footer.php** - Page footer template
- **functions.php** - Common PHP functions and utilities

### `/public`
Main application pages and user-facing functionality
- **index.php** - Home/landing page
- **login.php** - User login page
- **logout.php** - User logout handler
- **register.php** - User registration page
- **add.php** - Add new room/booking
- **edit.php** - Edit booking or room details
- **delete.php** - Delete booking or room
- **search.php** - Search results page
- **search-rooms.php** - Room search functionality
- **check-availability.php** - Check room availability
- **room-price.php** - Room pricing information
- **AI/** - AI-related features (currently empty)

## Getting Started

1. Import `database_schema.sql` into your database
2. Configure database connection in `config/db.php`
3. Access the application through `public/index.php`

## Features

- User authentication (login/register)
- Room search and availability checking
- Booking management (add, edit, delete)
- Room pricing information
- Dynamic room availability
