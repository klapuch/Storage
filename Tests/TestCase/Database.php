<?php
declare(strict_types = 1);
namespace Klapuch\TestCase;

use Klapuch\Storage;
use Tester;

abstract class Database extends Tester\TestCase {
    /** @var Storage\Database */
    protected $database;

    protected function setUp() {
        parent::setUp();
        Tester\Environment::lock('database', __DIR__ . '/../temp');
        $this->database = $this->connection();
    }

    private function connection(): Storage\Database {
        $credentials = parse_ini_file(__DIR__ . '/.database.ini');
        $this->database = new Storage\PDODatabase(
            $credentials['host'],
            $credentials['user'],
            $credentials['pass'],
            $credentials['name']
        );
        return $this->database;
    }
}