<?php

if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

file_put_contents("php://stdout", $_SERVER['REQUEST_URI'] . PHP_EOL);

include __DIR__ . '/public/index.php';