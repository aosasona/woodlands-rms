# RMS

> [!WARNING]
> Please do NOT push to main, we do not pay for GitHub Team plan so we cannot enforce branch rules, but PLEASE DO NOT PUSH TO `main` or `master`.

This is the code for the record management system.

You can see the documentation for the Phlo framework [here](http://github.com/aosasona/phlo-template)

# Development

## Windows

You need to manually copy the core repo to this root of this project and ensure you do not commit it as Windows does not respect symlinks unlike UNIX(-like) operating systems. It is also recommended to have your folder structure like shown below so that docker compose can setup symlinks for you after the docker build completes.

```
root
├── core
│   ├── composer.json
│   ├── composer.lock
│   ├── data
│   ├── docs
│   ├── migrate
│   ├── src
│   └── vendor
└── rms
    ├── Dockerfile
    ├── LICENSE
    ├── README.md
    ├── api
    ├── composer.json
    ├── composer.lock
    ├── deployment
    ├── docker-compose.yml
    ├── fly.toml
    ├── index.php
    ├── pages
    ├── public
    ├── src
    └── vendor
```

```sh
docker compose up
```

## UNIX-based Operating systems (and WSL)

For UNIX-based operating systems, there is an helper script to scaffold the development environment the first time around, it works around Docker's requirement for our `core` directory to be a proper directory instead of a symlink, but we need it to be a symlink so that we can work on the core directory and have changes appear instantly.

```sh
chmod +x ./deployment/dev.sh
./deployment/dev.sh
```

# Services

| **Service**                  | **URL**               | **Port(s)** |
| :--------------------------- | :-------------------- | :---------- |
| RMS web                      | http://127.0.0.1:8080 | 8080        |
| MySQL database               | http://127.0.0.1:3306 | 3306        |
| PHPMyAdmin Database Explorer | http://127.0.0.1:9090 | 9090        |
