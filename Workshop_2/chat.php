<?php
require_once './config.php';
startSession();

if (empty($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : null;
$messages = [];
$error = null;

if ($ticket_id) {
    try {
        $stmt = $con->prepare("SELECT m.*, u.username, r.name as role_name 
                             FROM messages m
                             JOIN users u ON m.sender_id = u.id
                             JOIN roles r ON u.role_id = r.id
                             WHERE ticket_id = ? 
                             ORDER BY created_at ASC");
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
            $message = htmlspecialchars(trim($_POST['message']), ENT_NOQUOTES, 'UTF-8');
            $stmt = $con->prepare("INSERT INTO messages (content, sender_id, ticket_id, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sii", $message, $current_user_id, $ticket_id);
            $stmt->execute();
            header("Location: chat.php?ticket_id=".$ticket_id);
            exit();
        }
    } catch (Exception $e) {
        $error = "Erreur de communication";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .messages-container {
            height: 60vh;
            overflow-y: auto;
        }
        .user-message {
            background-color: #3b82f6;
            color: white;
            margin-left: auto;
        }
        .other-message {
            background-color: #e5e7eb;
            margin-right: auto;
        }
    </style>
</head>
<body class="bg-gray-100">
<?php include('./components/header.php') ?>
    <div class="container mx-auto max-w-4xl py-8 px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="home.php" class="text-white hover:text-blue-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold">Ticket #<?= $ticket_id ?? 'Nouveau' ?></h1>
                </div>
            </div>
            
            <div class="messages-container p-6 space-y-4">
                <?php if (empty($messages)): ?>
                    <p class="text-center text-gray-500 py-8">Aucun message</p>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="max-w-xs rounded-lg px-4 py-2 <?= ($msg['sender_id'] == $current_user_id) ? 'user-message' : 'other-message' ?>">
                            <p class="font-semibold">
                                <?= ($msg['sender_id'] == $current_user_id) ? 'Vous' : htmlspecialchars($msg['username']) . ($msg['role_name'] == 'admin' ? ' (admin)' : '') ?>
                            </p>
                            <p><?= nl2br(htmlspecialchars($msg['content'], ENT_NOQUOTES, 'UTF-8')) ?></p>
                            <p class="text-xs opacity-80 mt-1">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t">
                <form method="POST" class="flex gap-2">
                    <input type="text" name="message" placeholder="Votre message..." 
                           class="flex-grow px-4 py-2 border rounded-lg" required>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const container = document.querySelector('.messages-container');
        container.scrollTop = container.scrollHeight;
    </script>
<?php include('./components/footer.php') ?>