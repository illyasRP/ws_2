<?php
session_start();
require_once 'db_conn.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button_inscription'])) {
 
    $lastname = htmlspecialchars(trim($_POST['lastname'] ?? ''));
    $firstname = htmlspecialchars(trim($_POST['firstname'] ?? ''));
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    if (empty($lastname) || empty($firstname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères";
    } else {
   
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Cet email ou nom d'utilisateur existe déjà";
        } else {
          
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role_id = 3; // Rôle par défaut: utilisateur standard
            $avatar = 'default-avatar.png'; // Avatar par défaut
            
            $stmt = $con->prepare("INSERT INTO users (email, password, username, firstname, lastname, role_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssis", $email, $hashed_password, $username, $firstname, $lastname, $role_id, $avatar);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Votre compte a été créé avec succès !";
                header("Location: index.php");
                exit();
            } else {
                $error = "Erreur lors de la création du compte: " . $con->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Système de Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen flex flex-col">
    <main class="flex-grow flex items-center justify-center p-4">
        <form method="POST" class="bg-white rounded-xl shadow-lg overflow-hidden w-full max-w-md">
            <div class="bg-primary-600 p-6 text-center">
                <h2 class="text-2xl font-bold text-white">Inscription</h2>
            </div>
            
            <div class="p-6 space-y-4">
    
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="lastname" name="lastname" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                           value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>">
                </div>

            
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                           value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>">
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

       
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse Mail</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

      
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                  
                </div>

      
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmation Mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                </div>

           
                <div class="pt-2">
                    <button type="submit" name="button_inscription"
                           class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 transition duration-300 cursor-pointer font-semibold">
                        S'inscrire
                    </button>
                </div>

        
                <p class="text-center text-sm text-gray-600 pt-2">
                    Vous avez déjà un compte ?
                    <a href="index.php" class="text-primary-600 hover:underline font-medium">connectez vous ici</a>
                </p>
            </div>
        </form>
    </main>
</body>
</html>