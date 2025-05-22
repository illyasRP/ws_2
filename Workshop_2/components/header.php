
<?php 
require_once __DIR__ . '/../config.php'; 
startSession();
$stmt = $con->prepare("SELECT id, username, firstname, lastname, email, image, role_id, notifications, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<header>
<nav class="bg-black p-4 text-white shadow-md">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="text-2xl font-bold"><a href="before.php">Votre ticket</a></div>
        <div class="space-x-4 flex items-center">
   
          <a
            href="home.php"  
            class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300"
          >
            Accueil
          </a>
   
          <a
            href="about.php"
            class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300"
          >
            À propos
          </a>
          
            <?php if (isset($_SESSION['user_id'])) : ?>
            <?php if ($_SESSION['role_id'] == 1) : ?>
              <a
                href="./admin/adminer.php"
                class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300"
              >
                Admin space
              </a>
            <?php endif; ?>

       
            <?php if ($_SESSION['role_id'] == 2) : ?>
              <a
                href="admin/support.php"
                class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300"
              >
                support space
              </a>
            <?php endif; ?>
            

            <a href="profil.php" class="flex items-center">
   
              <img 
                src="./uploads/<?= htmlspecialchars($user['image'] ?? 'default.jpg') ?>" 
                onerror="this.src='./uploads/default.jpg'"
                class="w-10 h-10 rounded-full border-2 border-white object-cover hover:border-blue-400 transition"
                alt="Photo de profil"
              >
            </a>

            <a 
              href="deconnexion.php" 
              class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300"
            >
              Déconnexion
            </a>
          <?php else : ?>

            <a 
              href="index.php" 
              class="inline-block px-4 py-2 text-white rounded-lg hover:bg-white hover:text-black transition duration-300"
            >
              Connexion
            </a>
          <?php endif; ?>
          
        </div>
      </div>
    </nav>
</header>
