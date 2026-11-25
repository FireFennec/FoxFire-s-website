# FoxFire's Website

A personal web application built with Symfony, designed to serve as a developer portfolio and a platform for developing practical web application features.

**Live website:** https://foxfireworld.com

## Project Overview

This project is an independently developed web application that combines a personal portfolio with practical functionality. Its purpose is to demonstrate my experience with PHP and Symfony while continuously improving my skills in backend development, database management, application security, frontend development, and automated testing.

The application is actively maintained and gradually expanded with new features, improvements, and bug fixes.

## Key Features

### Personal Portfolio

- Personal introduction and contact information.
- Overview of technical skills, experience, and proficiency levels.
- Responsive user interface with custom styling.

### Website Development Roadmap

- Overview of planned, ongoing, and completed website features.
- Feature priorities, statuses, and deadlines.
- Database-driven feature management.
- Pagination of feature listings.

### Cooking Recipes

- Database-driven recipe collection.
- Ingredient lists and cooking instructions.
- Dedicated recipe pages.

### User Authentication and Security

- User registration and authentication.
- Email verification.
- Secure password hashing.
- CSRF protection.
- Login rate limiting.
- Role-based access control.
- Terms and conditions acceptance tracking.
- User account management.

### Administration

- Administration interface powered by EasyAdmin.
- User and role management.
- Protection of privileged user accounts.
- Restricted access based on user permissions.

### D&D Encounter Tracker

An interactive combat management tool designed for Dungeons & Dragons sessions.

- Management of allied and enemy combatants.
- Health point tracking with visual health bars.
- Damage and healing management.
- Initiative tracking.
- Dynamic interface updates without full page reloads.
- Separate permissions for Dungeon Masters and players.
- Encounter reset functionality.

## Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8.5 | Backend development |
| Symfony 7.4 LTS | Application framework |
| Doctrine ORM 3.6 | Object-relational mapping |
| PostgreSQL 16 | Relational database |
| Twig | Template rendering |
| Tailwind CSS 4.1 | Frontend styling |
| JavaScript | Client-side functionality |
| Symfony UX Turbo | Enhanced navigation and interactions |
| Stimulus | JavaScript integration |
| Symfony AssetMapper | Frontend asset management |
| EasyAdmin | Administration interface |
| Pagerfanta | Pagination |
| Symfony Mailer | Email delivery |
| PHPUnit | Automated testing |
| DAMA Doctrine Test Bundle | Database test isolation |
| PHPStan | Static code analysis |
| PHP CS Fixer | Code style consistency |
| Composer | Dependency management |
| Git and GitHub | Version control |

## Application Architecture

The application follows Symfony's MVC architecture and separates application responsibilities into controllers, entities, repositories, forms, services, security components, and templates.

The project uses dependency injection, Doctrine entity mapping, database migrations, Symfony Forms, validation constraints, and role-based authorization.

## Project Structure

| Directory | Description |
|---|---|
| `assets/` | JavaScript, stylesheets, and frontend assets |
| `bin/` | Symfony console and executable scripts |
| `config/` | Application and package configuration |
| `migrations/` | Doctrine database migrations |
| `public/` | Public entry point and accessible assets |
| `src/Controller/` | HTTP request handling |
| `src/Entity/` | Doctrine entities |
| `src/Repository/` | Database queries and data retrieval |
| `src/Form/` | Symfony form definitions |
| `src/Security/` | Authentication and security components |
| `src/Service/` | Application services |
| `templates/` | Twig templates |
| `tests/` | Automated tests |

## System Requirements

Before installing the application, make sure the following software is available:

- PHP 8.5 recommended.
- Composer 2.x.
- PostgreSQL 16.
- Git.
- Symfony CLI recommended.
- PHP extensions required by Composer dependencies, including PDO PostgreSQL, Ctype, Iconv, XML, DOM, and Mbstring.
- An SMTP server or local email testing service for email verification.

Node.js and npm are not required for the standard frontend build because the project uses Symfony AssetMapper and SymfonyCasts Tailwind Bundle.

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/FireFennec/FoxFire-s-website.git
cd FoxFire-s-website
```

### 2. Install dependencies

```bash
composer install
```

Verify that the installed PHP version and extensions satisfy the project requirements:

```bash
composer check-platform-reqs
```

### 3. Configure environment variables

Create a `.env.local` file in the root directory of the project.

```dotenv
APP_ENV=dev
APP_SECRET=replace_with_a_secure_random_secret

DATABASE_URL="postgresql://app:password@127.0.0.1:5432/app?serverVersion=16&charset=utf8"

MAILER_DSN=smtp://127.0.0.1:1025
APP_CONTACT_EMAIL=contact@example.com
```

Replace the example database credentials and application secret with your own values.

Local environment configuration and sensitive credentials must not be committed to version control.

### 4. Set up the database

Make sure PostgreSQL is running and the configured database user has the required permissions.

Create the database:

```bash
php bin/console doctrine:database:create
```

Execute the database migrations:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 5. Prepare frontend assets

Install Importmap dependencies:

```bash
php bin/console importmap:install
```

Build Tailwind CSS:

```bash
php bin/console tailwind:build
```

During frontend development, Tailwind can also run in watch mode:

```bash
php bin/console tailwind:build --watch
```

## Running the Application

Start the local Symfony development server:

```bash
symfony server:start
```

The application will normally be available at:

http://127.0.0.1:8000

The actual address is displayed in the terminal when the server starts.

For registration and email verification, ensure that the SMTP service configured in `MAILER_DSN` is running.

## Automated Testing

The project includes automated tests covering controllers, entities, repositories, services, validation, authentication, authorization, and application functionality.

PHPUnit is used as the testing framework, together with Symfony's testing tools and DAMA Doctrine Test Bundle.

### Prepare the test database

The test environment uses a separate database with the `_test` suffix, as configured in Doctrine.

Create the test database:

```bash
php bin/console doctrine:database:create --env=test
```

Execute migrations:

```bash
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

### Run the complete test suite

```bash
php bin/phpunit
```

### Run a specific test class

```bash
php bin/phpunit tests/Controller/SecurityControllerTest.php
```

### Run controller tests

```bash
php bin/phpunit tests/Controller
```

### Run entity tests

```bash
php bin/phpunit tests/Entity
```

Database changes made during supported tests are isolated using DAMA Doctrine Test Bundle.

## Code Quality

The project uses PHPStan for static analysis and PHP CS Fixer for code style consistency.

Run static analysis:

```bash
vendor/bin/phpstan analyse
```

Check Twig template syntax:

```bash
php bin/console lint:twig templates
```

Check YAML configuration syntax:

```bash
php bin/console lint:yaml config
```

Validate Doctrine mappings and database schema:

```bash
php bin/console doctrine:schema:validate
```

## Production Deployment

The application is deployed with Symfony's production environment enabled.

Typical deployment steps include installing production dependencies, executing database migrations when required, building frontend assets, and refreshing the application cache.

```bash
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
php bin/console tailwind:build --minify --env=prod
php bin/console asset-map:compile --env=prod
php bin/console cache:clear --env=prod
```

Production credentials and environment variables must be configured separately and securely.

## Development Workflow

Development is organized using Git branches and GitHub pull requests.

New features, bug fixes, refactoring, and documentation updates are developed in separate branches before being merged into the main branch.

Automated tests and relevant application checks are used to verify changes before production deployment.

## Project Status

**Active development**

The application is continuously maintained and expanded. Future improvements focus on new functionality, application security, code quality, automated testing, and user experience.

## Author

Developed and maintained by FireFennec.

- Website: https://foxfireworld.com
- Technical skills: https://foxfireworld.com/skills
- GitHub: https://github.com/FireFennec

## License

This project is proprietary. The source code is publicly available for portfolio and review purposes. No permission to redistribute or commercially use the project is granted unless explicitly authorized by the author.
