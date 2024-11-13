# XM-Stock Quote

This project provides an endpoint to receive stock quotes for a given company symbol within a specified date range, and sends the historical data as a CSV attachment to the provided email.

## Features

- Validate user inputs (company symbol, start date, end date, and email)
- Fetch historical stock quotes using the Yahoo Finance API
- Send an email with the stock quote data as a CSV attachment
- Comprehensive test coverage
- OpenAPI documentation
- Docker support for easy setup and deployment

## Prerequisites

- PHP 8.1 or higher
- Composer
- Symfony CLI
- Docker (optional, for containerized setup)

## Installation

1. Clone the repository:

   ```sh
   git clone https://github.com/deliteser112/stock-quote-php-symfony.git
   cd Stock-Quote-App
   ```

2. Install dependencies:

   ```sh
   composer install
   ```

   Or using Docker

   ```sh
   docker-compose up --build -d
   ```

3. Set up environment variables:

   ```dotenv
   DATABASE_URL=pgsql://postgres:postgres@localhost:5432/stocks
   MAILER_DSN=smtp://3906f354976114:2f5edb86adad7b@sandbox.smtp.mailtrap.io:2525
   RAPIDAPI_KEY=7dcca4844emsh2f9244cca3fe0fdp1c26b8jsnc82d7a417bae
   RAPIDAPI_URL=https://yh-finance.p.rapidapi.com/stock/v3/get-historical-data
   ```

4. Initialize the Database:

   Connect to the php container and run the Symfony migration command to set up the database schema.

   ```sh
   php bin/console doctrine:migrations:migrate
   ```

   Or using Docker

   ```sh
   docker-compose exec php bash
   php bin/console doctrine:migrations:migrate
   ```

5. Access the Application:

   - Symfony application: http://127.0.0.1:8000

## Configuration

Ensure the following environment variables are set in your `.env.local`:

- `MAILER_DSN`: The DSN for the mailer service
- `RAPIDAPI_KEY`: Your RapidAPI key for accessing the Yahoo Finance API
- `RAPIDAPI_URL`: Your RapidAPI url for accessing the Yahoo Finance API

## Running the Application

To start the application locally:

```sh
symfony server:start
```

## Testing

Run the tests to ensure 100% code coverage:

```sh
php bin/phpunit
```

## API Documentation

The API is documented using OpenAPI. To view the documentation:

- Start the application.
- Navigate to http://127.0.0.1:8000/api/doc.
