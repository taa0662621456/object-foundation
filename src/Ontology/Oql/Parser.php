<?php
namespace ObjectFoundation\Ontology\Oql;

use InvalidArgumentException;

final class Parser
{
    private array $tokens = [];
    private int $pos = 0;

    public function parse(string $q): Query
    {
        $q = trim($q);
        if (stripos($q, 'SELECT ') !== 0) {
            throw new InvalidArgumentException('Query must start with SELECT');
        }
        $rest = substr($q, 7);
        $parts = preg_split('/\s+WHERE\s+/i', $rest, 2);
        $select = array_map('trim', explode(',', $parts[0]));

        $ast = null;
        if (isset($parts[1])) {
            $this->tokens = $this->tokenize($parts[1]);
            $this->pos = 0;
            $ast = $this->parseExpr();
        }

        return new Query($select, $ast);
    }

    private function tokenize(string $s): array
    {
        $out = [];
        $len = strlen($s);
        $i = 0;
        while ($i < $len) {
            $ch = $s[$i];
            if (ctype_space($ch)) { $i++; continue; }
            if ($ch === '(' || $ch === ')') { $out[] = $ch; $i++; continue; }
            if ($ch === '"') {
                $j = $i+1; $buf = '';
                while ($j < $len && $s[$j] !== '"') { $buf .= $s[$j]; $j++; }
                if ($j >= $len) throw new InvalidArgumentException('Unclosed string');
                $out[] = ['STRING', $buf];
                $i = $j+1; continue;
            }
            // read word
            $j = $i; $buf = '';
            while ($j < $len && preg_match('/[A-Za-z0-9_\\\\]/', $s[$j])) { $buf .= $s[$j]; $j++; }
            if ($buf !== '') {
                $kw = strtoupper($buf);
                if (in_array($kw, ['AND','OR','NOT','HAS','IMPLEMENTS','NAME','LIKE'])) {
                    $out[] = $kw;
                } else {
                    $out[] = ['IDENT', $buf];
                }
                $i = $j; continue;
            }
            // punctuation like commas ignored
            $i++;
        }
        return $out;
    }

    private function peek() {
        return $this->tokens[$this->pos + 0] ?? null; }
    private function eat($expected = null) {
        $tok = $this->tokens[$this->pos] ?? null;
        if ($expected !== null) {
            if ($tok !== $expected && !($expected === 'IDENT' && is_array($tok) && $tok[0]==='IDENT')
                && !($expected === 'STRING' && is_array($tok) && $tok[0]==='STRING')) {
                throw new InvalidArgumentException('Unexpected token');
            }
        }
        $this->pos++;
        return $tok;
    }

    // Grammar (Pratt/recursive):
    // expr := orExpr
    // orExpr := andExpr (OR andExpr)*
    // andExpr := unaryExpr (AND unaryExpr)*
    // unaryExpr := NOT unaryExpr | primary
    // primary := predicate | '(' expr ')'
    private function parseExpr() { return $this->parseOr(); }
    private function parseOr() {
        $node = $this->parseAnd();
        while (($t=$this->peek()) === 'OR') {
            $this->eat('OR');
            $rhs = $this->parseAnd();
            $node = ['type'=>'op','op'=>'or','left'=>$node,'right'=>$rhs];
        }
        return $node;
    }
    private function parseAnd() {
        $node = $this->parseUnary();
        while (($t=$this->peek()) === 'AND') {
            $this->eat('AND');
            $rhs = $this->parseUnary();
            $node = ['type'=>'op','op'=>'and','left'=>$node,'right'=>$rhs];
        }
        return $node;
    }
    private function parseUnary() {
        if ($this->peek() === 'NOT') {
            $this->eat('NOT');
            $node = $this->parseUnary();
            return ['type'=>'op','op'=>'not','node'=>$node];
        }
        return $this->parsePrimary();
    }
    private function parsePrimary() {
        $t = $this->peek();
        if ($t === '(') {
            $this->eat('(');
            $node = $this->parseExpr();
            $this->eat(')');
            return $node;
        }
        return $this->parsePredicate();
    }
    private function parsePredicate() {
        $t = $this->peek();
        if ($t === 'HAS') {
            $this->eat('HAS');
            $nameTok = $this->eat('IDENT');
            $what = is_array($nameTok) ? $nameTok[1] : '';
            return ['type'=>'pred','op'=>'has','what'=>$what];
        }
        if ($t === 'IMPLEMENTS') {
            $this->eat('IMPLEMENTS');
            $nameTok = $this->eat('IDENT');
            $what = is_array($nameTok) ? $nameTok[1] : '';
            return ['type'=>'pred','op'=>'implements','what'=>$what];
        }
        if ($t === 'NAME') {
            $this->eat('NAME');
            $this->eat('LIKE');
            $strTok = $this->eat('STRING');
            $substr = is_array($strTok) ? $strTok[1] : '';
            return ['type'=>'pred','op'=>'name_like','what'=>$substr];
        }
        throw new InvalidArgumentException('Unknown predicate near token: '.print_r($t,true));
    }
}
