<?php
require_once __DIR__ . '/config.php';
startSession();


if (!empty($_SESSION['user_id'])) {
    redirectBasedOnRole();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];


    if (empty($email) || empty($password)) {
        $error = "Email et mot de passe requis";
    } else {
     
        $stmt = $con->prepare("SELECT id, username, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

     
            if (password_verify($password, $user['password'])) {
       
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['email'] = $email;

                logLogin($user['id']);

     
                redirectBasedOnRole();
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    }
}


function redirectBasedOnRole() {
    if ($_SESSION['role_id'] == 1) {
        header("Location: admin/adminer.php");
    } elseif ($_SESSION['role_id'] == 2) {
        header("Location: admin/support.php");
    } else {
        header("Location: home.php");
    }
    exit();
}


function logLogin($user_id) {
    global $con;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $con->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $ip, $user_agent);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .login-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center login-container">
    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-lg shadow-xl">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">Connexion</h2>
            <p class="mt-2 text-sm text-gray-600">Accédez à votre espace personnel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="votre@email.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">Se souvenir de moi</label>
                </div>

              
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Se connecter
                </button>
            </div>
        </form>

        <div class="text-center text-sm text-gray-600">
            Pas encore de compte? 
            <a href="signup.php" class="font-medium text-blue-600 hover:text-blue-500">
                Créer un compte
            </a>
        </div>
    </div>
</body>
</html>