# Step-by-Step Setup: Laravel, PostgreSQL, GitHub, and Render

This guide is written for this project on Windows/PowerShell.

Current project shape:

- This app currently runs as a Node.js + Express web app from `server/server.js`.
- Static pages live in `public/`.
- PostgreSQL is used through the Node `pg` package.
- Laravel is not installed inside this folder yet. The safest integration path is to create a Laravel app beside this project, then either proxy to this Node API or gradually move pages/API code into Laravel.

Official references:

- Laravel installation: https://laravel.com/docs/12.x/installation
- Laravel database config: https://laravel.com/docs/12.x/database
- PostgreSQL Windows installer: https://www.postgresql.org/download/windows/
- GitHub existing project guide: https://docs.github.com/en/github/importing-your-projects-to-github/adding-an-existing-project-to-github-using-the-command-line
- Render Node/Express deploy guide: https://render.com/docs/deploy-node-express-app
- Render PostgreSQL guide: https://render.com/docs/postgresql-creating-connecting

## 1. Install Required Tools

Open PowerShell and check what you already have:

```powershell
php -v
composer --version
node -v
npm -v
git --version
```

If PHP, Composer, or Laravel are missing, install Laravel's tooling:

```powershell
composer global require laravel/installer
```

Close and reopen PowerShell, then check:

```powershell
laravel --version
```

If `laravel` is not recognized, add Composer's global vendor bin folder to your Windows PATH:

```powershell
$env:APPDATA + "\Composer\vendor\bin"
```

Add that folder in Windows Environment Variables, then reopen PowerShell.

## 2. Install PostgreSQL on Windows

1. Go to https://www.postgresql.org/download/windows/
2. Download the EDB PostgreSQL installer.
3. Run the installer.
4. Install these components:
   - PostgreSQL Server
   - pgAdmin 4
   - Command Line Tools
5. Remember the password you set for the `postgres` user.
6. Keep the default port unless you need another one:

```text
5432
```

After installation, open PowerShell and test:

```powershell
psql --version
```

If `psql` is not recognized, add PostgreSQL's `bin` folder to PATH. It is commonly:

```text
C:\Program Files\PostgreSQL\18\bin
```

Your version folder may be `17`, `16`, or another installed version.

## 3. Create the Local Database

Log in to PostgreSQL:

```powershell
psql -U postgres
```

Create the database:

```sql
CREATE DATABASE momoys_furniture;
```

Exit:

```sql
\q
```

## 4. Configure This App for Local PostgreSQL

In this project, open `.env` and set:

```env
NODE_ENV=development
PORT=3000
APP_BASE_URL=http://localhost:3000

DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=momoys_furniture
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password

AUTH_SECRET=replace-this-with-a-long-random-secret
AUTH_TOKEN_TTL_SECONDS=604800

ADMIN_EMAIL=admin@momoys.com
ADMIN_PASSWORD=admin123
```

Do not commit `.env` to GitHub. It is already ignored by `.gitignore`.

Install dependencies:

```powershell
npm install
```

Run the app:

```powershell
npm start
```

Open:

```text
http://localhost:3000
```

Test the backend:

```text
http://localhost:3000/api/health
```

The server creates the required PostgreSQL table automatically. You can also run `database/schema.sql` manually if needed.

## 5. Create and Use a Laravel App Beside This Project

From the parent folder, create a Laravel app:

```powershell
cd "E:\JR FILES\Third Year Files\Second Sem\Software Engineering 2\laravel"
laravel new momoys-laravel
cd momoys-laravel
npm install
npm run build
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

Configure Laravel to use the same local PostgreSQL database. In `momoys-laravel/.env`, set:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=momoys_furniture
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

Then run:

```powershell
php artisan migrate
```

Note: this project's current PostgreSQL data is stored in a JSON document table named `documents`. Laravel migrations create Laravel's own tables. That is okay while you are integrating gradually.

## 6. Integration Options Between Laravel and This App

Choose one integration style.

### Option A: Keep Node as the API and Use Laravel as the Front Door

Use this if you want the app working quickly.

1. Keep this Node app running on:

```text
http://localhost:3000
```

2. Keep Laravel running on:

```text
http://127.0.0.1:8000
```

3. In Laravel, create routes/pages that link to or embed the existing public pages.
4. Use the Node API for:

```text
/api/products
/api/orders
/api/auth/login
/api/auth/register
```

This is the lowest-risk path because the existing app already works with the Node API.

### Option B: Move Static Pages Into Laravel Blade

Use this if your teacher requires Laravel views.

1. Copy HTML content from `public/*.html`.
2. Create Blade files in Laravel:

```text
resources/views/pages/login.blade.php
resources/views/pages/products.blade.php
resources/views/pages/cart.blade.php
```

3. Copy assets from this project:

```text
public/css
public/js
public/images
public/models
```

Into Laravel:

```text
momoys-laravel/public/css
momoys-laravel/public/js
momoys-laravel/public/images
momoys-laravel/public/models
```

4. Add Laravel routes in `routes/web.php`:

```php
Route::view('/', 'pages.index');
Route::view('/login', 'pages.login');
Route::view('/products', 'pages.products');
Route::view('/cart', 'pages.cart');
```

5. Update links from `.html` URLs to Laravel routes later.

Example:

```html
<!-- old -->
<a href="/products.html">Products</a>

<!-- new -->
<a href="/products">Products</a>
```

### Option C: Fully Rewrite the Backend in Laravel

Use this if the final requirement is a pure Laravel backend.

You would recreate these Node routes as Laravel controllers:

```text
server/routes/auth.js          -> AuthController
server/routes/products.js      -> ProductController
server/routes/orders.js        -> OrderController
server/routes/payments.js      -> PaymentController
server/routes/users.js         -> UserController
server/routes/brands.js        -> BrandController
server/routes/queries.js       -> QueryController
server/routes/testimonials.js  -> TestimonialController
```

This is a larger rewrite, so do it after the Node + PostgreSQL version is stable.

## 7. Push the Project to GitHub

From this project folder:

```powershell
cd "E:\JR FILES\Third Year Files\Second Sem\Software Engineering 2\laravel\momoys-furniture"
git status
```

Make sure `.env` is not staged:

```powershell
git status --short
```

If this is your first GitHub push, create an empty repository on GitHub. Do not add a README on GitHub if your local project already has one.

Then run:

```powershell
git add .
git commit -m "Convert app to Node PostgreSQL backend"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git push -u origin main
```

If `origin` already exists:

```powershell
git remote -v
git remote set-url origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git push -u origin main
```

Never push these:

```text
.env
node_modules/
serviceAccountKey.json
```

## 8. Create PostgreSQL on Render

1. Go to https://dashboard.render.com
2. Click `New +`
3. Click `Postgres`
4. Fill in:

```text
Name: momoys-furniture-db
Database: momoys_furniture
User: momoys_user
Region: choose the closest region, and use the same region for the web service
PostgreSQL Version: latest available stable version
```

5. Create the database.
6. Wait until the database status is available.
7. Open the database page.
8. Copy the `Internal Database URL`.

Render recommends internal database URLs for services in the same account and region because they use Render's private network.

## 9. Deploy the Node App to Render

1. Go to Render Dashboard.
2. Click `New +`.
3. Click `Web Service`.
4. Connect your GitHub repository.
5. Select this project repo.
6. Configure:

```text
Language: Node
Branch: main
Build Command: npm install
Start Command: npm start
```

7. Add environment variables:

```env
NODE_ENV=production
DATABASE_URL=paste_render_internal_database_url_here
PGSSL=true
AUTH_SECRET=generate_a_long_random_secret
AUTH_TOKEN_TTL_SECONDS=604800
ADMIN_EMAIL=admin@momoys.com
ADMIN_PASSWORD=change-this-password
APP_BASE_URL=https://your-render-service-name.onrender.com
PAYMONGO_SECRET_KEY=your_paymongo_secret_if_using_payments
PAYMONGO_WEBHOOK_SECRET=your_paymongo_webhook_secret_if_using_payments
```

If your Render Postgres connection works without SSL, `PGSSL` can be removed. If you see SSL-related connection errors, keep `PGSSL=true`.

8. Click `Create Web Service`.
9. Wait for the deploy to finish.

Test:

```text
https://your-render-service-name.onrender.com/api/health
```

You should see JSON with:

```json
{
  "status": "OK",
  "database": "postgresql"
}
```

## 10. Update the Frontend API URL for Render

Open:

```text
public/js/config.js
```

For production, update this line:

```js
: 'https://momoysfurniture.onrender.com/api';
```

Change it to your actual Render URL:

```js
: 'https://your-render-service-name.onrender.com/api';
```

Commit and push:

```powershell
git add public/js/config.js
git commit -m "Update production API URL"
git push
```

Render will redeploy automatically after the push.

## 11. PayMongo Webhook on Render

If you use PayMongo payments, set the webhook URL to:

```text
https://your-render-service-name.onrender.com/api/payments/paymongo/webhook
```

Subscribe to:

```text
checkout_session.payment.paid
```

Make sure these Render environment variables are set:

```env
PAYMONGO_SECRET_KEY=your_secret_key
PAYMONGO_WEBHOOK_SECRET=your_webhook_secret
APP_BASE_URL=https://your-render-service-name.onrender.com
```

## 12. Common Problems

### PostgreSQL connection refused locally

Error:

```text
ECONNREFUSED 127.0.0.1:5432
```

Fix:

1. Open Windows Services.
2. Find `postgresql-x64-*`.
3. Start or restart it.
4. Confirm `.env` uses the right password and port.

### Password authentication failed

Fix `.env`:

```env
DB_USERNAME=postgres
DB_PASSWORD=your_actual_postgres_password
```

Restart the Node server after changing `.env`.

### Render deploy succeeds but database fails

Check:

```env
DATABASE_URL=your_render_internal_database_url
PGSSL=true
```

Also confirm the Render web service and Render Postgres database are in the same region.

### Login works locally but not on Render

Check:

```env
AUTH_SECRET=the_same_value_between_deploys
APP_BASE_URL=https://your-render-service-name.onrender.com
```

After changing environment variables, redeploy the Render service.

## 13. Suggested Final Workflow

For your project submission, use this order:

1. Get PostgreSQL running locally.
2. Run this Node app locally with `npm start`.
3. Confirm login/register works.
4. Create the Laravel app beside this project.
5. Move pages into Laravel Blade only if required.
6. Push the working code to GitHub.
7. Create Render PostgreSQL.
8. Deploy the Node app to Render.
9. Update `public/js/config.js` with the Render URL.
10. Push again and test the hosted site.
