# Project Conventions — CarFleet

These rules define how this Laravel 13 application is structured. Follow them for all new code. Prefer pragmatic SOLID: clear organization without ceremonial layers.

## Directory Layout (Type First, Area Second)

Top-level folders under `app/` are **by role/type**, not by vertical domain modules.  
**Do not** create trees like `app/Fleet/Actions` or `app/Domains/Vehicles/...` unless explicitly requested.

Inside each type folder, group by **area** (plural noun: `Vehicles`, `Drivers`, `Assignments`, `Maintenance`, `Expenses`, `Documents`, …) when there is more than a one-off class—or as soon as a cluster forms.

```text
app/
  Actions/
    Vehicles/        # e.g., RegisterVehicleAction, DecommissionVehicleAction
    Drivers/         # e.g., AssignLicenseAction
    Assignments/     # e.g., AssignVehicleToDriverAction
  Services/
    Maintenance/     # e.g., MaintenanceCalculatorService
  DTOs/
    Vehicles/
  Enums/
    Vehicles/        # VehicleStatusEnum, VehicleTypeEnum
    Drivers/         # DriverLicenseStatusEnum
    Maintenance/     # MaintenanceStatusEnum
  Exceptions/
    Vehicles/
  Contracts/
    Telematics/      # External interfaces (e.g. GPSProviderInterface)
  Gateways/
    Telematics/
  Models/            # Eloquent stays flat by default
  Http/
  Providers/
  Filament/
```

### Namespace = Path

| Path | Namespace Example |
|---|---|
| `app/Actions/Vehicles/CreateVehicleAction.php` | `App\Actions\Vehicles\CreateVehicleAction` |
| `app/DTOs/Vehicles/UpsertVehicleDTO.php` | `App\DTOs\Vehicles\UpsertVehicleDTO` |
| `app/Enums/Vehicles/VehicleStatusEnum.php` | `App\Enums\Vehicles\VehicleStatusEnum` |
| `app/Exceptions/Vehicles/VehicleNotAvailableException.php` | `App\Exceptions\Vehicles\VehicleNotAvailableException` |

### Area Folder Rules

- Prefer **one area name** per cluster and reuse it across types (`Actions/Vehicles`, `Services/Vehicles`, `DTOs/Vehicles`).
- Models remain flat under `app/Models` unless the team explicitly adopts model area folders.
- Do not add new top-level `app/` folders without approval.

## Naming Suffixes (Files and Classes)

Every class name and filename must include its role as a suffix to avoid ambiguity:

| Role | Suffix | Example Class / File |
|---|---|---|
| Action | `Action` | `Actions/Assignments/AssignVehicleAction.php` |
| Service | `Service` | `Services/Maintenance/MaintenanceScheduleService.php` |
| DTO | `DTO` | `DTOs/Vehicles/CreateVehicleDTO.php` |
| Enum | `Enum` | `Enums/Vehicles/VehicleStatusEnum.php` |
| Contract / Interface | `Interface` | `Contracts/Telematics/GPSProviderInterface.php` |
| Gateway | `Gateway` | `Gateways/Telematics/SamsaraGPSGateway.php` |

Actions are named as verbs + suffix: `RegisterVehicleAction`, `CompleteMaintenanceAction`.  
Services are named as capabilities + suffix: `FleetCostCalculatorService`.  
DTOs use the `DTO` suffix only.

## Actions vs Services vs Models

| Piece | Responsibility |
|---|---|
| **Action** | One use case / domain verb. Preferred entry point from Controllers, Livewire, Filament, Jobs. Invokable (`__invoke`). |
| **Service** | Reusable capability within an area of the domain, shared by multiple Actions or callers. |
| **Model** | Persistence, relations, scopes, local invariants. Not full use-case orchestration. |

Rules:
- Prefer **Action** for a single, concrete use case.
- Keep Filament resources, Livewire components, and Controllers thin: validate/authorize at the edge, then invoke an Action.

## DTOs

- Name: `{Purpose}DTO` (e.g., `RegisterVehicleDTO`).
- Prefer `readonly` classes with constructor property promotion and strict types (`declare(strict_types=1);`).
- Validation lives at the edge (Form Request, Livewire rules, Filament schemas). The Action receives an already-validated/normalized DTO.

## Contracts, Gateways, and Dependency Injection

- Use **interfaces** (`*Interface` in `app/Contracts`) and **gateways** (`*Gateway` in `app/Gateways`) for external I/O (GPS integrations, third-party SMS, fuel card providers).
- Bind interface → gateway in `AppServiceProvider`.
- Depend on the interface, never on concrete gateways inside Actions or domain logic.

## Enums and Migrations

- Encapsulate fixed domain vocabularies in **backed enums** under `app/Enums/{Area}/*Enum.php`.
- Prefer `string` backed enums.
- Put Filament/UI helper methods on the enum (`label()`, `color()`, `icon()`).
- Do **NOT** use DB native `ENUM` columns in migrations. Use `string` and cast to the backed enum on the model.

```php
// Migration
$table->string('status', 32)->default('available');

// Model
protected function casts(): array
{
    return [
        'status' => VehicleStatusEnum::class,
    ];
}
```

## Typing and PHPDoc

- Every PHP file must begin with `declare(strict_types=1);`.
- Strict type hints on parameters, return types, and properties.
- PHPDoc is used only when adding necessary context (e.g. `@throws`, generics, array shapes). Avoid redundant PHPDocs that repeat native type hints.

## Cross-Cutting Rules

- Open multi-model writes inside Actions with `DB::transaction`.
- Authorize at the edge or at the start of the Action (policies/gates).
- Follow Pint styling: `./vendor/bin/sail pint` before committing.
