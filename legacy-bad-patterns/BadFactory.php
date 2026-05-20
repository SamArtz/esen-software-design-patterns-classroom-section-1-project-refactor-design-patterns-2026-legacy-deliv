<?php

// Anti-pattern: Factory para un objeto sin variación.
// No hay razón para el Factory aquí. SimpleLogger no tiene variantes.
// En S3 se aprende cuándo usar Factory vs cuándo no.
class BadFactory
{
    public static function create(): SimpleLogger
    {
        return new SimpleLogger(); // SimpleLogger no tiene variantes
    }
}

class SimpleLogger
{
    public function log(string $msg): void { error_log($msg); }
}
