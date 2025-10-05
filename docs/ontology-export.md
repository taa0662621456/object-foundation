# Ontology Export

Export to JSON-LD:
```bash
./bin/foundation foundation:ontology:export -f jsonld -o var/export/ontology.jsonld "App\\Entity\\Product"
```

Export GraphQL & OpenAPI:
```bash
./bin/foundation foundation:ontology:graphql -o var/export/schema.graphql "App\\Entity\\Product"
./bin/foundation foundation:ontology:openapi -o var/export/openapi.yaml "App\\Entity\\Product"
```