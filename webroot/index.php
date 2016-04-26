<?php

//Load composer libraries
require __DIR__ . '/../vendor/autoload.php';
$boot = new \app\domain\chat\ChatService(__DIR__ . '/../');
$boot->init();
exit();