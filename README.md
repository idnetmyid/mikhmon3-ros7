# Mikhmon 3 ROS7 – Docker

**Mikhmon 3 for RouterOS 7** is a Docker-ready version of Mikhmon, designed to manage MikroTik Hotspot services through the MikroTik RouterOS API.

This image uses PHP 8.4 with Apache and supports both **AMD64** and **ARM64** architectures, making it suitable for desktop servers, VPS, mini PCs, and ARM-based devices.

## Features

- Docker-based deployment with Apache and PHP 8.4.
- Designed for MikroTik RouterOS 7.
- Supports MikroTik Hotspot management and PPPoE Client.
- Multi-platform image: `linux/amd64` and `linux/arm64`.
- Persistent configuration and application data.
- Automatic initial configuration file creation.
- Docker Compose support.
- Application source files can be managed through a host directory.
- Suitable for local servers, VPS, and ARM64 devices.

## Docker Image

**Docker Hub:**  
https://hub.docker.com/r/idnetmyid/mikhmon3-ros7

Pull the latest image:

```bash
docker pull idnetmyid/mikhmon3-ros7:latest
```

## Installation

### 1. Create a Project Directory

```bash
mkdir mikhmon
cd mikhmon
```

### 2. Create docker-compose.yml

Create a file named `docker-compose.yml` with the following content:

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
    restart: unless-stopped
```

### 3. Start the Container

```bash
docker compose up -d
```

### 4. Access Mikhmon

Open the application in your browser:

```text
http://SERVER-IP:83
```

Replace `SERVER-IP` with the IP address of your Docker host.

## Default Login Credentials

Use the following credentials for the initial application login:

| Field | Default Value |
|---|---|
| Username | `mikhmon` |
| Password | `12345` |

## Directory Structure

The deployment uses persistent host directories:

```text
mikhmon/
├── docker-compose.yml
└──  data/
    └── config.php
```

### Volume Configuration

| Host Directory | Container Path | Purpose |
|---|---|---|
| `./data` | `/opt/mikhmon/data` | Persistent configuration |

The default configuration file is created automatically at:

```text
data/config.php
```

This keeps configuration data separate from the application source files.

## MikroTik Connection

Configure your MikroTik router connection through the Mikhmon application.

Make sure that:

- MikroTik API service is enabled.
- The router is reachable from the Docker container.
- The API port is accessible through your network.
- The configured MikroTik user has the required permissions.

## Supported Platforms

This Docker image supports the following platforms:

| Platform | Architecture |
|---|---|
| Linux | AMD64 |
| Linux | ARM64 |

Docker automatically selects the appropriate image for the host architecture.

## Useful Docker Commands

Check container status:

```bash
docker compose ps
```

View application logs:

```bash
docker compose logs -f mikhmon
```

Restart the application:

```bash
docker compose restart mikhmon
```

Stop the application:

```bash
docker compose down
```

Update the image:

```bash
docker compose pull
docker compose up -d
```

## Data Persistence and Backup

The `data` directories are mounted from the host. This means configuration and application files remain available outside the container.

For backup, save directories:

```text
data/
```

Do not delete these directories when recreating the container if you want to retain your configuration and application files.

## Security

For a safer deployment:

- Change the default Mikhmon login password after the first login, if password changes are supported by your application setup.
- Avoid using default credentials on publicly accessible installations.
- Restrict access to the Mikhmon web interface using a firewall or VPN.
- Do not expose the MikroTik API directly to the public internet.
- Use strong credentials for MikroTik API access.
- Do not publish your private `config.php` file.
- Back up the `data` directory regularly.

## Disclaimer

This project is an independent Docker packaging and customization developed from the original Mikhmon source code by **Laksa19**.

**Original Mikhmon project:**  
https://laksa19.github.io/

All credit for the original Mikhmon application goes to its original developer, **Laksa19**.

This project provides additional Docker packaging, configuration, and customizations to support modern deployment environments, including Docker-based installations and multi-platform architecture support.

MikroTik and RouterOS are trademarks of MikroTik. This project is not an official MikroTik product and is not affiliated with or endorsed by MikroTik or the original Mikhmon developer.