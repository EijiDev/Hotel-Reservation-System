
<?php

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM UserAccounts WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create ($email, $name, $password) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $this->db->prepare("INSERT INTO UserAccounts(Name, Email, UserPassword) 
                                 VALUES (?, ?, ?)");
    return $stmt->execute([
        $name, 
        $email, 
        $hashedPassword 
    ]);
}
}