<?php

define('BASE_URL', 'http://matchin.test/');
define('APP_NAME', 'MatchIn');

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}
