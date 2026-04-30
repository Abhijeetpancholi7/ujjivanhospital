# Ujjivan Hospital Indoor Patient Register

Ujjivan Hospital Indoor Patient Register is a web-based patient admission register for managing indoor patient records at Ujjivan Hospital, NTPC Dadri. It replaces a manual register workflow with a searchable, printable, database-backed interface.

## Features

- Add multiple indoor patient admission rows in one screen.
- Save patient records to a MySQL database.
- View saved records with date, patient name, employee number, diagnosis, doctor, and keyword filters.
- Edit and delete existing admission records.
- Duplicate rows for faster repeated data entry.
- Save and print the register sheet.
- Print-friendly register layout.
- Basic validation for required fields such as admission date, patient name, age, sex, and diagnosis.

## Tech Stack

- PHP
- MySQL
- JavaScript
- Bootstrap 5
- Bootstrap Icons
- HTML and CSS

## Project Structure

```text
api/                    PHP API endpoints for save, fetch, update, and delete
assets/css/style.css     Application styling and print layout
assets/js/app.js         Frontend register logic
config/database.php      MySQL database connection
database.sql             Database and table setup script
index.php                Main indoor patient register page
```

## Setup

1. Copy or clone the project into your XAMPP `htdocs` folder.
2. Start Apache and MySQL from the XAMPP control panel.
3. Import `database.sql` into MySQL using phpMyAdmin or the MySQL command line.
4. Update `config/database.php` if your MySQL database name, user, password, or host are different.
5. Open the project in a browser through your local XAMPP URL.

## Database

The application uses the `clinic` database and the `indoor_patient_register` table. The table stores admission number, admission date and time, employee number, patient details, diagnosis, department fields, staff nurse, doctor name, remarks, and timestamps.

## Purpose

This project helps hospital staff maintain indoor patient admission records in a cleaner digital format while keeping the familiar register-style layout for daily use and printing.
