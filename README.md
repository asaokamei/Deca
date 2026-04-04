Deca
====

An **application starter kit** built on **Slim 4**, aimed at **years of continued use**—to keep building and maintaining real apps on the same stack.

- **Slim** handles HTTP routing and the request/response cycle.
- **Deca** layers familiar, low-dependency packages—PHP-DI, nyholm/psr7, Monolog, Twig, filp/whoops, and more—**on top of** Slim, **with** its own `core` code **to** wire them together cleanly.
- **Interfaces** (often PSR) abstract third-party boundaries so app code can survive library upgrades.

Japanese: [README.ja.md](README.ja.md). In-depth documentation (Japanese): [docs/deca/README.ja.md](docs/deca/README.ja.md).

### Installation

Requires PHP 8.0+ and [Composer](https://getcomposer.org/).

Use Composer’s [`create-project`](https://getcomposer.org/doc/03-cli.md#create-project) to create a new project and install dependencies in one step. Replace `my-app` with your directory name:

```
composer create-project wscore/deca my-app
```

### Demo 

```
cd my-app
cd public
php -S 127.0.0.1:8000 index.php
```

Then open `http://127.0.0.1:8000` in your browser.
