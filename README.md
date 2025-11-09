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