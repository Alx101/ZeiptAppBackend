# Zeipt App Backend
This is the backend portion of the demo application showcasing the Zeipt API.
This backend is built on the php framework Slim 3, coupled with Phinx for database migrations
and Eloquent for databse interaction. It is set up to use SQLite by default.

You can get the companion app [here](https://github.com/Alx101/ZeiptIonicApp).

## Dependencies
This project assumes a correct setup of [composer](https://getcomposer.org).

## Installation
This assumes you're working in bash.

#### Install dependencies
Clone this repository to the desired directory. Navigate to it and run `composer install`.

#### Set up database
- Navigate to the db directory and create a file called `database.sqlite`
- Go back to the root and run `vendor/bin/phinx migrate`

## Running
With the database set up and the vendor folder populated you can navigate to
the public directory and run `php -S localhost:8000` to run the server.

Then in a browser, go to `http://localhost:8000` and you should see a 
list of the routes available.

