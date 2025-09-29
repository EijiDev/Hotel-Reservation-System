<?php
namespace App\Models;
use PDO;
class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($email, $name, $password)
    {
        $sql = "INSERT INTO UserAccounts (Name, Email, UserPassword) VALUES (:Name, :Email, :UserPassword)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->fetch()) {   
            return false;
        }
        return $stmt->execute([
            'Name' => $name,
            'Email' => $email,
            'UserPassword' => $password
        ]);
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM UserAccounts WHERE Email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
