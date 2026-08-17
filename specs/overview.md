# System Overview — CarFleet

## 1. Domain Context

CarFleet is a fleet management administration platform designed to track vehicles, driver assignments, maintenance schedules, and operational costs.

## 2. Technical Stack & Foundation

| Layer | Technology |
|---|---|
| Framework | Laravel 13.x (PHP 8.4) |
| Admin Interface | Filament v4 (Panel Builder & Schemas) |
| Database | MySQL 8.0 (Docker Sail) |
| Cache & Queues | Redis (Docker Sail) |
| Containerization | Laravel Sail |
| Development AI | Laravel Boost & Engram |

## 3. Core Bounded Contexts

1. **Fleet Management**: Vehicle profiles, specs, status lifecycle (available, assigned, maintenance, decommissioned).
2. **Driver Management**: Driver credentials, license validation, assignment history.
3. **Operations & Maintenance**: Service schedules, repair logs, fuel & expense logs.
4. **Access Control**: Role-based access control (RBAC) integrated into Filament Admin Panel.

## 4. Next Steps

- Define domain entities and migrations for Vehicles & Drivers.
- Create Filament Resources for initial CRUD management.
- Establish unit and integration tests (Pest / PHPUnit).
