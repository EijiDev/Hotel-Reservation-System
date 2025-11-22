<?php

namespace App\Models;

use PDO;

class Role
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all roles
     */
    public function getAllRoles()
    {
        $stmt = $this->db->query("
            SELECT RoleID, RoleName 
            FROM roles 
            ORDER BY RoleID ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get role by ID
     */
    public function getRoleById($roleId)
    {
        $stmt = $this->db->prepare("
            SELECT RoleID, RoleName 
            FROM roles 
            WHERE RoleID = :role_id 
            LIMIT 1
        ");

        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get role by name
     */
    public function getRoleByName($roleName)
    {
        $stmt = $this->db->prepare("
            SELECT RoleID, RoleName 
            FROM roles 
            WHERE RoleName = :role_name 
            LIMIT 1
        ");

        $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new role
     */
    public function create($roleName)
    {
        // Check if role already exists
        if ($this->getRoleByName($roleName)) {
            error_log("⚠️ Role already exists: {$roleName}");
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO roles (RoleName) 
            VALUES (:role_name)
        ");

        $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                $roleId = $this->db->lastInsertId();
                error_log("✅ Role created successfully. RoleID: {$roleId}");
                return $roleId;
            }
        } catch (\PDOException $e) {
            error_log("❌ Failed to create role: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Update role name
     */
    public function update($roleId, $roleName)
    {
        $stmt = $this->db->prepare("
            UPDATE roles 
            SET RoleName = :role_name 
            WHERE RoleID = :role_id
        ");

        $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a role
     */
    public function delete($roleId)
    {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE RoleID = :role_id");
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get users count by role
     */
    public function getUserCountByRole($roleId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM useraccounts 
            WHERE RoleID = :role_id
        ");

        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get all roles with user counts
     */
    public function getRolesWithUserCounts()
    {
        $stmt = $this->db->query("
            SELECT 
                r.RoleID,
                r.RoleName,
                COUNT(u.UserID) as user_count
            FROM roles r
            LEFT JOIN useraccounts u ON r.RoleID = u.RoleID
            GROUP BY r.RoleID, r.RoleName
            ORDER BY r.RoleID ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if role name exists
     */
    public function roleExists($roleName)
    {
        return (bool) $this->getRoleByName($roleName);
    }
}