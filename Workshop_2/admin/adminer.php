<?php
require_once '../config.php';
startSession();


if (empty($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: index.php');
    exit();
}


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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = intval($_POST['new_role']);
    $current_user_role = $_SESSION['role_id'];

  
    $stmt = $con->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $target_user = $stmt->get_result()->fetch_assoc();

    if ($current_user_role > $target_user['role_id']) {
        $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires";
        header("Location: adminer.php");
        exit();
    }

    $stmt = $con->prepare("UPDATE users SET role_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_role, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Rôle utilisateur mis à jour";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour";
    }
    header("Location: adminer.php");
    exit();
}


$users = $con->query("
    SELECT id, username, email, role_id, created_at 
    FROM users 
    ORDER BY role_id ASC, created_at DESC
")->fetch_all(MYSQLI_ASSOC);


$role_counts = [
    1 => 0,
    2 => 0,
    3 => 0
];
foreach ($users as $user) {
    $role_counts[$user['role_id']]++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            padding-top: 64px; 
        }
    </style>
</head>
<body class="bg-gray-100">

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
                    <a href="adminer.php" class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300">
                        Admin space
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

        <h1 class="text-3xl font-bold mb-6">Admin space</h1>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold text-gray-500">Admin(s)</h3>
                <p class="text-2xl font-bold text-purple-600"><?= $role_counts[1] ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold text-gray-500">Support(s)</h3>
                <p class="text-2xl font-bold text-blue-600"><?= $role_counts[2] ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="font-semibold text-gray-500">Utilisateur(s)</h3>
                <p class="text-2xl font-bold text-gray-600"><?= $role_counts[3] ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Nom d'utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Inscrit le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><?= $user['id'] ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <?php if ($user['id'] != $_SESSION['user_id']): // Ne pas permettre de se modifier soi-même ?>
                                <form method="POST" class="inline-flex items-center gap-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" class="border rounded px-2 py-1 text-sm">
                                        <option value="1" <?= $user['role_id'] == 1 ? 'selected' : '' ?>>Admin</option>
                                        <option value="2" <?= $user['role_id'] == 2 ? 'selected' : '' ?>>support</option>
                                        <option value="3" <?= $user['role_id'] == 3 ? 'selected' : '' ?>>User</option>
                                    </select>
                                    <button type="submit" name="change_role" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                        Appliquer
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('form[method="POST"]').forEach(form => {
            form.addEventListener('submit', (e) => {
                const select = form.querySelector('select[name="new_role"]');
                const newRole = select.options[select.selectedIndex].text;
                if (!confirm(`Confirmez-vous le changement de rôle vers ${newRole} ?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
  <?php include('../components/footer.php') ?>