<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Ticket non spécifié";
    header("Location: gestion.php");
    exit();
}

$ticket_id = (int)$_GET['id'];

try {
    $stmt = $con->prepare("UPDATE tickets SET status = 'closed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$ticket_id, $_SESSION['user_id']]);
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Ticket fermé avec succès";
    } else {
        $_SESSION['error'] = "Ticket introuvable ou déjà fermé";
    }
} catch (Exception $e) {
    error_log("Erreur fermeture ticket: " . $e->getMessage());
    $_SESSION['error'] = "Erreur technique lors de la fermeture";
}

header("Location: gestion.php");
exit();