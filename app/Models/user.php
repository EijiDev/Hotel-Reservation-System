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
     * Get user by email with role information
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("
            SELECT u.UserID, u.Name, u.Email, u.UserPassword, u.RoleID, r.RoleName
            FROM useraccounts u
            JOIN roles r ON u.RoleID = r.RoleID
            WHERE u.Email = :email 
            LIMIT 1
        ");
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Normalize field names for consistency
        if ($user && isset($user['UserPassword'])) {
            $user['Password'] = $user['UserPassword'];
            $user['Role'] = $user['RoleName'];
            unset($user['UserPassword']);
        }
        
        return $user;
    }

    /**
     * Get user by ID with role information
     */
    public function getUserById($userId)
    {
        $stmt = $this->db->prepare("
            SELECT u.UserID, u.Name, u.Email, u.RoleID, r.RoleName, u.Created_at
            FROM useraccounts u
            JOIN roles r ON u.RoleID = r.RoleID
            WHERE u.UserID = :user_id 
            LIMIT 1
        ");
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Normalize field names
        if ($user && isset($user['RoleName'])) {
            $user['Role'] = $user['RoleName'];
        }
        
        return $user;
    }

    /**
     * Create new user account
     */
    public function create($name, $email, $password, $roleId = 2)
    {
        // Check if email already exists
        if ($this->getUserByEmail($email)) {
            error_log("⚠️ Email already exists: " . $email);
            return false;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO useraccounts (Name, Email, UserPassword, RoleID) 
            VALUES (:name, :email, :password, :role_id)
        ");

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);

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
        $allowedFields = ['Name' => 'Name', 'Email' => 'Email', 'RoleID' => 'RoleID'];
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
     * Get all users with role information (for admin)
     */
    public function getAllUsers()
    {
        $stmt = $this->db->query("
            SELECT u.UserID, u.Name, u.Email, u.RoleID, r.RoleName, u.Created_at 
            FROM useraccounts u
            JOIN roles r ON u.RoleID = r.RoleID
            ORDER BY u.UserID DESC
        ");

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normalize field names
        foreach ($users as &$user) {
            if (isset($user['RoleName'])) {
                $user['Role'] = $user['RoleName'];
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

    /**
     * Get role ID by role name
     */
    public function getRoleIdByName($roleName)
    {
        $stmt = $this->db->prepare("SELECT RoleID FROM roles WHERE RoleName = :role_name LIMIT 1");
        $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['RoleID'] : null;
    }
}