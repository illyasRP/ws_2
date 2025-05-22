<?php
require_once __DIR__ . '/config.php';
startSession();

// Vérification de connexion basique
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['close_ticket'])) {
        $ticket_id = intval($_POST['ticket_id']);
        
        // Vérifier que le ticket appartient à l'utilisateur
        $stmt = $con->prepare("UPDATE tickets SET status = 'closed' WHERE id = ? AND creator_id = ?");
        $stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Ticket fermé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la fermeture du ticket";
        }
        header("Location: gestion.php");
        exit();
    }
    
    if (isset($_POST['delete_ticket'])) {
        $ticket_id = intval($_POST['ticket_id']);
        
        // Vérifier que le ticket appartient à l'utilisateur
        $stmt = $con->prepare("SELECT status FROM tickets WHERE id = ? AND creator_id = ?");
        $stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Supprimer d'abord les messages associés
            $delete_msgs = $con->prepare("DELETE FROM messages WHERE ticket_id = ?");
            $delete_msgs->bind_param("i", $ticket_id);
            $delete_msgs->execute();
            
            // Puis supprimer le ticket
            $delete_ticket = $con->prepare("DELETE FROM tickets WHERE id = ?");
            $delete_ticket->bind_param("i", $ticket_id);
            
            if ($delete_ticket->execute()) {
                $_SESSION['success'] = "Ticket supprimé avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression";
            }
            header("Location: gestion.php");
            exit();
        }
    }
}

// Récupération des tickets
$tickets = [];
$stmt = $con->prepare("SELECT id, subject, status, created_at FROM tickets WHERE creator_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('./components/header.php') ?>
    
    <main class="container mx-auto py-8 px-4">
        <!-- Version avec bouton Créer à droite -->
        <div class="bg-blue-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="home.php" class="text-white hover:text-blue-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="text-2xl font-bold">Mes Tickets</h2>
            </div>
            <a href="creat.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                Créer un ticket
            </a>
        </div>
        
        <!-- Messages d'état -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 my-4 rounded">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-4 rounded">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <?php if (!empty($tickets)): ?>
                    <div class="space-y-4">
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold">
                                            <a href="chat.php?ticket_id=<?= $ticket['id'] ?>" class="text-blue-600 hover:underline">
                                                #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['subject']) ?>
                                            </a>
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Créé le <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?= $ticket['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                               ($ticket['status'] === 'closed' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                            <?= ucfirst($ticket['status']) ?>
                                        </span>
                                        <div class="flex space-x-2">
                                            <?php if ($ticket['status'] !== 'closed'): ?>
                                                <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir fermer ce ticket ?');">
                                                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                                    <button type="submit" name="close_ticket" class="text-gray-500 hover:text-gray-700" title="Fermer le ticket">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">
                                                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                                <button type="submit" name="delete_ticket" class="text-red-500 hover:text-red-700" title="Supprimer le ticket">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center py-8 text-gray-500">Aucun ticket trouvé</p>
                    <div class="text-center">
                        <a href="creat.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Créer un ticket
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main><br><br><br><br><br><br><br><br><br><br>
    <?php include('./components/footer.php') ?>
