# Laravel + PostgreSQL Setup Guide

## Overview
Your Momoy's Furniture application has been converted from Firebase to a complete Laravel backend with PostgreSQL database. This guide will help you set up and run the application.

## Prerequisites
- PHP 8.2 or higher
- PostgreSQL 12 or higher
- Composer
- Node.js and NPM (for frontend assets)

## Setup Instructions

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Configure Environment

Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env
```

Edit `.env` and configure your PostgreSQL database:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=momoys_laravel
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

Generate the application key:
```bash
php artisan key:generate
```

### 3. Create PostgreSQL Database

Using psql or pgAdmin, create the database:
```sql
CREATE DATABASE momoys_laravel;
```

### 4. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- `users` - User accounts with role support
- `api_tokens` - Authentication tokens
- `products` - Product catalog
- `brands` - Product brands
- `orders` - Customer orders
- `order_messages` - Order chat/messaging
- `payments` - Payment records
- `queries` - Customer inquiries
- `testimonials` - User testimonials

### 5. Build Frontend Assets

```bash
npm run build
```

For development with hot reload:
```bash
npm run dev
```

### 6. Start the Application

**Development Server:**
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

**Production:**
Use a proper PHP-FPM setup with Nginx or Apache.

## Authentication Flow

### Login/Register
1. Users visit `/login`
2. Forms POST to `/api/auth/login` or `/api/auth/register`
3. Laravel validates credentials and returns JWT-like token
4. Token stored in `localStorage` with user data
5. Authenticated requests include token in `Authorization: Bearer {token}` header

### Token Management
- Tokens expire after 30 days
- Stored in `api_tokens` table
- Frontend uses `/js/auth-helper.js` for token management
- Invalid tokens automatically redirect to login

## API Endpoints

### Authentication
- `POST /api/auth/register` - Create new account
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get current user
- `PUT /api/auth/profile` - Update user profile

### Products
- `GET /api/products` - List all products
- `POST /api/products` - Create product (admin only)
- `PUT /api/products/{id}` - Update product (admin only)
- `DELETE /api/products/{id}` - Delete product (admin only)

### Orders
- `GET /api/orders` - List orders
- `POST /api/orders` - Create order
- `GET /api/orders/{id}` - Get order details
- `GET /api/orders/{id}/chat` - Get order messages
- `POST /api/orders/{id}/chat` - Send order message

### Other Resources
- `/api/brands` - Product brands
- `/api/queries` - Customer inquiries
- `/api/testimonials` - User testimonials
- `/api/payments` - Payment records

## Frontend Changes

### Old Firebase Code Removed
The following Firebase-related files have been removed from the login page:
- Firebase initialization script
- Firebase authentication calls
- Firestore database operations

### New Auth Helper
A new `/js/auth-helper.js` file provides:
- Token management
- Authenticated API requests
- Auto-logout on 401 responses
- Admin role checking

### Usage in Your Pages
```html
<!-- Include auth helper in your pages -->
<script src="/js/auth-helper.js"></script>

<!-- Make authenticated requests -->
<script>
  const response = await auth.request('/products', {
    method: 'GET'
  });
</script>

<!-- Check authentication -->
<script>
  if (auth.requireAdmin()) {
    // Load admin content
  }
</script>
```

## Database Structure

### Users Table
```
id (bigint, primary key)
name (string) - User's full name
email (string, unique)
email_verified_at (timestamp, nullable)
password (string, hashed)
role (string) - 'admin' or 'customer'
remember_token (string, nullable)
created_at (timestamp)
updated_at (timestamp)
```

### API Tokens Table
```
id (bigint, primary key)
user_id (bigint, foreign key)
token_hash (string, unique) - SHA256 hash of token
expires_at (timestamp, nullable) - 30 days from creation
created_at (timestamp)
updated_at (timestamp)
```

## Troubleshooting

### Database Connection Error
- Verify PostgreSQL is running: `pg_isready -h localhost`
- Check database credentials in `.env`
- Ensure database exists: `createdb momoys_laravel`

### Migration Errors
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database user has proper permissions
- Run migrations with verbose output: `php artisan migrate -v`

### Authentication Issues
- Clear browser localStorage: `localStorage.clear()`
- Check token expiration
- Verify Authorization header is being sent

### Asset Loading Issues
- Rebuild assets: `npm run build`
- Clear browser cache
- Check `public/build` directory exists

## Development Tips

### Database Seeding
To seed test data (if seeders are available):
```bash
php artisan db:seed
```

### Database Interactions
Access the database using psql:
```bash
psql -U postgres -d momoys_laravel
```

### API Testing
Use tools like Postman or curl:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

## Deployment

### On Render.com (as per existing documentation)
See `SETUP_LARAVEL_POSTGRES_GITHUB_RENDER.md` for deployment instructions.

### Environment Setup
Set these environment variables in your hosting platform:
- `DB_CONNECTION=pgsql`
- `DB_HOST=` (your PostgreSQL host)
- `DB_PORT=5432`
- `DB_DATABASE=` (your database name)
- `DB_USERNAME=` (your database user)
- `DB_PASSWORD=` (your database password)
- `APP_KEY=` (generate with `php artisan key:generate`)

## Support

For issues:
1. Check Laravel logs in `storage/logs/`
2. Review API responses in browser DevTools
3. Verify database connections
4. Check file permissions on `storage/` and `bootstrap/cache/` directories

---

**Next Steps:**
1. Set up PostgreSQL locally or on your server
2. Copy `.env.example` to `.env` and configure database
3. Run migrations: `php artisan migrate`
4. Start development server: `php artisan serve`
5. Visit `http://localhost:8000/login` to test authentication
