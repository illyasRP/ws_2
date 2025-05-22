<?php
require_once __DIR__ . '/config.php';
startSession();

// verification session
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// image 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = "user_" . $_SESSION['user_id'] . "." . $imageFileType;
    $target_path = $target_dir . $new_filename;

    //image 2
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "Le fichier n'est pas une image valide.";
    } elseif ($_FILES["profile_image"]["size"] > 500000) {
        $_SESSION['error'] = "L'image est trop volumineuse (max 500KB).";
    } elseif (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        $_SESSION['error'] = "Seuls JPG, JPEG, PNG & GIF sont autorisés.";
    } elseif (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_path)) {
        $stmt = $con->prepare("UPDATE users SET image = ? WHERE id = ?");
        $stmt->bind_param("si", $new_filename, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Photo de profil mise à jour avec succès !";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour en base de données.";
        }
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de l'upload.";
    }
    header("Location: profil.php");
    exit();
}


// edit user db
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($con, $_POST['uname']);
    $firstname = mysqli_real_escape_string($con, $_POST['fame']);
    $lastname = mysqli_real_escape_string($con, $_POST['iname']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    
    $stmt = $con->prepare("UPDATE users SET username = ?, firstname = ?, lastname = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $username, $firstname, $lastname, $email, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profil mis à jour avec succès !";
        $_SESSION['username'] = $username;
        $_SESSION['firstname'] = $firstname;
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour : " . $con->error;
    }
    header("Location: profil.php");
    exit();
}

// edit le mdp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Les nouveaux mots de passe ne correspondent pas";
    } elseif (strlen($new_password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
    } else {
        $stmt = $con->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($current_password, $user['password'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $_SESSION['user_id']);
            $stmt->execute();
            
            $_SESSION['success'] = "Mot de passe mis à jour avec succès !";
        } else {
            $_SESSION['error'] = "Mot de passe actuel incorrect";
        }
    }
    header("Location: profil.php");
    exit();
}



// Récup sql
$stmt = $con->prepare("SELECT id, username, firstname, lastname, email, image, role_id, notifications, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Utilisateur non trouvé !");
    
}

//afficher toute les actions d'user
$stmt = $con->prepare("SELECT COUNT(*) FROM tickets WHERE creator_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tickets_count = $stmt->get_result()->fetch_row()[0];

$stmt = $con->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$messages_count = $stmt->get_result()->fetch_row()[0];

$stmt = $con->prepare("SELECT COUNT(*) FROM tickets WHERE creator_id = ? AND status = 'closed'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resolved_count = $stmt->get_result()->fetch_row()[0];

// Derniers tickets de l'user
$stmt = $con->prepare("SELECT id, subject, status, created_at FROM tickets WHERE creator_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Historique de connexion de l'user
$stmt = $con->prepare("SELECT login_time, ip_address, user_agent FROM login_history WHERE user_id = ? ORDER BY login_time DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$login_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Date d'inscription de l'user
$member_since = date('d/m/Y', strtotime($user['created_at']));



?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .profile-container { transition: all 0.3s ease; }
        #uploadForm { transition: opacity 0.2s ease; }
        #profileImage { transition: transform 0.3s ease; }
        #profileImage:hover { transform: scale(1.05); }
        .tab-button { transition: all 0.3s ease; }
        .tab-button.active { border-bottom: 2px solid #3b82f6; color: #3b82f6; }
        .progress-bar { transition: width 0.5s ease-in-out; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include('./components/header.php') ?>
    
    <div class="container mx-auto py-8 px-4">
        <!-- Messages flash -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">

            <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600"></div>
            
            <div class="flex justify-center -mt-20 relative">
                <div class="relative group">
                    <img id="profileImage" 
                         src="./uploads/<?= htmlspecialchars($user['image'] ?? 'default.jpg') ?>" 
                         onerror="this.src='./uploads/default.jpg'"
                         class="w-40 h-40 rounded-full border-4 border-white object-cover shadow-lg hover:shadow-xl transition">
                    
                 
                    <form id="uploadForm" action="profil.php" method="post" enctype="multipart/form-data" 
                          class="absolute -bottom-2 -right-2">
                        <label class="bg-blue-600 text-white p-3 rounded-full shadow-lg cursor-pointer hover:bg-blue-700 transition flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            <input type="file" name="profile_image" accept="image/*" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                </div>
            </div>

            <div class="flex border-b">
                <button onclick="showTab('profile')" class="tab-button active px-6 py-3 font-medium">Profil</button>
                <button onclick="showTab('password')" class="tab-button px-6 py-3 font-medium">Mot de passe</button>
                <button onclick="showTab('security')" class="tab-button px-6 py-3 font-medium">Sécurité</button>
            </div>

            <div class="p-6">

                <div id="profile-tab" class="tab-content">
     
                    <div id="profile-display" class="profile-container">
                        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
                            <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                        </h2>
                        <p class="text-gray-600 text-center mb-6">@<?= htmlspecialchars($user['username']) ?></p>
                        
            
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="font-semibold text-gray-700 mb-2">Votre Activité</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <div class="text-center p-2 bg-white rounded">
                                    <p class="text-2xl font-bold"><?= $tickets_count ?></p>
                                    <p class="text-sm">Tickets</p>
                                </div>
                                <div class="text-center p-2 bg-white rounded">
                                    <p class="text-2xl font-bold"><?= $messages_count ?></p>
                                    <p class="text-sm">Messages</p>
                                </div>
                                <div class="text-center p-2 bg-white rounded">
                                    <p class="text-2xl font-bold"><?= $resolved_count ?></p>
                                    <p class="text-sm">Résolus</p>
                                </div>
                                <div class="text-center p-2 bg-white rounded">
                                    <p class="text-2xl font-bold"><?= $member_since ?></p>
                                    <p class="text-sm">Membre depuis</p>
                                </div>
                            </div>
                        </div>
                        
       <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-semibold text-gray-700">Email</h3>
        <p class="text-gray-600"><?= htmlspecialchars($user['email']) ?></p>
    </div>
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-semibold text-gray-700">Rôle</h3>
        <div class="flex items-center mt-1">
            <?php
            $roleConfig = [
                1 => ['text' => 'Admin', 'color' => 'purple', 'icon' => '👑'],
                2 => ['text' => 'Support', 'color' => 'blue', 'icon' => '🛡️'],
                3 => ['text' => 'Utilisateur', 'color' => 'gray', 'icon' => '👤']
            ];
            $role = $roleConfig[$user['role_id'] ?? ['text' => 'Inconnu', 'color' => 'yellow', 'icon' => '❓']];
            ?>
            <span class="mr-2 text-lg"><?= $role['icon'] ?></span>
            <span class="px-3 py-1 text-sm rounded-full bg-<?= $role['color'] ?>-100 text-<?= $role['color'] ?>-800">
                <?= $role['text'] ?>
            </span>
        </div>
    </div>
</div>
                      
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-3">Vos derniers tickets</h3>
                            <div class="space-y-2">
                                <?php foreach($recent_tickets as $ticket): ?>
                                    <a href="chat.php?ticket_id=<?= $ticket['id'] ?>" 
                                       class="block p-3 border rounded hover:bg-gray-50 transition">
                                        <div class="flex justify-between">
                                            <span>#<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['subject']) ?></span>
                                            <span class="text-sm <?= $ticket['status'] === 'open' ? 'text-green-600' : 'text-gray-500' ?>">
                                                <?= ucfirst($ticket['status']) ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></div>
                                    </a>
                                <?php endforeach; ?>
                                <a href="gestion.php" class="block text-center text-blue-600 hover:underline mt-2">
                                    Voir tous les tickets →
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-6">
                            <button onclick="toggleEdit()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                Modifier le profil
                            </button>
                        </div>
                    </div>

              
                    <div id="profile-edit" class="profile-container hidden">
                        <form action="profil.php" method="post">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Nom d'utilisateur</label>
                                    <input type="text" name="uname" value="<?= htmlspecialchars($user['username']) ?>" 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Prénom</label>
                                        <input type="text" name="fame" value="<?= htmlspecialchars($user['firstname']) ?>" 
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Nom</label>
                                        <input type="text" name="iname" value="<?= htmlspecialchars($user['lastname']) ?>" 
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                                </div>
                                
                                <div class="flex justify-between pt-4">
                                    <button type="button" onclick="toggleEdit()" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" name="update_profile" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="password-tab" class="tab-content hidden">
                    <form action="profil.php" method="post" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Mot de passe actuel</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Nouveau mot de passe</label>
                            <input type="password" name="new_password" required minlength="8"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
                    
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="confirm_password" required minlength="8"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="change_password" 
                                   class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>


<div id="security-tab" class="tab-content hidden">
    <div class="space-y-6">

        
     
        <div>
            <h3 class="text-lg font-semibold mb-3">Activité récente</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500">IP</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500">Appareil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($login_history as $login): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($login['login_time'])) ?></td>
                            <td class="px-4 py-2"><?= $login['ip_address'] ?></td>
                            <td class="px-4 py-2 text-sm"><?= $login['user_agent'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
            
         
    </div>
</div>
        </div>
    </div>

    <script>
       
        function showTab(tabName) {
           
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.toggle('active', button.textContent.toLowerCase().includes(tabName));
            });
            
        
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.toggle('hidden', content.id !== tabName + '-tab');
            });
        }

  
        function toggleEdit() {
            document.getElementById('profile-display').classList.toggle('hidden');
            document.getElementById('profile-edit').classList.toggle('hidden');
        }
    </script>
</div>
</div>
    <?php include('./components/footer.php') ?>
