# 🏗️ Laravel ERP — Inventory & Warehouse Management System

A production-ready Laravel ERP demo for inventory, warehouse, purchasing, sales, reporting, notifications, RBAC, and REST APIs.

## 🚀 Overview

`LaravelERP` is a real-world ERP application built with Laravel to showcase:
- inventory and stock management
- warehouse and supplier handling
- purchase orders and sales workflows
- reporting, exports, and notifications
- role-based access control and REST APIs
- clean, maintainable architecture

## ✨ Key Features

- Product, supplier, customer, warehouse management
- Purchase order creation, receipt handling, and stock updates
- Sales order processing and sale receipt notifications
- Low-stock alerts with notification support
- Exportable reports for inventory and sales
- Role-based access control (RBAC)
- REST API endpoints for integration
- Event-driven workflows with listeners and jobs

## 🧠 Why Recruiters Should Care

This project demonstrates:
- practical Laravel application design
- business-domain implementation, not boilerplate
- clean separation of controllers, services, resources, and policies
- asynchronous processing with jobs and queues
- API-first thinking and maintainable code structure

## 🛠 Tech Stack

- Laravel
- PHP
- MySQL / MariaDB
- Blade / API Resources
- Notifications
- Queues / Jobs
- Export utilities

## ▶️ Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve