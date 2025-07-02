# 🧠 Team Task Manager API

This is a Laravel-based task manager API that supports user authentication, role-based access (Admin/Member), task assignment, soft deletes, and Excel import/export.

---

## ⚙️ Setup Instructions

```bash
# 1. Clone the repository
git clone https://github.com//Oluwamaya/team-task.git
cd team-task-manager

# 2. Install dependencies
composer install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Set your .env database config
DB_DATABASE=team_task
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations
php artisan migrate

# 6. Start the development server
php artisan serve

# Sample for Admin
{
  "name": "Admin",
  "email": "admin@example.com",
  "password": "password",
  "role": "admin"
}

# Sample for Member
{
  "name": "Member",
  "email": "member@example.com",
  "password": "password",
  "role": "member"
}

# ASSUMPTION AND DECISION
User roles are kept simple for this project — just "admin" and "member" — and stored as plain strings in the users table.

For Excel import/export, I used the popular maatwebsite/excel package. Formatting is clean and readable, and includes the required fields.

Admin users have full access to all tasks — they can create, assign, update, delete (soft or permanent), and restore.

Members can only view the tasks assigned to them and update their status (e.g., from pending to completed).

Dates are validated and formatted using Laravel’s built-in tools — no external libraries used for that.

During import, if a row is missing required data or has errors (like an unknown email), it's simply skipped or logged — so the rest of the file still processes without breaking.


