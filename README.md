# ATIS_GENERATOR

Rudimentary instructions to get started:
1. Download Code
2. Import redbbqhz_atis_generator.sql using your preferred method into MySQL. This file includes ICAO codes and associated airport names.
3. Open /includes/constants.php in a text editor and change the MySQL database connection information to your database
4. Upload files to your server.
5. Profit.

Advanced Instructions:
1. Download Code
2. MariaDB
	1. Create database
		1. name: redbeard-atis-generator
	2. Create atis-generator user
		1. username: redbeard_atis_generator(or whatever works for you)
		2. password: (create secure password or have mariaDB generate it for you)
		3. permissions: only enable SELECT for the database: redbeard-atis-generator
	3. Import redbbqhz_atis_generator.sql using your preferred method into MySQL. This file includes ICAO codes and associated airport names.
3. Open /includes/constants.php in a text editor and change the database connection information to your database and user info

Requirments:
1. PHP: version 8.1 or greater
2. MYSQL: My current server setup is running 10.3.36-MariaDB-log-cll-lve
3. cURL: is used to fetch weather info, so this function must be enabled.

Basic rules:
1. Credit me for any use.
2. Do NOT, and I mean DO NOT charge for this, or put it behind a paywall.
