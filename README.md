# Mikhmon 3 ROS7 – Docker

**Mikhmon 3 for RouterOS7** is a Docker-ready version of Mikhmon, designed to manage MikroTik Hotspot services through the MikroTik RouterOS API.

This project packages and customizes the original Mikhmon application for modern Docker-based deployment environments.

It uses **PHP 8.4 with Apache** and supports both **AMD64** and **ARM64** architectures.

## Features

- Docker-based deployment using Apache and PHP 8.4.
- Designed for MikroTik RouterOS 7.
- MikroTik Hotspot management.
- Multi-platform support: `linux/amd64` and `linux/arm64`.
- Persistent application configuration.
- Automatic initial configuration file creation.
- Docker Compose support.
- Application source files can be managed through a host directory.
- Suitable for VPS, mini PCs, home servers, and ARM64 devices.

## Docker Image

Docker Hub:

https://hub.docker.com/r/idnetmyid/mikhmon3-ros7

Pull the latest image:

```bash
docker pull idnetmyid/mikhmon3-ros7:latest
```

## Requirements

- Docker Engine
- Docker Compose V2
- Internet connection for pulling the Docker image
- MikroTik RouterOS with API service enabled

## Installation

### 1. Create Project Directory

```bash
mkdir mikhmon
cd mikhmon
```

### 2. Create docker-compose.yml

Create a file named `docker-compose.yml`:

```yaml
services:
  mikhmon:
    image: idnetmyid/mikhmon3-ros7:latest
    container_name: mikhmon
    ports:
      - "83:80"
    environment:
      - TZ=Asia/Jakarta
    volumes:
      - ./data:/opt/mikhmon/data
      - ./web:/var/www/html
    restart: unless-stopped
```

### 3. Start Mikhmon

Run:

```bash
docker compose up -d
```

Docker will download the image and create the container.

### 4. Access Mikhmon

Open your browser:

```text
http://SERVER-IP:83
```

Replace `SERVER-IP` with your Docker host IP address.

## Directory Structure

The deployment uses the following directory structure:

```text
mikhmon/
├── docker-compose.yml
├── data/
│   └── config.php
└── web/
    ├── index.php
    ├── include/
    ├── report/
    └── other application files
```

### Volume Mapping

| Host Directory | Container Path | Description |
|---|---|---|
| `./data` | `/opt/mikhmon/data` | Persistent configuration |
| `./web` | `/var/www/html` | Application web files |

The container automatically initializes the application files when the web directory is empty.

The default configuration file is automatically created at:

```text
data/config.php
```

The application accesses this configuration through a symbolic link:

```text
web/include/config.php
```

This design keeps configuration data separate from application source files.

## Configuration

### Timezone

The default timezone is:

```yaml
environment:
  - TZ=Asia/Jakarta
```

This setting configures the container timezone to Western Indonesia Time (WIB).

### MikroTik Router Connection

Configure your MikroTik router connection through the Mikhmon application.

Make sure:

- MikroTik API service is enabled.
- The Docker container can reach the MikroTik router.
- The API port is accessible through your network.
- The configured MikroTik user has the required permissions.

## Supported Platforms

The Docker image supports:

| Operating System | Architecture |
|---|---|
| Linux | AMD64 |
| Linux | ARM64 |

Docker automatically selects the appropriate image architecture for the host system.

## Docker Management Commands

### Check Container Status

```bash
docker compose ps
```

### View Logs

```bash
docker compose logs -f mikhmon
```

### Restart Container

```bash
docker compose restart mikhmon
```

### Stop Container

```bash
docker compose down
```

### Update to Latest Image

```bash
docker compose pull
docker compose up -d
```

## Data Persistence and Backup

The following directories are mounted from the host:

- `data/` – stores persistent Mikhmon configuration.
- `web/` – stores application files.

For backup, save both directories.

```text
data/
web/
```

Do not delete these directories when recreating the container if you want to retain your configuration and application files.

## Security Recommendations

- Do not expose the MikroTik API directly to the public internet.
- Use strong credentials for MikroTik API access.
- Restrict access to the Mikhmon web interface.
- Use a firewall or VPN for remote access.
- Back up the `data/` directory regularly.
- Never publish private configuration files or router credentials.

## Development and Deployment

This project uses Docker Buildx to build multi-platform images.

Build and publish the latest image:

```bash
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -t idnetmyid/mikhmon3-ros7:latest \
  --push .
```

## Disclaimer

This project is an independent Docker packaging and customization developed from the original Mikhmon source code by **Laksa19**.

Original Mikhmon project:

https://laksa19.github.io/

All credit for the original Mikhmon application goes to its original developer, **Laksa19**.

This project provides additional Docker packaging, configuration, and customizations to support modern deployment environments, including multi-platform architecture support.

MikroTik and RouterOS are trademarks of MikroTik. This project is not an official MikroTik product and is not affiliated with or endorsed by MikroTik or the original Mikhmon developer.