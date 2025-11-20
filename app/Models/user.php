<?php

namespace App\Models;

use PDO;

class User
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("
            SELECT UserID, Name, Email, UserPassword, role 
            FROM useraccounts 
            WHERE Email = :email 
            LIMIT 1
        ");
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Rename UserPassword to Password for consistency in controllers
        if ($user && isset($user['UserPassword'])) {
            $user['Password'] = $user['UserPassword'];
            $user['Role'] = $user['role']; // Also normalize role
            unset($user['UserPassword']);
            unset($user['role']);
        }
        
        return $user;
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId)
    {
        $stmt = $this->db->prepare("
            SELECT UserID, Name, Email, role 
            FROM useraccounts 
            WHERE UserID = :user_id 
            LIMIT 1
        ");
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Normalize role column name
        if ($user && isset($user['role'])) {
            $user['Role'] = $user['role'];
            unset($user['role']);
        }
        
        return $user;
    }

    /**
     * Create new user account
     */
    public function create($name, $email, $password, $role = 'user')
    {
        // Check if email already exists
        if ($this->getUserByEmail($email)) {
            error_log("⚠️ Email already exists: " . $email);
            return false;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO useraccounts (Name, Email, UserPassword, role) 
            VALUES (:name, :email, :password, :role)
        ");

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                $userId = $this->db->lastInsertId();
                error_log("✅ User created successfully. UserID: " . $userId);
                return $userId;
            }
        } catch (\PDOException $e) {
            error_log("❌ Failed to create user: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            UPDATE useraccounts 
            SET UserPassword = :password 
            WHERE UserID = :user_id
        ");

        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Update user information
     */
    public function updateUser($userId, $data)
    {
        $allowedFields = ['Name' => 'Name', 'Email' => 'Email', 'role' => 'role'];
        $updateFields = [];
        $params = [':user_id' => $userId];

        foreach ($data as $key => $value) {
            if (isset($allowedFields[$key])) {
                $dbColumn = $allowedFields[$key];
                $updateFields[] = "$dbColumn = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE useraccounts SET " . implode(', ', $updateFields) . " WHERE UserID = :user_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete user
     */
    public function delete($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM useraccounts WHERE UserID = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get all users (for admin)
     */
    public function getAllUsers()
    {
        $stmt = $this->db->query("
            SELECT UserID, Name, Email, role, Created_at 
            FROM useraccounts 
            ORDER BY UserID DESC
        ");

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normalize role column
        foreach ($users as &$user) {
            if (isset($user['role'])) {
                $user['Role'] = $user['role'];
                unset($user['role']);
            }
        }
        
        return $users;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email)
    {
        return (bool) $this->getUserByEmail($email);
    }
}