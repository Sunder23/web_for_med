# WordPress Docker Dev Environment

A Docker-based WordPress development environment for custom theme and plugin development.

## Structure

```
├── docker-compose.yml
├── .env                  # Local environment variables (not committed)
├── .env.example          # Template for .env
├── Makefile              # Convenience commands
├── theme/                # Place your custom theme(s) here
└── plugin/               # Place your custom plugin(s) here
```

## Quick Start

```bash
cp .env.example .env
docker compose up -d
```

- WordPress: http://localhost:8080
- phpMyAdmin: http://localhost:8081

## Commands

| Command       | Description                        |
|---------------|------------------------------------|
| `make up`     | Start containers                   |
| `make down`   | Stop containers                    |
| `make logs`   | Follow WordPress logs              |
| `make shell`  | Open shell in WordPress container  |
| `make fresh`  | Wipe volumes and restart fresh     |
