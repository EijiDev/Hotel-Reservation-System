<?php
class Database
{
    private $servername = "localhost";
    private $dbusername = "root";
    private $dbpassword = "";
    private $dbname = "HotelReservationDB";
    private $charset = 'utf8mb4';

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
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}
