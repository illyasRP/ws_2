<?php
require_once '../config.php';
startSession();

// Vérification basique admin
if (empty($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../index.php");
    exit();
}

// Récupérer les infos utilisateur si nécessaire pour la photo
$user_img = 'default.jpg'; // Valeur par défaut
if (isset($_SESSION['user_id'])) {
    $stmt = $con->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $user_img = $user_data['image'] ?? 'default.jpg';
    }
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $ticket_id = intval($_POST['ticket_id']);
        $new_status = $_POST['new_status'];
        
        $stmt = $con->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $ticket_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Statut du ticket mis à jour";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }
        header("Location: adminer.php");
        exit();
    }
    
    if (isset($_POST['delete_ticket'])) {
        $ticket_id = intval($_POST['ticket_id']);
        
        // Vérifier que le ticket est bien fermé
        $stmt = $con->prepare("SELECT status FROM tickets WHERE id = ?");
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $ticket = $result->fetch_assoc();
            if ($ticket['status'] === 'closed') {
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
                header("Location: adminer.php");
                exit();
            }
        }
    }
}

// Récupérer la liste des tickets
$tickets = [];
$stmt = $con->prepare("
    SELECT t.id, t.subject, t.status, t.created_at, u.username 
    FROM tickets t
    JOIN users u ON t.creator_id = u.id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $tickets = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            padding-top: 64px; /* Compensation pour le header fixe */
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header fixe en haut -->
    <header class="fixed top-0 left-0 right-0 z-50">
        <nav class="bg-black p-4 text-white shadow-md">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="text-2xl font-bold"><a href="home.php">Votre ticket</a></div>
                <div class="space-x-4 flex items-center">
                    <a href="../before.php" class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300">
                        Accueil
                    </a>
                    <a href="../about.php" class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300">
                        À propos
                    </a>
                    <a href="support.php" class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300">
                        Support space
                    </a>
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <a href="../profil.php" class="flex items-center">
                            <img 
                                src="../uploads/<?= htmlspecialchars($user_img) ?>" 
                                onerror="this.src='../uploads/default.jpg'"
                                class="w-10 h-10 rounded-full border-2 border-white object-cover hover:border-blue-400 transition"
                                alt="Photo de profil"
                            >
                        </a>
                        <a href="../deconnexion.php" class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300">
                            Déconnexion
                        </a>
                    <?php else : ?>
                        <a href="../index.php" class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300">
                            Connexion
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mx-auto p-4">
        <!-- Messages d'état -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Tableau des tickets -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Liste des tickets</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="py-3 px-4 text-left">ID</th>
                            <th class="py-3 px-4 text-left">Sujet</th>
                            <th class="py-3 px-4 text-left">Créé par</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Statut</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($tickets as $ticket): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4"><?= htmlspecialchars($ticket['id']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($ticket['subject']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($ticket['username']) ?></td>
                            <td class="py-3 px-4"><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?= $ticket['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                       ($ticket['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                                    <?= ucfirst($ticket['status']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <form method="POST" class="inline-flex gap-2">
                                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                        <select name="new_status" class="border rounded px-2 py-1 text-sm">
                                            <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                            <option value="pending" <?= $ticket['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                        </select>
                                        <button type="submit" name="update_status" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                            Mettre à jour
                                        </button>
                                    </form>
                                    <a href="../chat.php?ticket_id=<?= $ticket['id'] ?>" class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600 inline-block">
                                        Voir
                                    </a>
                                    <?php if ($ticket['status'] === 'closed'): ?>
                                        <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">
                                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                            <button type="submit" name="delete_ticket" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                                Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  <?php include('../components/footer.php') ?>