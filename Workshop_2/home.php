<?php
require_once __DIR__ . '/config.php';
startSession();


if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Ticket - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="font-sans antialiased">
<?php include('./components/header.php') ?>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-blue-600 mb-2">VOTRE TICKET</h1>
            <p class="text-lg text-gray-600">Gérez vos demandes en toute simplicité</p>
        </div>
        
        <div class="w-full max-w-md space-y-6">
         
            <a href="gestion.php" class="block">
                <button class="w-full py-6 px-4 bg-white border-2 border-blue-500 rounded-xl shadow-md hover:bg-blue-50 transition duration-300 flex flex-col items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-xl font-semibold text-blue-700">TOUS VOS TICKETS</span>
                </button>
            </a>
            
       
            <a href="creat.php" class="block">
                <button class="w-full py-6 px-4 bg-blue-600 text-white rounded-xl shadow-md hover:bg-blue-700 transition duration-300 flex flex-col items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="text-xl font-semibold">CRÉER UN NOUVEAU TICKET</span>
                </button>
            </a>
        </div>
    </div>
    <?php include('./components/footer.php') ?>