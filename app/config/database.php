<?php
namespace App\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private $servername;
    private $dbusername;
    private $dbpassword;
    private $dbname;
    private $charset;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->servername = $_ENV['DB_HOST'];
        $this->dbusername = $_ENV['DB_USER'];
        $this->dbpassword = $_ENV['DB_PASS'];
        $this->dbname     = $_ENV['DB_NAME'];
        $this->charset    = $_ENV['DB_CHARSET'];
    }

    public function connect()
    {
        $dsn = "mysql:host={$this->servername};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $this->dbusername, $this->dbpassword, $options);
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}
