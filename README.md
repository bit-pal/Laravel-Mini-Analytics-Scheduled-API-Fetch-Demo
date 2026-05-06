# Laravel Scheduler + Mini Analytics Dashboard (HH.ru Test Task)

## Demo video
[Watch demo video](https://github.com/user-attachments/assets/b6497a0c-d663-4b07-a16c-22d1e5e917b8)

## Main goal
Build a **complete, working Laravel project** that demonstrates:

- a scheduled **console command** that fetches data from a public API and stores it in a DB table
- a **JSON endpoint** returning stored records
- a small **frontend JS task** (dynamic fields based on a “Type” select)
- an **analytics mini-system**: embeddable tracker script + backend storage + **authorized** statistics page with charts

This repository implements all requirements using **Laravel 12** and **SQLite**.

## What’s implemented (mapped to requirements)

### 1) Console command (scheduled)
- **Command**: `php artisan api:fetch-joke`
- **Source API**: Official Joke API (`random_joke`)
- **Storage table**: `api_fetches` (stores `source`, `external_id`, JSON `payload`, `fetched_at`)
- **Schedule**: configured in `bootstrap/app.php`
  - For demo speed it’s set to **every 30 seconds** (`everyThirtySeconds()`).
  - To match the original requirement, switch it back to **every 5 minutes** (`everyFiveMinutes()`).

### 2) JSON route returning table records
- **Endpoint**: `GET /api/api-fetches?limit=50`

### 3) JS dynamic fields by Type (external page requirement)
- **File**: `public/js/type-fields.js`
- **Algorithm**:
  - detect a “Type” `<select>` (by `id/name/label`)
  - on change: show only fields where `name` contains the selected value
  - hide + disable non-matching fields

### 4) Additional task: visit counter + analytics page
- **Tracker script** (embeddable):
  - `GET /tracker.js?siteKey=...`
  - sends visit data to `POST /api/visit`
  - posts back to the same origin where `tracker.js` is hosted (works for `127.0.0.1:8000`, LAN IPs, deployment, etc.)
- **Collector endpoint**:
  - `POST /api/visit`
  - stores: IP (server-side), city (GeoIP lookup; localhost → `Localhost`), device (UA heuristic), url/referrer/timezone, timestamp
- **Auth**:
  - Laravel Breeze (Blade)
  - stats page requires login
- **Stats UI**:
  - `GET /stats` shows:
    - unique visits per hour (last 24h)
    - pie chart by city (last 24h)

## Requirements
- PHP **8.2**
- Composer
- Node.js (optional; only needed if you want to rebuild frontend assets)

## Setup (SQLite)
From the project root:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Create SQLite DB file (CMD):

```bat
type nul > database\database.sqlite
```

Run migrations + seed demo data:

```bash
php artisan migrate
php artisan db:seed
```

## Run locally
Start the web server:

```bash
php artisan serve
```

Open:
- `http://127.0.0.1:8000`

## Quick verification (recommended order)

### A) API fetch “logs”
Create a couple records:

```bash
php artisan api:fetch-joke
php artisan api:fetch-joke
```

View stored records as JSON:
- `http://127.0.0.1:8000/api/api-fetches`

### B) Scheduler (runs the fetch periodically)
Check current schedule:

```bash
php artisan schedule:list
```

To run scheduler locally:

```bash
php artisan schedule:work
```

### C) JS dynamic fields (external page)
Attach:

```html
<script src="/js/type-fields.js"></script>
```

Or paste the file contents into the browser console.

### D) Analytics

#### Login
- URL: `http://127.0.0.1:8000/login`
- Demo user:
  - Email: `test@example.com`
  - Password: `password`

#### Open stats page
- `http://127.0.0.1:8000/stats`

#### Get `siteKey`

```bash
php artisan tinker --execute="echo DB::table('sites')->value('site_key');"
```

#### Create visit logs (recommended)
The easiest way is to embed the tracker on any page. For local demo, you can also call it directly:

- `http://127.0.0.1:8000/tracker.js?siteKey=YOUR_SITE_KEY&v=1`

Then refresh:
- `http://127.0.0.1:8000/stats`

#### Create test city logs (manual inserts)
If you want multiple regions quickly (for the pie chart), insert synthetic visits:

```bash
php artisan tinker --execute="DB::table('visits')->insert(['site_id'=>DB::table('sites')->value('id'),'visited_at'=>now(),'ip'=>'10.0.1.1','city'=>'Tokyo','device'=>'mobile','user_agent'=>'Test UA','url'=>'http://example.com','referrer'=>null,'timezone'=>'Asia/Tokyo','created_at'=>now(),'updated_at'=>now()]);"
php artisan tinker --execute="DB::table('visits')->insert(['site_id'=>DB::table('sites')->value('id'),'visited_at'=>now(),'ip'=>'10.0.1.2','city'=>'Paris','device'=>'desktop','user_agent'=>'Test UA','url'=>'http://example.com','referrer'=>null,'timezone'=>'Europe/Paris','created_at'=>now(),'updated_at'=>now()]);"
php artisan tinker --execute="DB::table('visits')->insert(['site_id'=>DB::table('sites')->value('id'),'visited_at'=>now(),'ip'=>'10.0.1.3','city'=>'New York','device'=>'tablet','user_agent'=>'Test UA','url'=>'http://example.com','referrer'=>null,'timezone'=>'America/New_York','created_at'=>now(),'updated_at'=>now()]);"
```

## Useful endpoints summary
- API fetch records JSON: `GET /api/api-fetches`
- Tracker script: `GET /tracker.js?siteKey=...`
- Visit collector: `POST /api/visit`
- Stats page: `GET /stats` (auth)
- Stats data (auth):
  - `GET /stats/data/hourly`
  - `GET /stats/data/cities`

```html
<script src="http://127.0.0.1:8000/tracker.js?siteKey=YOUR_SITE_KEY"></script>
```

### Collector endpoint
- `POST /api/visit`

Server stores: IP (from request), city (GeoIP lookup), device (UA heuristic), url/referrer/timezone.

### Statistics page (charts)
Login, then open:
- `GET /stats`

Shows:
- unique visits by hour (last 24h)
- unique visits by city (last 24h)

