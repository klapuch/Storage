<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase/PostgresDatabase.php';
require_once __DIR__ . '/TestCase/MySqlDatabase.php';

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');