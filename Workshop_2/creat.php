<?php
require_once './config.php';
startSession();


if (empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = null;
$message_content = '';
$ticket_subject = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message_content = trim($_POST['message']);
    $ticket_subject = trim($_POST['subject']);
    
    if (empty($message_content)) {
        $error = "Veuillez écrire un message avant de créer le ticket";
    } elseif (empty($ticket_subject)) {
        $error = "Veuillez donner un titre à votre ticket";
    } else {
        try {
            $con->begin_transaction();
            
            $stmt = $con->prepare("INSERT INTO tickets (creator_id, subject, status) VALUES (?, ?, 'open')");
            $stmt->bind_param("is", $_SESSION['user_id'], $ticket_subject);
            $stmt->execute();
            $ticket_id = $con->insert_id;
            
            $stmt = $con->prepare("INSERT INTO messages (content, sender_id, ticket_id, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sii", $message_content, $_SESSION['user_id'], $ticket_id);
            $stmt->execute();
            
            $con->commit();
            
            header("Location: chat.php?ticket_id=".$ticket_id);
            exit();
        } catch (Exception $e) {
            $con->rollback();
            $error = "Erreur lors de la création du ticket : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('./components/header.php') ?>
    <div class="container mx-auto py-8 px-4">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 px-6 py-4 text-white">
                <h2 class="text-xl font-bold">Nouvelle demande</h2>
                <p class="text-sm opacity-90">Décrivez votre problème</p>
            </div>

            <form method="POST" class="p-6">
                <div class="mb-4">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Titre du ticket</label>
                    <input 
                        type="text" 
                        name="subject" 
                        id="subject"
                        required
                        class="w-full px-4 py-2 border rounded-lg"
                        placeholder="Donnez un titre à votre ticket"
                        value="<?= 
                            isset($_POST['subject']) ? 
                            htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8') : 
                            '' 
                        ?>"
                    >
                </div>
                
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea 
                        name="message" 
                        id="message"
                        rows="5" 
                        required
                        class="w-full px-4 py-2 border rounded-lg"
                        placeholder="Décrivez votre problème ici..."
                    ><?= 
                        isset($_POST['message']) ? 
                        htmlspecialchars($_POST['message'], ENT_NOQUOTES, 'UTF-8') : 
                        '' 
                    ?></textarea>
                </div>
                
                <button 
                    type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700"
                >
                    Envoyer la demande
                </button>
            </form>
        </div>
    </div><br><br><br><br><br>
    <?php include('./components/footer.php') ?>