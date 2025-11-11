# 🚀 Live Upload

A real-time file upload system built with **Laravel**, **Livewire**, and **Docker**.  
This project demonstrates background file processing with queued jobs and Livewire polling for near real-time UI updates.

---

## 🧰 Tech Stack

- **Backend:** Laravel 12  
- **Frontend:** Livewire + TailwindCSS  
- **Containerization:** Docker & Docker Compose  
- **Queue:** Redis + Supervisor
- **Database:** MySQL  

---

## ⚙️ Setup & Installation

### 1️⃣ Clone the Repository

---

### 2️⃣ Build and Start the Containers
```bash
docker compose up -d --build
```
This will:

- Build the PHP + Node image

- Wait for MySQL to be ready

- Automatically run:

    - php artisan migrate --force

    - npm install && npm run build

- Start:

    - PHP-FPM

    - Nginx

    - Supervisor (manages queue:work)

Once running, open your browser and visit:

👉 http://localhost:8005/upload

---

## ⚡ Real-Time Updates

This project uses:
```
<div wire:poll.2s="refresh">
```
Livewire polls the backend every 2 seconds to fetch the latest file upload statuses — no need to refresh the page manually.

---

## 📁 Key Features

- Upload CSV or text files up to 100MB

- Compute checksum to prevent duplicate uploads

- Process files asynchronously in the background via queues

- Display upload status (Pending, Completed, Failed) in real-time

- Automatically refreshes using Livewire polling

- Automatic migration and frontend build handled during Docker startup

- Supervisor-managed queue workers for continuous processing

- Application served at http://localhost:8005

---

## 💡 Highlight

This project uses **Livewire** to handle file uploads and render the upload table.  
While it is **not a standard Laravel controller**, this Livewire component acts in a “controller-like” way:

- **Location:** `app/Http/Livewire/UploadManager.php`  
- **Blade View:** `resources/views/livewire/upload-manager.blade.php`  

#### How it works:

1. **Handles requests & validation**  
   The Livewire component validates uploaded CSV files, stores them, and initiates processing.

2. **Dispatches background jobs**  
   Each upload triggers a `ProcessCsvUpload` that handles CSV parsing, cleaning non-UTF-8 characters, and performing chunked `upsert()` operations in the database.

3. **Uses Transformers (Resources) for data formatting**  
   - The component retrieves uploaded files and passes them through a **Laravel Resource** (`FileUploadResource`) before sending them to the view.  
   - This ensures consistent API-style formatting, similar to what a controller returning a JSON API would do.

4. **Real-time progress updates**  
   - Redis stores upload progress.  
   - Livewire polls and updates the table automatically without page reloads.  

#### Key Points:

- This component **replaces a traditional controller** for this page.  
- All heavy-lifting logic (CSV parsing, upsert, progress tracking) is handled in the **Job file**.  
- The page is fully reactive and public — no auth is required.  

> **Note:** If you are looking for a traditional `Controller -> View` setup, this page does not use it. All actions are encapsulated in the Livewire component and background job.
