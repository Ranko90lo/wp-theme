# WordPress Theme

## Requirements
- Docker
- NPM
- Composer

## Installation

### 1. Local domain

Before starting Docker, add `wordpress.local` to your system hosts file.

**macOS / Linux:**
```bash
echo "127.0.0.1 wordpress.local" | sudo tee -a /etc/hosts
```

**Windows** (run Command Prompt as Administrator):
```
echo 127.0.0.1 wordpress.local >> C:\Windows\System32\drivers\etc\hosts
```

### 2. Docker

In the root of the project run the following command to start the containers.

```bash
docker-compose up -d
```

After Docker finishes the build, wait a couple of minutes for WordPress to complete the file transfer (~5 minutes). If something seems off, restart the containers:

```bash
docker-compose down && docker-compose up -d
```

| Service     | URL                          |
|-------------|------------------------------|
| WordPress   | http://wordpress.local       |
| phpMyAdmin  | http://localhost:8080        |

### 3. Composer

In the root of the project run:

```bash
composer install
```

### 4. Theme

Go into the theme directory and install dependencies:

```bash
cd themes/theme
npm install
```

## Development

```bash
# Watch assets during development
npm run dev

# Build assets for production
npm run build
```

## Code Quality

```bash
# Run PHPCS linter
composer lint

# Run PHPCS against WordPress-VIP-Go standard
composer phpcs-theme
```