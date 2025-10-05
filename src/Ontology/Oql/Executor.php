<?php
namespace ObjectFoundation\Ontology\Oql;

use ReflectionClass;

final class Executor
{
    /**
     * @param Query $query
     * @param string[] $classes
     * @return array<int, array<string,mixed>>
     */
    public function run(Query $query, array $classes): array
    {
        $rows = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) continue;
            $ref = new ReflectionClass($class);
            $traits = array_keys($ref->getTraits());
            $interfaces = array_values($ref->getInterfaceNames());

            if ($query->ast && !$this->evalAst($query->ast, $ref, $traits, $interfaces)) {
                continue;
            }

            $row = [];
            foreach ($query->select as $field) {
                $f = strtolower(trim($field));
                switch ($f) {
                    case 'entity': $row['entity'] = $ref->getName(); break;
                    case 'traits': $row['traits'] = $traits; break;
                    case 'interfaces': $row['interfaces'] = $interfaces; break;
                    default: $row[$f] = null; // ignore
                }
            }
            if (!$row) { // default columns if none specified
                $row = ['entity'=>$ref->getName(),'traits'=>$traits,'interfaces'=>$interfaces];
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function evalAst(array $node, ReflectionClass $ref, array $traits, array $interfaces): bool
    {
        $type = $node['type'] ?? null;
        if ($type === 'pred') {
            $op = strtolower($node['op'] ?? '');
            $what = $node['what'] ?? '';
            if ($op === 'has') {
                foreach ($traits as $t) {
                    if (str_ends_with($t, '\\' . $what) || $t === $what) return true;
                }
                return false;
            }
            if ($op === 'implements') {
                foreach ($interfaces as $i) {
                    if (str_ends_with($i, '\\' . $what) || $i === $what) return true;
                }
                return false;
            }
            if ($op === 'name_like') {
                return stripos($ref->getName(), $what) !== false;
            }
            return false;
        }
        if ($type === 'op') {
            $op = strtolower($node['op'] ?? '');
            if ($op === 'not') {
                return !$this->evalAst($node['node'], $ref, $traits, $interfaces);
            }
            if ($op === 'and') {
                return $this->evalAst($node['left'], $ref, $traits, $interfaces) && $this->evalAst($node['right'], $ref, $traits, $interfaces);
            }
            if ($op === 'or') {
                return $this->evalAst($node['left'], $ref, $traits, $interfaces) || $this->evalAst($node['right'], $ref, $traits, $interfaces);
            }
        }
        return false;
    }
}
