<?php
require_once __DIR__ . '/config.php';
startSession();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Support Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .team-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: top center;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen flex flex-col">
    <?php include('./components/header.php') ?> 
    
    <main class="flex-grow container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-primary-600 p-8 md:p-12 text-center">
                <h1 class="text-4xl font-bold text-white mb-4">À propos de notre service</h1>
                <p class="text-primary-100 text-lg max-w-2xl mx-auto">Notre plateforme de tickets dédiée à votre satisfaction</p>
            </div>
            
            <div class="p-8 md:p-12 space-y-8">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="md:w-1/3 flex justify-center">
                        <div class="bg-primary-100 p-6 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-primary-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="md:w-2/3">
                        <h2 class="text-2xl font-semibold text-primary-800 mb-4">Notre Mission</h2>
                        <p class="text-gray-600">Nous nous engageons à fournir un système de support efficace et transparent. Notre plateforme de tickets a été conçue pour simplifier le processus de demande d'assistance, garantissant une réponse rapide et un suivi clair de chaque requête.</p>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-2xl font-semibold text-primary-800 mb-6 text-center">Notre Équipe</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-primary-100">
                            <div class="h-48 bg-gray-100 overflow-hidden">
                                <img src="./uploads/team/illyas.jpg" alt="Photo de Illyas Seyidov" class="team-photo">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800">Illyas Seyidov</h3>
                                <p class="text-primary-600 text-sm">Admin</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-primary-100">
                            <div class="h-48 bg-gray-100 overflow-hidden">
                            <img src="./uploads/team/yanis.jpg" alt="Photo de Illyas Seyidov" class="team-photo">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800">Yanis</h3>
                                <p class="text-primary-600 text-sm">Support N°1</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-primary-100">
                            <div class="h-48 bg-gray-100 overflow-hidden">
                            <img src="./uploads/team/tom.jpg" alt="Photo de Illyas Seyidov" class="team-photo">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800">Tom</h3>
                                <p class="text-primary-600 text-sm">Support N°2</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-primary-100">
                            <div class="h-48 bg-gray-100 overflow-hidden">
                            <img src="./uploads/team/suley.jpg" alt="Photo de Illyas Seyidov" class="team-photo">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800">Suleyman</h3>
                                <p class="text-primary-600 text-sm">Support N°3</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-primary-700 px-6 py-8 text-center">
                <h3 class="text-xl font-semibold text-white mb-4">Prêt à commencer ?</h3>
                <a href="creat.php" class="inline-block bg-white text-primary-700 font-medium px-6 py-3 rounded-lg hover:bg-primary-50 transition duration-300">
                    Créer un ticket maintenant
                </a>
            </div>
        </div>
    </main>
    <?php include('./components/footer.php') ?>