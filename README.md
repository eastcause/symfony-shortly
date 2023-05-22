# Symfony-Shortly

## Description

Symfony-Shortly is a project built on the Symfony framework that allows users to shorten long URLs. Both logged-in and anonymous users can utilize this functionality. If a user is logged in, the shortened link will be saved in their history. The website also includes a Symfony command that enables the creation of an administrator account capable of deleting other users' links.

## Requirements

To run the Symfony-Shortly project, you need to have the following tools installed:

- PHP (recommended version: 7.4 or higher)
- Composer
- Symfony CLI

## Installation

1. Clone the project repository:

   ```
   git clone https://github.com/eastcause/symfony-shortly.git
   ```
2. Configure the database connection in the `.env` file.
3. Run the migrations to create the database schema:
   ```
   php bin/console doctrine:migrations:migrate
   ```
   
## Usage

After successfully running the project, you can utilize the URL shortening feature on the homepage. Logged-in users will have access to their history of shortened links.

To create an administrator account, use the Symfony command:

    php bin/console app:create-user

Once an administrator account is created, you can log in and start managing user links.
