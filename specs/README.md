# Spec-Driven Development (SDD) Guidelines — CarFleet

Welcome to the **CarFleet** SDD specification directory. This directory holds authoritative, version-controlled architecture, domain models, and feature specifications that guide development.

## Structure

```
specs/
├── README.md           # This specification guide
├── overview.md         # Project domain overview and system boundaries
├── architecture.md     # Technical design, stack, and architectural decisions
└── features/           # Individual feature specifications (e.g., vehicle-management.md)
```

## Core Principles

1. **Spec First, Code Second**: No feature branch should be implemented without a reviewed spec defining domain models, validation contracts, and user flows.
2. **Single Source of Truth**: Specs are maintained in Git alongside code. Pull requests MUST update specs when requirements or domain models evolve.
3. **Traceability**: Code commits, Filament resources, and tests should directly reference the spec items they fulfill.

## Specification Template

When creating a new feature spec under `specs/features/`, follow this layout:

- **Goal & Value**: Business objective and user story.
- **Domain Model**: Entities, attributes, relationships, and invariants.
- **UI / Filament Panel Design**: Schemas, forms, tables, actions, and access control.
- **API & Data Contracts**: Endpoints, input validation, and event triggers.
- **Acceptance Criteria**: Concrete Given-When-Then scenarios for automated testing.
