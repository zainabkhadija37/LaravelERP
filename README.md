# 🏗️ Laravel ERP — Inventory & Warehouse Management System

A production-ready Laravel ERP application for inventory, warehouse, purchasing, sales, reporting, notifications, role-based access control (RBAC), and REST APIs.

## 🚀 Overview

`LaravelERP` is a real-world ERP application built with Laravel to demonstrate practical backend engineering and clean application architecture.

The system covers:

- Inventory and stock management
- Warehouse and supplier management
- Purchase orders and sales workflows
- Reporting and data exports
- Notifications and low-stock alerts
- Role-based access control (RBAC)
- REST APIs for system integration
- Clean and maintainable application architecture

## 📸 Screenshots

### Product
![Product](screenshots/products.png)

### Dashboard
![Dashboard](screenshots/dashboard.png)

### Inventory Management
![Inventory Management](screenshots/inventory.png)

### Purchase Management
![Purchase Management](screenshots/purchases.png)

### Sales Management
![Sales Management](screenshots/sales.png)

### Warehouse Management
![Warehouse Management](screenshots/warehouse.png)

### Reports
![Reports](screenshots/reports.png)

## ✨ Key Features

- Product, supplier, customer, and warehouse management
- Purchase order creation, receipt handling, and automatic stock updates
- Sales order processing and sale receipt notifications
- Low-stock alerts and notification support
- Exportable inventory and sales reports
- Role-based access control (RBAC)
- REST API endpoints for integration
- Event-driven workflows using events, listeners, jobs, and queues

## 🧠 Architecture & Engineering Practices

This project demonstrates:

- Clean separation of controllers, services, API resources, and policies
- Service-layer based business logic
- Form Request validation
- Eloquent ORM and relationship management
- Database transactions for critical business operations
- Event-driven application workflows
- Asynchronous processing with jobs and queues
- RESTful API design
- Role-based authorization
- Maintainable and scalable Laravel application structure

## 🛠️ Tech Stack

### Backend
- Laravel
- PHP

### Database
- MySQL / MariaDB
- Eloquent ORM

### Frontend
- Blade
- Bootstrap / CSS

### APIs
- REST APIs
- Laravel API Resources

### Application Features
- Notifications
- Events & Listeners
- Jobs & Queues
- Role-Based Access Control
- Data Exports

## ▶️ Installation & Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

### 1. Clone the repository

```bash
git clone <https://github.com/zainabkhadija37/LaravelERP>
cd LaravelERP