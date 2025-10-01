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

    // Create a new user
    public function create($name, $email, $password, $role = 'user')
    {
        $sql = "INSERT INTO UserAccounts (Name, Email, UserPassword, role) 
                VALUES (:Name, :Email, :UserPassword, :role)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'Name' => $name,
            'Email' => $email,
            'UserPassword' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role
        ]);
    }

    // Find user by email
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM UserAccounts WHERE Email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Authenticate user
    public function authenticate($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM UserAccounts WHERE Email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['UserPassword'])) {
            return $user;
        }

        return false;
    }
}
