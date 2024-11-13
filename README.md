# Stock Quote API

This project is a PHP application built with Symfony 6.1.12 that provides an endpoint to receive stock quotes for a given company symbol within a specified date range, and sends the historical data as a CSV attachment to the provided email.

## Features

- Validate user inputs (company symbol, start date, end date, and email)
- Fetch historical stock quotes using the Yahoo Finance API
- Send an email with the stock quote data as a CSV attachment
- Comprehensive test coverage
- OpenAPI documentation
- Docker support for easy setup and deployment

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Docker](#docker)
- [License](#license)

## Prerequisites

- PHP 8.1 or higher
- Composer
- Symfony CLI
- Docker (optional, for containerized setup)

## Installation

1. Clone the repository:

   ```sh
   git clone <repository url>
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

   Copy `.env` to `.env.local` and configure the necessary variables, including your RapidAPI key:

   ```dotenv
   APP_ENV=dev
   APP_SECRET=9b76bc4c9e40f165a2b731c325c8793d

   DATABASE_URL=pgsql://postgres:password@db:5432/app

   MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
   MAILER_DSN=smtp://api:95ac6de7b68bf6a0412d99650805bb34@live.smtp.mailtrap.io:587
   RAPIDAPI_KEY=c29c584041msh773438672c09cc8p1cfb0fjsn092c60152115
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
   - pgAdmin: http://127.0.0.1:5432 (Login with admin@admin.com and admin)

## Configuration

Ensure the following environment variables are set in your `.env.local`:

- `APP_ENV`: The environment in which the application runs (e.g., `dev` or `prod`)
- `APP_SECRET`: A secret key used by Symfony
- `MAILER_DSN`: The DSN for the mailer service
- `RAPIDAPI_KEY`: Your RapidAPI key for accessing the Yahoo Finance API

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

## License

This project is licensed under the MIT License.
