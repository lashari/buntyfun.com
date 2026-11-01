# BuntyFun.com

A shareable, repeat-visit entertainment platform (quizzes, games, birthday wishes, Islamic names,
and more). Laravel 12 + MySQL, deployed to the same WebSouls cPanel account as eDirect360.

- Why this stack: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md)
- Local dev, build, deploy, rollback: [`docs/DEPLOY.md`](docs/DEPLOY.md)
- QA acceptance criteria: [`docs/QA_ACCEPTANCE.md`](docs/QA_ACCEPTANCE.md)

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
npm install
npm run dev     # in one terminal
php artisan serve   # in another
```
