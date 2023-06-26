<?php

require_once "BaseDao.php";

class FinalDao extends BaseDao
{
    private $conn;
    
    public function __construct()
    {
        parent::__construct();
    }

    public function login($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->execute(['email' => $email, 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ["success" => false, "message" => "Invalid credentials"];
        }

        $payload = [
            'id' => $user['id'],
            'iat' => time(),
            'exp' => time() + (60 * 60) // 1 hour expiry
        ];
        $jwt = JWT::encode($payload, 'your_jwt_secret_key');

        return ["success" => true, "message" => "Logged in successfully", "token" => $jwt];
    }

    public function investor($first_name, $last_name, $email, $company, $share_class_id, $share_class_category_id, $diluted_shares)
    {
        try {
            // Start the transaction
            $this->conn->beginTransaction();

            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT * FROM investors WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $investor = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($investor) {
                return ["success" => false, "message" => "Investor with this email already exists"];
            }

            // Insert new investor
            $stmt = $this->conn->prepare("INSERT INTO investors (first_name, last_name, email, company) VALUES (:first_name, :last_name, :email, :company)");
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'company' => $company
            ]);
            $investor_id = $this->conn->lastInsertId();

            // Check sum of diluted shares for the share class
            $stmt = $this->conn->prepare("SELECT SUM(diluted_shares) as total FROM cap_table WHERE share_class_id = :share_class_id");
            $stmt->execute(['share_class_id' => $share_class_id]);
            $total = $stmt->fetchColumn();

            $stmt = $this->conn->prepare("SELECT authorized_assets FROM share_classes WHERE id = :id");
            $stmt->execute(['id' => $share_class_id]);
            $authorized_assets = $stmt->fetchColumn();

            if ($total + $diluted_shares > $authorized_assets) {
                return ["success" => false, "message" => "Sum of diluted shares exceeds authorized assets for this share class"];
            }

            // Insert new record to cap_table
            $stmt = $this->conn->prepare("INSERT INTO cap_table (share_class_id, share_class_category_id, investor_id, diluted_shares) VALUES (:share_class_id, :share_class_category_id, :investor_id, :diluted_shares)");
            $stmt->execute([
                'share_class_id' => $share_class_id,
                'share_class_category_id' => $share_class_category_id,
                'investor_id' => $investor_id,
                'diluted_shares' => $diluted_shares
            ]);

            // Commit the transaction
            $this->conn->commit();

            return ["success" => true, "message" => "Investor created successfully"];

        } catch (\PDOException $e) {
            // Rollback the transaction
            $this->conn->rollBack();
            return ["success" => false, "message" => "Error occurred: " . $e->getMessage()];
        }
    }


    public function share_classes()
    {
        $stmt = $this->conn->prepare("SELECT * FROM share_classes");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function share_class_categories()
    {
        $stmt = $this->conn->prepare("SELECT * FROM share_class_categories");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>