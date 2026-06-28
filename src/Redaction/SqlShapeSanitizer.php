<?php

namespace Nerrowake\Strata\Redaction;

class SqlShapeSanitizer
{
    public function sanitize(string $sql): string
    {
        $shape = preg_replace("/'(?:''|[^'])*'/", '?', $sql) ?? $sql;
        $shape = preg_replace('/"(?:\\"|[^"])*"/', '?', $shape) ?? $shape;
        $shape = preg_replace('/\b\d+(?:\.\d+)?\b/', '?', $shape) ?? $shape;
        $shape = preg_replace('/\s+/', ' ', $shape) ?? $shape;

        return trim($shape);
    }
}
