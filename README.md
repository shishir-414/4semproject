# Lost & Found Web Application

A complete Lost and Found web application built using Core PHP, MySQL, HTML/CSS, and Docker.

This system allows users to report lost items, verify found items, and reclaim their belongings.

## 🚀 Features

-   **Authentication**: Secure User Registration, Login, and Logout.
-   **Item Management**: 
    -   Post items with images and location details.
    -   Edit and Update your own posts.
    -   Delete items you posted.
-   **Search & Filter**: Find items by status (Lost/Found) or search by keywords.
-   **Claims System**: Submit claims for items you own.
-   **Admin Panel**: Manage all posts and review claims (Approve/Reject).

## 🛠️ Tech Stack

-   **Frontend**: HTML5, CSS3, Bootstrap 5
-   **Backend**: Core PHP 8.2
-   **Database**: MySQL 8.0
-   **Environment**: Docker & Docker Compose

---

## 📦 Getting Started

### Prerequisites

Ensure you have the following installed:
-   [Docker Desktop](https://www.docker.com/products/docker-desktop)
-   [Git](https://git-scm.com/)

### Installation & Setup

1.  **Clone the Repository** (if applicable) or navigate to the project folder:
    ```bash
    cd /path/to/4semproject
    ```

2.  **Start the Application** using Docker Compose:
    ```bash
    docker-compose up -d --build
    ```
    This command builds the images and starts the containers in the background.

3.  **Initialize the Database** (Run only once):
    
    You need to import the database schema manually. Run this command:
    ```bash
    cat database.sql | docker exec -i 4semproject-db-1 mysql -uuser -ppassword project_db
    ```
    *(Note: If your container name is different, check it with `docker ps`)*

4.  **Access the Application**:
    -   **Web App**: [http://localhost:8080](http://localhost:8080)
    -   **phpMyAdmin**: [http://localhost:8081](http://localhost:8081)
        -   **Server**: `db`
        -   **Username**: `user`
        -   **Password**: `password`

---

## 🛑 Stopping the Application

To stop the containers:
```bash
docker-compose down
```

To stop and **remove database data** (start fresh):
```bash
docker-compose down -v
```

---

## � Admin Access

The default setup includes users but no default admin. To make a user an admin:

1.  Register a new account on the [Register Page](http://localhost:8080/register.php).
2.  Run the following SQL command (via phpMyAdmin or terminal):
    ```sql
    UPDATE users SET role = 'admin' WHERE email = 'YOUR_EMAIL@example.com';
    ```
3.  Log out and log back in to access the **Admin Dashboard**.

---

## 📂 Project Structure

```
/
├── assets/             # CSS, JS, Images, Uploads
├── config/             # Database connection
├── includes/           # Reusable Header, Footer, Functions
├── admin/              # Admin Panel
├── docker-compose.yml  # Docker Configuration
├── Dockerfile          # PHP Image Customization
├── database.sql        # Database Schema
└── *.php               # Application Pages
```

## ⚠️ Troubleshooting

-   **Image Upload Issues**:
    Ensure the `assets/uploads` directory exists and has write permissions.
    ```bash
    chmod 777 assets/uploads
    ```