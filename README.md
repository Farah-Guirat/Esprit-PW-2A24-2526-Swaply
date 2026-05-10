# 🔄 Swaply

> A web platform for exchanging services between users — built with simplicity and community in mind.

![HTML](https://img.shields.io/badge/HTML-E34F26?style=flat&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![AI](https://img.shields.io/badge/AI-Powered-FF6F00?style=flat&logo=openai&logoColor=white)

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

---

## 📖 About

**Swaply** is a service-exchange web application developed as part of the **Web Programming (PW)** module — 2nd Year Integrated Preparatory Program at **Esprit School of Engineering, Tunisia** (Academic Year 2025–2026).

The platform allows users to post, discover, and exchange services with one another — no money involved, just skills and time.

---

## ✨ Features

- 🔐 **User Authentication** — Secure sign up, login and session management
- 📁 **Projects** — Create and manage your exchange projects
- 📢 **Offers & Requests** — Post services you offer or request what you need
- 💬 **Messaging** — Real-time chat between users to coordinate exchanges
- 🚨 **Reclamations** — Submit and track complaints or disputes
- 📝 **Blog** — Share articles, tips and community updates
- 🤖 **AI Integration** — Smart features powered by artificial intelligence

---

## 🛠 Tech Stack

| Technology | Role |
|------------|------|
| HTML | Structure & markup |
| CSS | Styling & layout |
| JavaScript | Interactivity & logic |
| PHP | Backend & server-side logic |
| AI | Intelligent features & automation |

---

## ⚙️ Installation

1. **Clone the repository**

```bash
git clone https://github.com/Projet-technologies-web-2A/swaply.git
cd swaply
```

2. **Set up a local PHP server**

Make sure you have **PHP** and **MySQL** installed (via [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/)).

* Copy the project into your server's root folder:
  * XAMPP → `htdocs/swaply`
  * WAMP → `www/swaply`

3. **Configure the database**

* Open **phpMyAdmin** at `http://localhost/phpmyadmin`
* Create a new database named `swaply`
* Import the provided SQL file:

```bash
swaply/database/swaply.sql
```

4. **Configure environment**

* Open `config.php` and update your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'swaply');
```

5. **Launch the app**

* Start Apache & MySQL in XAMPP/WAMP
* Visit `http://localhost/swaply` in your browser

---

## 🚀 Usage

1. Register a new account or log in with existing credentials.
2. Create a **project** to organize your service exchanges.
3. Browse **offers** from other users or post a **request** for a service you need.
4. Use the **messaging** system to coordinate with other users.
5. Read and publish articles on the **blog**.
6. Submit a **reclamation** if you encounter any issue.
7. Let the **AI** assist you with smart recommendations and automation.

---

## 🤝 Contributing

Contributions are welcome! Here's how to get started:

1. Fork this repository
2. Create a new branch:

```bash
git checkout -b feature/your-feature-name
```

3. Make your changes and commit:

```bash
git commit -m "Add: your feature description"
```

4. Push to your branch:

```bash
git push origin feature/your-feature-name
```

5. Open a **Pull Request** and describe your changes

Please follow the existing code style and keep commits clear and focused.

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<p align="center">Made with ❤️ at Esprit School of Engineering — Tunisia</p>