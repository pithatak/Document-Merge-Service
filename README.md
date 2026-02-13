# PDF Composer

A simple Symfony + Vue application that allows users to merge multiple PDF and image files into a single PDF document.

---

## 🚀 Features

- Upload 2–10 files
- Supported formats:
    - PDF
    - JPG
    - PNG
- Drag & Drop interface
- Backend validation using DTO and Symfony Validator
- Service-based architecture
- Global API exception handling
- Automatic temporary file cleanup
- Unit tested PDF merge service
- Dockerized environment

---

## 🛠 Tech Stack

### Backend
- PHP 8.4
- Symfony
- FPDI
- TCPDF

### Frontend
- Vue 3
- Axios
- TailwindCSS

### Infrastructure
- Docker

---

## Project Setup (Docker)

### 1. Copy environment file:

From the project root:

```bash
   cp .env.example .env
```
### 2. Build and start containers

From the project root:

```bash
docker compose up -d --build
```

This will:

* build PHP and Nginx containers
* start all required services

### 3. Wait for the download

You need to wait for all dependencies to load. You can check the "php-fpm3" container logs to determine whether the download is complete. If there are no errors, you should see the message "Starting application..."

---

## 🧪 Run Tests

```bash
php bin/phpunit
```

---

# 📡 API Documentation

## Merge Files

Merges multiple PDF or image files into a single PDF document.

---

### Endpoint

```
POST /api/merge
```

---

### Content-Type

```
multipart/form-data
```

---

### Request Body

| Field    | Type    | Required | Description                         |
|----------|---------|----------|-------------------------------------|
| files[]  | file[]  | yes      | 2–10 files (PDF, JPG, PNG)         |

---

### Validation Rules

- Minimum 2 files required
- Maximum 10 files allowed
- Maximum file size: 15MB per file
- Allowed MIME types:
    - application/pdf
    - image/jpeg
    - image/png

---

### Success Response (200)

Returns:

```
Content-Type: application/pdf
```

Binary merged PDF file.

---

### Validation Error (400)

Example response:

```json
{
  "error": "Minimum 2 files required."
}
```

---

### Server Error (500)

```json
{
  "error": "Internal Server Error"
}
```

---
