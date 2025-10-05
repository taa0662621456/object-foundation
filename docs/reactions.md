# Reactions

Automate behavior on domain events.

- AutoLogReaction — writes an event line into `var/export/foundation.log`
- AutoExportReaction — writes JSON-LD snapshot of the entity to `var/export/auto-entities.jsonld`

CLI:
```bash
./bin/foundation foundation:reaction:list
./bin/foundation foundation:reaction:enable AutoExportReaction
./bin/foundation foundation:reaction:disable AutoLogReaction
```