# Mikhmon 3 ROS7 – Docker

**Mikhmon 3 for RouterOS 7** is a Docker-ready version of Mikhmon, designed to manage MikroTik Hotspot services through the MikroTik RouterOS API.

This Docker image uses PHP 8.4 with Apache and supports both **AMD64** and **ARM64** architectures. It is suitable for desktop servers, VPS, mini PCs, and ARM-based devices.

## Features

- Docker-based deployment with Apache and PHP 8.4.
- Designed for MikroTik RouterOS 7.
- Supports MikroTik Hotspot management and PPPoE Client.
- Multi-platform image: `linux/amd64` and `linux/arm64`.
- Docker Bridge networking.
- Persistent application configuration.
- Automatic initial configuration file creation.
- Docker Compose support.
- Suitable for local servers, VPS, mini PCs, and ARM64 devices.

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
    network_mode: bridge
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

Open Mikhmon in your browser:

```text
http://SERVER-IP:83
```

Replace `SERVER-IP` with the IP address of your Docker host.

## Network Configuration

This deployment uses Docker **Bridge Network**.

The port mapping:

```yaml
ports:
  - "83:80"
```

means:

| Component | Port |
|---|---:|
| Docker host | 83 |
| Mikhmon container (Apache) | 80 |

Access Mikhmon through the Docker host IP and port 83.

Bridge networking provides network isolation between the container and the host while allowing outbound connections to MikroTik routers, provided the network and firewall permit them.

Make sure that:

- The Docker host can reach the MikroTik router.
- The MikroTik API service is enabled.
- The configured API port is accessible.
- The MikroTik user has the required permissions.
- Firewall rules allow communication between the Docker host and MikroTik.

## Default Login Credentials

Use the following credentials for the initial application login:

| Field | Default Value |
|---|---|
| Username | `mikhmon` |
| Password | `12345` |

Change the default password after your first login.

## Directory Structure

The deployment uses a persistent host directory for application configuration:

```text
mikhmon/
├── docker-compose.yml
└── data/
    └── config.php
```

### Volume Configuration

| Host Directory | Container Path | Purpose |
|---|---|---|
| `./data` | `/opt/mikhmon/data` | Persistent application configuration |

The configuration file is automatically created during the first container initialization:

```text
data/config.php
```

The configuration is stored outside the Apache document root and remains available when the container is recreated or updated.

## Supported Platforms

This Docker image supports:

| Platform | Architecture |
|---|---|
| Linux | AMD64 |
| Linux | ARM64 |

Docker automatically selects the appropriate image architecture for the host system.

## Useful Docker Commands

### Check Container Status

```bash
docker compose ps
```

### View Application Logs

```bash
docker compose logs -f mikhmon
```

### Restart the Container

```bash
docker compose restart mikhmon
```

### Stop and Remove the Container

```bash
docker compose down
```

The persistent `data` directory is not removed by this command.

### Update the Docker Image

```bash
docker compose pull
docker compose up -d
```

### Check Container Network

```bash
docker inspect mikhmon
```

## Data Persistence and Backup

The `data` directory is mounted from the host and stores the persistent application configuration.

Back up this directory regularly:

```text
data/
```

Example backup command:

```bash
tar -czf mikhmon-data-backup.tar.gz data/
```

To restore the configuration, stop the container and restore the backed-up `data` directory before starting the container again.

**Important:** Do not delete the `data` directory when updating or recreating the container if you want to retain your application configuration.

## Security

For a safer deployment:

- Change the default Mikhmon login password after the first login.
- Restrict access to the Mikhmon web interface using a firewall or VPN.
- Do not expose the MikroTik API directly to the public internet.
- Use strong credentials for MikroTik API access.
- Do not publish your private `config.php` file.
- Back up the `data` directory regularly.
- Keep the Docker image and host operating system updated.

## Disclaimer

This project is an independent Docker packaging and customization developed from the original Mikhmon source code by **Laksa19**.

**Original Mikhmon project:**  
https://laksa19.github.io/

All credit for the original Mikhmon application goes to its original developer, **Laksa19**.

This project provides additional Docker packaging, configuration, and customizations to support modern deployment environments, including Docker-based installations and multi-platform architecture support.

MikroTik and RouterOS are trademarks of MikroTik. This project is not an official MikroTik product and is not affiliated with or endorsed by MikroTik or the original Mikhmon developer.

## Support

If you find our projects useful, consider supporting their development and maintenance.

Your support helps us continue improving our tools, services, and open-source projects.

**Choose your preferred donation method:**

- 🌍 [Donate via PayPal](https://paypal.me/idnetmyid)
- 🇮🇩 [Donate via Saweria](https://saweria.co/idnet)

Thank you for your support!