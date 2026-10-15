
# VetoraLuxe

VetoraLuxe is a veterinary clinic management system built with PHP and MySQL.

## Technologies
- PHP 8.2
- Apache
- MySQL 8.0
- Docker Compose

## Requirements
- Docker Desktop

## How to Run
1. Clone this repository.
2. Open the project folder in a terminal.
3. Run:

   docker compose up --build -d

4. Open http://localhost:8080 in your browser.

## Stop the Application
Run:

   docker compose down

## Database
The database is initialized from `vetora_db.sql` on the first startup and stored in a Docker volume.

## Default Admin Login
- Email: admin@kiyowo.id
- Password: admin123