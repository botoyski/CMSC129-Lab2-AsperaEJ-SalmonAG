# CMSC 129 Lab 2 - Task Management App

## Application Description
This is a Laravel + PostgreSQL task management application for CMSC 129 Lab 2.

Main purpose:
- Let users register/login and manage tasks.
- Demonstrate Laravel MVC architecture with full CRUD.
- Demonstrate database features like relationships, soft deletes, seeding, and filtered retrieval.

## Tech Stack
- Laravel 12
- PHP 8.3
- PostgreSQL
- Blade + Alpine.js
- Tailwind CSS (via Vite)

## Implemented Features
### Core Features (Rubric)
- Full CRUD for tasks:
  - Create task
  - Read task list and task details
  - Update task
  - Delete task
- MVC architecture:
  - Model: Task, Category, User
  - View: Blade templates and components
  - Controller: TaskController, ProfileController
- Database and ORM:
  - PostgreSQL connection via .env
  - Laravel migrations
  - Eloquent ORM for all operations
- Blade requirements:
  - Layouts with section/yield pattern
  - Reusable components
  - CSRF token support
  - Validation errors displayed in modal forms

### Expanded Features
- Soft delete with restore and permanent delete:
  - Soft delete to Trash
  - Restore from Trash
  - Permanent delete from Trash
- Search and filter:
  - Search by title/description/category
  - Filters by status, priority, and category
  - Works with pagination
- Database relationship:
  - Category hasMany Task
  - Task belongsTo Category
  - Task belongsTo User
- Seeding with Faker:
  - CategorySeeder
  - TaskSeeder

## Project Structure and MVC Explanation
- app/Models/User.php:
  - Eloquent user model, hasMany tasks.
- app/Models/Task.php:
  - Main resource model, uses SoftDeletes, belongsTo user and category.
- app/Models/Category.php:
  - Related model, hasMany tasks.
- app/Http/Controllers/TaskController.php:
  - Handles business logic for task CRUD, search/filter, trash/restore/force delete.
- app/Http/Requests/StoreTaskRequest.php:
  - Validation rules/messages for create.
- app/Http/Requests/UpdateTaskRequest.php:
  - Validation rules/messages for update.
- resources/views/dashboard.blade.php:
  - Main task UI page.
- resources/views/components/taskcard.blade.php:
  - Reusable task card UI component.
- resources/views/components/taskmodal.blade.php:
  - Reusable create/edit/archive/delete modal component.
- resources/views/tasks/show.blade.php:
  - Task detail page.
- routes/web.php:
  - Resource routes and custom restore/force-delete routes.
- database/migrations:
  - Schema definitions for users, categories, tasks, and framework tables.
- database/seeders:
  - Seeds test data for quick demo.

## Environment Setup
### 1. Prerequisites
Install or make sure you have:
- PHP 8.2+ (8.3 used in this project)
- Composer
- Node.js + npm
- PostgreSQL server

### 2. Clone and Install
Run inside project root:

```powershell
composer install
npm install
```

### 3. Environment File
Copy env template:

```powershell
Copy-Item .env.example .env -Force
```

Configure database in .env:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lab2
DB_USERNAME=laravel_user
DB_PASSWORD=password
```

Generate app key:

```powershell
php artisan key:generate
```

## PostgreSQL Driver Fix (Important)
If you see `could not find driver` while migrating, enable pgsql drivers in your active php.ini:

- extension=pdo_pgsql
- extension=pgsql

Quick check:

```powershell
php -m
```

Expected to include:
- pdo_pgsql
- pgsql

## Database Setup and Migration
Create your PostgreSQL database first (example name: lab2), then run:

```powershell
php artisan migrate --seed
```

This will:
- Create all tables
- Run CategorySeeder and TaskSeeder

## Run the Application
Use two terminals in project root:

Terminal 1:
```powershell
npm run dev
```

Terminal 2:
```powershell
php artisan serve
```

Open:
- http://127.0.0.1:8000

## How to Verify Database Reflection
### A. Quick Laravel checks
1. Show DB connection and tables:

```powershell
php artisan db:show
```

2. Show tasks table schema:

```powershell
php artisan db:table tasks
```

3. Show users:

```powershell
php artisan tinker --execute="dump(App\Models\User::query()->orderByDesc('id')->get(['id','name','email','created_at'])->toArray());"
```

4. Show task counts per user:

```powershell
php artisan tinker --execute="dump(App\Models\Task::query()->selectRaw('user_id, COUNT(*) as total')->groupBy('user_id')->orderBy('user_id')->get()->toArray());"
```

5. Show tasks for a specific user (example user_id 2):

```powershell
php artisan tinker --execute="dump(App\Models\Task::query()->with('user:id,name,email')->where('user_id', 2)->latest()->get(['id','user_id','title','status','priority','due_date','created_at'])->toArray());"
```

### B. GUI checks using HeidiSQL (Laragon)
If Laragon shortcut gives launch directory error, run:

```powershell
Start-Process -FilePath "C:\laragon\bin\heidisql\heidisql.exe" -WorkingDirectory "C:\laragon\bin\heidisql"
```

Connection values:
- Type: PostgreSQL (TCP/IP)
- Host: 127.0.0.1
- Port: 5432
- User: laravel_user
- Password: password
- Database: lab2

Then:
- Open connection
- Expand lab2
- Open Tables
- Right-click table -> Select rows

## Common Troubleshooting
### 1. `could not find driver`
- Enable pgsql extensions in php.ini.
- Verify with `php -m`.

### 2. `php artisan serve` exits with code 1
Try:

```powershell
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8001
```

If 8000 is occupied, use another port.

### 3. `npm run dev` exits with code 1
Try:

```powershell
npm install
npm run dev
```

Also verify Node.js is installed:

```powershell
node -v
npm -v
```

## Sample Demo Flow for Presentation
1. Register a new account.
2. Create a task under that account.
3. Confirm user/task in DB using tinker command.
4. Update task status/priority.
5. Move task to Trash (soft delete).
6. Restore task from Trash.
7. Permanently delete a trashed task.

## Screenshots
Add your screenshots here before submission:

## Notes
- .env is private and should not be committed.
- .env.example is a template for setup and should be committed.
- UI was prepared by project frontend work, backend is integrated to PostgreSQL via Laravel APIs.
