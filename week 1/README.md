# Week 1: Student and Course CRUD

A Laravel application for managing students and courses with resourceful CRUD
routes, validation, MySQL migrations, and Bootstrap Blade views.

## Setup

1. Install dependencies and configure the local environment:

   ```sh
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Create the database in MySQL or DBeaver:

   ```sql
   CREATE DATABASE WorkshopDB;
   ```

3. Check the MySQL credentials in `.env`, then run migrations and start Laravel:

   ```sh
   php artisan migrate
   php artisan serve
   ```

   Open `http://127.0.0.1:8000/students` or
   `http://127.0.0.1:8000/courses`.

## Features

- Student records include name, unique email, phone, address, and date of birth.
- Courses include a description, duration in weeks, fee, difficulty, and active
  status.
- Both resources support listing, details, creation, editing, and deletion.
- Form validation errors and success messages are displayed in the UI.

Run the feature tests with `php artisan test`.
