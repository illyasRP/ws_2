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
<script src="https://cdn.tailwindcss.com"></script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Pikachu a fui !</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f8f8;
            text-align: center;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .container {
            margin-top: 100px;
        }
        
        h1 {
            color:rgb(242, 255, 55);
            text-shadow: 2px 2px 4px #000;
        }
        
        p {
            font-size: 1.5em;
            color: #333;
        }
        
        a {
            color: #ff6600;
            text-decoration: none;
            font-weight: bold;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .pikachu {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 50px auto;
            background-image: url('https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/versions/generation-v/black-white/animated/25.gif');
            background-size: contain;
            background-repeat: no-repeat;
            animation: run 5s linear infinite;
        }
        
        @keyframes run {
            0% {
                left: -100px;
                transform: scaleX(1);
            }
            49% {
                transform: scaleX(1);
            }
            50% {
                left: calc(100% + 100px);
                transform: scaleX(-1);
            }
            99% {
                transform: scaleX(-1);
            }
            100% {
                left: -100px;
                transform: scaleX(1);
            }
        }
        
        .pokeball {
            position: absolute;
            width: 30px;
            height: 30px;
            background-image: url('https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png');
            background-size: contain;
            top: 70%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>
<?php include('./components/header.php') ?>
    <div class="container">
        <h1>404 !</h1>
        <p>Oh non ! Pikachu a fait une fuite électrique !</p>
        <div class="pikachu"></div>
        <p>Essayez de <a href="home.php">retourner à la page d'accueil</a> ou de chercher ailleurs.</p>
        <div class="pokeball"></div>
    </div><br><br><br><br><br><br><br><br>
    <?php include('./components/footer.php') ?>