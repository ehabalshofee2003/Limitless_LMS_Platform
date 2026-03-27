# 🚀 Limitless LMS

A scalable and production-ready Learning Management System (LMS) backend built with Laravel.  
Designed to simulate real-world educational platforms with advanced features like cohort-based learning, payments, AI-powered tools, and real-time notifications.

---

## 🔥 Key Features

- 👥 Multi-role system (Student / Instructor / Admin)
- 🎓 Cohort-based learning with Drip Content strategy
- 💳 Payment & Wallet system (transactions, payouts)
- 🤖 AI-powered lesson assistant
- 🔔 Real-time notifications (Firebase FCM)
- 🧠 Quiz system with attempts tracking
- 💬 Threaded comments & reviews system
- 🧾 Certificate generation (PDF)
- ⚡ Code execution system (Code Runner)

---

## 🏗️ Architecture

This project follows a clean and scalable architecture:

- **Pattern:** Repository & Service Layer Pattern  
- **Framework:** Laravel 12 (PHP 8.2+)  
- **Database:** MySQL  
- **Caching & Queues:** Redis  
- **Containerization:** Docker-ready  

### 🎯 Design Goals
- Separation of concerns  
- Scalability & maintainability  
- Production-ready structure  

---

## 🗄️ Database Schema (Overview)

| Table         | Description |
|--------------|------------|
| users        | Users (طلاب، مدربين، مشرفين) |
| institutions | Institutions & instructors |
| courses      | Courses with versioning system |
| cohorts      | Cohort-based enrollment |
| lessons      | Lessons (video, PDF, links) |
| lesson_user  | User progress tracking |
| quizzes      | Quiz system (JSON-based) |
| quiz_attempts| Attempts & scores |
| payments     | Payment records |
| wallets      | User balances |
| transactions | Financial transactions |
| reviews      | Course reviews |
| comments     | Threaded discussions |
| notifications| Internal notifications |
| fcm_tokens   | Device tokens for push notifications |

---

## 📡 API Overview

Base URL:

Authentication:

### Example Endpoints

| Method | Endpoint |
|-------|--------|
| POST | `/auth/login` |
| GET  | `/courses` |
| POST | `/courses` |
| GET  | `/cohorts/{id}/lessons` |
| POST | `/quizzes/{id}/submit` |

👉 Full API Documentation available inside the project.

---

## ⚙️ Installation

```bash
git clone https://github.com/ehabalshofee2003/limitless-lms.git
cd limitless-lms

composer install
cp .env.example .env
php artisan key:generate

# Configure database inside .env

php artisan migrate
php artisan serve
```
🐳 Docker Support

docker compose up -d

🔐 Security

Authentication via Laravel Sanctum
- Sensitive data managed through .env
- Token-based API access
- Secure payment handling

💡 Why this Project?
This project was built to simulate a real-world LMS backend system with:
- Complex business logic
- Scalable architecture
- Real production scenarios (payments, cohorts, notifications)
It reflects my ability to design and build backend systems beyond CRUD applications.

🚀 Future Improvements
- GraphQL support
- Advanced analytics dashboard
- Microservices architecture
- AI recommendation engine

👨‍💻 Author
Ehab
Backend Developer (Laravel)

GitHub: https://github.com/ehabalshofee2003

📄 License
This project is licensed under the MIT License.
