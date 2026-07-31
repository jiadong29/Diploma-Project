1. Download and launch XAMPP. Download and install XAMPP from the official website (it includes Apache, PHP, MySQL, and phpMyAdmin). After installation, open the XAMPP Control Panel. Click the Start button next to Apache and MySQL, ensuring both backgrounds turn green (indicating running status).

2. Place project files. Copy your project folder to the XAMPP root directory, typically C:\xampp\htdocs\Online_Bill_Payment_System

3. Initialize the database with one click. The project code includes a built-in database initialization script, init_db.php! Open your browser, enter and run: http://localhost/Online_Bill_Payment_System/init_db.php. After running, this script will automatically create a database named Online_Bill_Payment_System in your MySQL database and establish the required four tables (users, credit_cards, merchants, bills).

4. Once the project database is successfully established, you can access your project homepage in your browser: http://localhost/Online_Bill_Payment_System/Project.php. At this point, functions such as login, registration, information modification, and bill payment should all be back to normal.
