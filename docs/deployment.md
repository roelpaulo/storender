# Deployment Guide

## Prerequisites
- Docker & Docker Compose
- Basic understanding of terminal commands

## Quick Start
1.  **Clone the repository**:
    ```bash
    git clone <repository-url>
    cd storender
    ```

2.  **Environment Setup**:
    Copy the example environment file:
    ```bash
    cp .env.example .env
    ```
    *Note: If `.env.example` is missing, ensure `.env` has specific keys like `APP_ENV`, `DB_CONNECTION`, `SMTP_HOST`, etc.*

3.  **Start the Application**:
    ```bash
    docker compose up -d --build
    ```

4.  **Access Dashboard**:
    Open [http://localhost:8080](http://localhost:8080) in your browser.

## Configuration (.env)

| Variable | Description | Default |
| :--- | :--- | :--- |
| `APP_ENV` | Environment (`local` or `production`) | `local` |
| `PORT` | Public port for Nginx | `8080` |
| `DB_CONNECTION` | Database type (`sqlite`) | `sqlite` |
| `SMTP_HOST` | SMTP Server (e.g. `mailpit` or `smtp.gmail.com`) | `mailpit` |
| `SMTP_PORT` | SMTP Port | `1025` |

## Production Notes
- **Persistence**: Ensure the `./database` and `./storage` directories are mounted to persistent volumes if deploying to a cloud container service.
- **Security**: Change `APP_ENV` to `production` to suppress debug errors.
- **SSL**: This setup assumes an Nginx reverse proxy handles SSL termination in front of the container.
