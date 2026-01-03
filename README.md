# Storender

**Storender** is a lightweight, self-hosted file storage and management API built with PHP 8.4 and Docker. It provides a secure, project-scoped environment for handling file uploads, downloads, and metadata, complete with a modern web dashboard.

![Dashboard Preview](https://via.placeholder.com/800x400?text=Storender+Dashboard)

## Features
- **Project Isolation**: Manage multiple projects with scoped assets.
- **Secure Storage**: Files are private by default; toggle "Public" visibility as needed.
- **API Keys**: Generate granular API keys for programmatic access to private assets.
- **Streaming Uploads**: Efficient file handling with minimal memory footprint.
- **Modern Dashboard**: Built with TailwindCSS, DaisyUI, and AlpineJS for a responsive experience.
- **Dockerized**: Ready to deploy with Nginx and PHP-FPM.

## Quick Start
1.  **Clone & Start**:
    ```bash
    docker compose up -d --build
    ```
2.  **Access Dashboard**:
    Go to [http://localhost:8080](http://localhost:8080) and register your admin account.
3.  **Start Uploading**:
    Create a project and drag-and-drop files to upload.

## Documentation
- [Deployment Guide](docs/deployment.md)
- [API Reference](docs/api.md)

## Tech Stack
- **Backend**: PHP 8.4 (No Framework), FastRoute, PHP-DI
- **Database**: SQLite (Zero config)
- **Frontend**: AlpineJS, TailwindCSS, DaisyUI
- **Server**: Nginx, Docker

## License
MIT
