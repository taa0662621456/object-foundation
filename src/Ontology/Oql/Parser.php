<?php
namespace ObjectFoundation\Ontology\Oql;

/**
 * Very small parser for queries like:
 *   SELECT entity, traits WHERE has(LocaleAwareTrait) AND has(TranslatableTrait)
 */
final class Parser
{
    public function parse(string $q): Query
    {
        $q = trim($q);
        if (!str_starts_with(strtoupper($q), 'SELECT ')) {
            throw new \InvalidArgumentException('Query must start with SELECT');
        }
        $parts = preg_split('/\s+WHERE\s+/i', substr($q, 7), 2);
        $select = array_map('trim', explode(',', $parts[0]));
        $preds = [];

        if (isset($parts[1])) {
            // Split by AND (no precedence, minimal viable)
            $conds = preg_split('/\s+AND\s+/i', $parts[1]);
            foreach ($conds as $c) {
                $c = trim($c);
                if (preg_match('/^has\(([^)]+)\)$/i', $c, $m)) {
                    $preds[] = ['op' => 'has', 'what' => trim($m[1])];
                    continue;
                }
                if (preg_match('/^implements\(([^)]+)\)$/i', $c, $m)) {
                    $preds[] = ['op' => 'implements', 'what' => trim($m[1])];
                    continue;
                }
                if (preg_match('/^name\s+like\s+"([^"]+)"$/i', $c, $m)) {
                    $preds[] = ['op' => 'name_like', 'what' => $m[1]];
                    continue;
                }
            }
        }

        return new Query($select, $preds);
    }
}