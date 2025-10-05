<?php
namespace ObjectFoundation\Ontology\Oql;

final class Executor
{
    /**
     * @param string[] $classes FQCN list
     * @return array<int, array<string,mixed>>
     */
    public function run(Query $query, array $classes): array
    {
        $rows = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) continue;
            $ref = new \ReflectionClass($class);
            $traits = array_keys($ref->getTraits());
            $interfaces = array_values($ref->getInterfaceNames());

            if (!$this->matchPredicates($query->predicates, $ref, $traits, $interfaces)) continue;

            $row = [];
            foreach ($query->select as $field) {
                $f = strtolower($field);
                switch ($f) {
                    case 'entity':
                        $row['entity'] = $ref->getName();
                        break;
                    case 'traits':
                        $row['traits'] = $traits;
                        break;
                    case 'interfaces':
                        $row['interfaces'] = $interfaces;
                        break;
                    default:
                        // ignore unknown fields gracefully
                        $row[$f] = null;
                }
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function matchPredicates(array $preds, \ReflectionClass $ref, array $traits, array $interfaces): bool
    {
        foreach ($preds as $p) {
            $op = strtolower($p['op'] ?? '');
            $what = $p['what'] ?? '';
            if ($op === 'has') {
                $found = false;
                foreach ($traits as $t) {
                    if (str_ends_with($t, '\\' . $what) || $t === $what) { $found = true; break; }
                }
                if (!$found) return false;
            } elseif ($op === 'implements') {
                $found = false;
                foreach ($interfaces as $i) {
                    if (str_ends_with($i, '\\' . $what) || $i === $what) { $found = true; break; }
                }
                if (!$found) return false;
            } elseif ($op === 'name_like') {
                $name = $ref->getName();
                if (stripos($name, $what) === false) return false;
            }
        }
        return true;
    }
}