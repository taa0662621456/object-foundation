<?php
namespace ObjectFoundation\Ontology\Oql;

final class Query
{
    public function __construct(
        public array $select = [],       // ['entity', 'traits', 'interfaces']
        public array $predicates = []    // [['op' => 'has', 'what' => 'TraitFQCN'], ...]
    ) {}
}