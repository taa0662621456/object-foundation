<?php
namespace ObjectFoundation\Ontology\Oql;

final class Query
{
    public function __construct(
        public array $select = [],
        public ?array $ast = null
    ) {}
}