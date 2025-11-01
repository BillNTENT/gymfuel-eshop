<?php
// my_files/db_connect.php
declare(strict_types=1);

function getPDO(): PDO {
    // >>> Ρύθμισε αν χρειάζεται τα credentials σου <<<
    $db  = 'gymfuel_db';
    $host= '127.0.0.1';
    $user= 'root';
    $pass= '';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $user, $pass, $opt);
}
