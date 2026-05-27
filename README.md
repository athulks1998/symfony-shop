# symfony-shop

Full-stack e-commerce admin panel — Symfony 7 REST API backend + React/TypeScript frontend.

## Tech Stack

**Backend**
- PHP 8.2 + Symfony 7
- Doctrine ORM + MySQL 8
- JWT Authentication (LexikJWTAuthenticationBundle)
- Twig (for any SSR views)
- Docker + Apache

**Frontend**
- React 18 + TypeScript
- Vite
- Axios
- React Router v6
- Tailwind CSS

## Features

- **Product management** — CRUD with pagination, category filtering, soft-delete
- **Order management** — Create orders with stock reservation (transactional), status state machine
- **Data validation** — DTO-based validation with Symfony Validator
- **Global error handling** — ExceptionSubscriber maps domain exceptions to JSON responses
- **CORS** — NelmioCorsBundle configured for local development

## Project Structure

```
symfony-shop/
├── symfony-backend/      # Symfony 7 API
│   ├── src/
│   │   ├── Controller/   # ProductController, OrderController
│   │   ├── Entity/       # Product, Order, OrderItem (Doctrine entities)
│   │   ├── Service/      # ProductService, OrderService (business logic)
│   │   ├── DTO/          # Input DTOs with validation constraints
│   │   ├── Repository/   # Doctrine repositories with custom queries
│   │   ├── Exception/    # Domain exceptions
│   │   └── EventSubscriber/  # Global exception → JSON response mapping
│   ├── Dockerfile
│   └── docker-compose.yml
└── react-frontend/       # React + TypeScript SPA
    ├── src/
    │   ├── components/   # Pages and layout components
    │   ├── hooks/        # useProducts, useOrders
    │   ├── services/     # Axios API client
    │   └── types/        # TypeScript interfaces
    └── vite.config.ts
```

## Getting Started

### Backend

```bash
cd symfony-backend
docker-compose up -d
docker exec symfony_app php bin/console doctrine:migrations:migrate
```

API available at `http://localhost:8000/api`

### Frontend

```bash
cd react-frontend
npm install
npm run dev
```

App available at `http://localhost:3000`

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | List products (paginated, filterable) |
| GET | `/api/products/{id}` | Get single product |
| POST | `/api/products` | Create product |
| PUT | `/api/products/{id}` | Update product |
| DELETE | `/api/products/{id}` | Soft-delete product |
| GET | `/api/orders` | List orders (filterable by status/email) |
| GET | `/api/orders/{id}` | Get single order |
| POST | `/api/orders` | Create order (with stock reservation) |
| PATCH | `/api/orders/{id}/status` | Update order status |
