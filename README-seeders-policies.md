# Seeders + policies

1. Copy `app/`, `config/` and `database/` into the project root.
   (It replaces database/seeders/DatabaseSeeder.php.)
2. Add to `.env`:

       ADMIN_NAME="Admin"
       ADMIN_EMAIL=oui.cruzado@gmail.com
       ADMIN_PASSWORD=choose-a-strong-password
       REQUIRE_SUBSCRIPTION=false

3. `php artisan config:clear`
4. `php artisan db:seed`

Policies are auto-discovered (App\Models\X -> App\Policies\XPolicy). Models without a
policy (e.g. ActivityLog) are denied by default.
