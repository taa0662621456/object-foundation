# Object Foundation — Roadmap

## v1.1.x (Practical Foundation)
- Doctrine Embeddables for core VOs (timestamps, lock info, workflow, IP restriction).
- Domain Events: EntityCreatedEvent, EntityUpdatedEvent, ConfigChangedEvent, SoftDeletedEvent.
- Symfony Console Toolkit: `foundation:entity:info` (+ scaffolding later).
- Config Encryption Layer (design & PoC).

## v1.2.x
- Production-ready encryption for `ConfigurableTrait` (AES-256, key rotation, adapters).
- CLI: `foundation:config:encrypt`, `foundation:config:rotate-key`.

## v1.3.x
- i18n: `LocaleAwareTrait`, `TranslatableTrait` (strategy for Doctrine).

## v1.4.x
- CLI: `foundation:entity:init`, `foundation:ontology:export` (JSON-LD/RDF).
- Packaged examples and recipes for Symfony / Laravel / pure PHP.

## v2.x — Ontological Layer
- Ontology Manifest system per entity.
- Introspection API (REST/GraphQL).
- Ontology Browser UI (React/Vue).

## v3.x — Ontology Engine
- Digital Fingerprint per entity.
- Semantic export (JSON-LD/RDF).
- Ontology query endpoint and graph indexer integration.