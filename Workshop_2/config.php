<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'ws_2');

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($con->connect_error) {
    die("Database connection failed: " . $con->connect_error);
}

function redirect($url) {
    header("Location: $url");
    exit();
}
function deleteUserAccount($user_id, $password) {
    global $con;
    
    try {
        $stmt = $con->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Tentative de suppression sur compte inexistant: $user_id");
            return false;
        }
        
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];
        
        if (!password_verify($password, $hashed_password)) {
            error_log("Mot de passe incorrect pour suppression compte: $user_id");
            return false;
        }
        
        $con->begin_transaction();
        
        $dependent_tables = [
            'messages' => 'user_id',
            'ticket_responses' => 'user_id',
            'user_sessions' => 'user_id'
        ];
        
        foreach ($dependent_tables as $table => $column) {
            try {
                $stmt = $con->prepare("DELETE FROM $table WHERE $column = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            } catch (Exception $e) {
                error_log("Erreur nettoyage table $table: " . $e->getMessage());
            }
        }
        
        $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $con->commit();
        return true;
        
    } catch (Exception $e) {
        $con->rollback();
        error_log("Erreur critique suppression compte: " . $e->getMessage());
        return false;
    }
}
?>