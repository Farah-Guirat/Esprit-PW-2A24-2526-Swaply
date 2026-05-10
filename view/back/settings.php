<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'klai.aziz@admin.tn') {
    header('Location: ../front/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Paramètres Dynamiques</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
<div class="flex h-screen overflow-hidden">
<?php $currentPage = 'settings'; require __DIR__ . '/sidebar.php'; ?>

  <div class="main-content">
    <header class="glass-card" id="main-header">
      <h2 id="page-title" class="text-gradient text-2xl font-bold">Paramètres Dynamiques</h2>
      <div class="header-right">
        <div class="search-box glass-card">
          <i class="fa-solid fa-magnifying-glass text-teal-600"></i>
          <input type="text" placeholder="Rechercher..." class="bg-transparent border-none outline-none">
        </div>
        <div class="notifications">
          <i class="fa-solid fa-bell text-2xl text-yellow-500 pulse-glow"></i>
          <span class="badge bg-gradient-to-r from-red-500 to-pink-500">7</span>
        </div>
        <div class="user glass-card p-2">
          <img src="https://i.pravatar.cc/40?img=12" alt="Admin" class="rounded-full border-2 border-teal-400">
          <div>
            <p class="name font-semibold text-gradient">Admin</p>
            <p class="role text-sm text-teal-600">Super Admin</p>
          </div>
        </div>
        <a href="../../controller/logout.php" class="logout-btn glass-card text-red-600 hover:text-red-700 transition-colors" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
          <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    </header>

    <div class="page-content p-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-12 text-center slide-up">
          <h1 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">
            ⚙️ <span class="text-gradient">Paramètres Avancés</span>
          </h1>
          <p class="text-xl text-white/90 max-w-2xl mx-auto">
            Interface dynamique et moderne pour gérer votre plateforme Swaply
          </p>
          <div class="mt-6 flex justify-center">
            <div class="glass-card px-6 py-3 rounded-full">
              <span class="text-teal-600 font-semibold">✨ Interface Interactive</span>
            </div>
          </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
          <div class="glass-card p-6 text-center scale-in" style="animation-delay: 0.1s">
            <div class="text-3xl mb-2">👥</div>
            <div class="text-2xl font-bold text-gradient">1,247</div>
            <div class="text-sm text-gray-600">Utilisateurs Actifs</div>
          </div>
          <div class="glass-card p-6 text-center scale-in" style="animation-delay: 0.2s">
            <div class="text-3xl mb-2">📝</div>
            <div class="text-2xl font-bold text-gradient">856</div>
            <div class="text-sm text-gray-600">Publications</div>
          </div>
          <div class="glass-card p-6 text-center scale-in" style="animation-delay: 0.3s">
            <div class="text-3xl mb-2">🔔</div>
            <div class="text-2xl font-bold text-gradient">23</div>
            <div class="text-sm text-gray-600">Notifications</div>
          </div>
          <div class="glass-card p-6 text-center scale-in" style="animation-delay: 0.4s">
            <div class="text-3xl mb-2">🛡️</div>
            <div class="text-2xl font-bold text-gradient">99.9%</div>
            <div class="text-sm text-gray-600">Sécurité</div>
          </div>
        </div>

        <!-- Compte Administrateur -->
        <div class="glass-card rounded-3xl overflow-hidden mb-8 card-hover-effect slide-up">
          <div class="p-8 border-b border-white/20" style="background: var(--primary-gradient);">
            <h2 class="text-3xl font-bold text-white flex items-center gap-4">
              <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
                <i class="fas fa-user-shield text-3xl text-white"></i>
              </div>
              Compte Administrateur
            </h2>
            <p class="text-white/90 mt-2 text-lg">Gérez votre compte administrateur et les paramètres de sécurité</p>
          </div>
          <div class="p-8">
            <div class="grid gap-8 md:grid-cols-2">
              <div class="floating-animation">
                <label class="block text-xl font-bold text-gray-800 mb-4 text-gradient">Email Administrateur</label>
                <input type="email" value="klai.aziz@admin.tn" class="w-full px-6 py-4 text-xl border-2 border-teal-300 rounded-2xl focus:outline-none focus:ring-4 focus:ring-teal-500 focus:border-transparent transition-all glass-card" readonly>
              </div>
              <div class="floating-animation" style="animation-delay: 1s">
                <label class="block text-xl font-bold text-gray-800 mb-4 text-gradient">Mot de passe</label>
                <button class="magic-button px-8 py-4 text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transform hover:-translate-y-2 flex items-center gap-4 text-xl w-full justify-center">
                  <i class="fas fa-key text-2xl"></i> Changer le mot de passe
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div class="glass-card rounded-3xl overflow-hidden mb-8 card-hover-effect slide-up" style="animation-delay: 0.3s">
          <div class="p-8 border-b border-white/20" style="background: var(--warning-gradient);">
            <h2 class="text-3xl font-bold text-white flex items-center gap-4">
              <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
                <i class="fas fa-bell text-3xl text-white"></i>
              </div>
              Notifications Interactives
            </h2>
            <p class="text-white/90 mt-2 text-lg">Configurez les notifications avec des toggles dynamiques</p>
          </div>
          <div class="p-8">
            <div class="space-y-6">
              <div class="glass-card p-6 rounded-2xl border-2 border-yellow-300 hover:border-yellow-400 transition-all duration-300 flex items-center justify-between group">
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 text-xl group-hover:text-yellow-600 transition-colors">🆕 Nouveaux utilisateurs</h3>
                  <p class="text-gray-600 mt-1">Recevoir une notification lors de l'inscription</p>
                </div>
                <div class="toggle-slider active cursor-pointer" onclick="this.classList.toggle('active')"></div>
              </div>
              <div class="glass-card p-6 rounded-2xl border-2 border-blue-300 hover:border-blue-400 transition-all duration-300 flex items-center justify-between group">
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 text-xl group-hover:text-blue-600 transition-colors">📝 Nouvelles publications</h3>
                  <p class="text-gray-600 mt-1">Notification pour chaque nouvelle publication</p>
                </div>
                <div class="toggle-slider cursor-pointer" onclick="this.classList.toggle('active')"></div>
              </div>
              <div class="glass-card p-6 rounded-2xl border-2 border-red-300 hover:border-red-400 transition-all duration-300 flex items-center justify-between group">
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 text-xl group-hover:text-red-600 transition-colors">🚨 Réclamations</h3>
                  <p class="text-gray-600 mt-1">Alerte pour chaque nouvelle réclamation</p>
                </div>
                <div class="toggle-slider active cursor-pointer" onclick="this.classList.toggle('active')"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sécurité -->
        <div class="glass-card rounded-3xl overflow-hidden mb-8 card-hover-effect slide-up" style="animation-delay: 0.5s">
          <div class="p-8 border-b border-white/20" style="background: var(--danger-gradient);">
            <h2 class="text-3xl font-bold text-white flex items-center gap-4">
              <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
                <i class="fas fa-shield-alt text-3xl text-white"></i>
              </div>
              Sécurité Avancée
            </h2>
            <p class="text-white/90 mt-2 text-lg">Paramètres de sécurité avec indicateurs visuels</p>
          </div>
          <div class="p-8">
            <div class="grid gap-8 md:grid-cols-2">
              <div class="glass-card p-6 rounded-2xl border-2 border-green-300 hover:border-green-400 transition-all duration-300 floating-animation">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-3 text-xl">
                  <i class="fas fa-mobile-alt text-green-600 text-2xl"></i>
                  Authentification 2FA
                </h3>
                <div class="flex items-center gap-4 mb-4">
                  <span class="px-4 py-2 bg-gradient-to-r from-green-400 to-green-600 text-white rounded-full text-lg font-bold pulse-glow">Activé</span>
                  <span class="text-gray-600 font-medium">WebAuthn Sécurisé</span>
                </div>
                <button class="text-green-600 hover:text-green-800 text-lg font-bold transition-colors flex items-center gap-2">
                  <i class="fas fa-cog"></i> Configurer
                </button>
              </div>
              <div class="glass-card p-6 rounded-2xl border-2 border-purple-300 hover:border-purple-400 transition-all duration-300 floating-animation" style="animation-delay: 2s">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-3 text-xl">
                  <i class="fas fa-clock text-purple-600 text-2xl"></i>
                  Session Admin
                </h3>
                <p class="text-gray-600 mb-4 text-lg">Durée maximale: <span class="font-bold text-purple-600 text-xl">24 heures</span></p>
                <button class="text-purple-600 hover:text-purple-800 text-lg font-bold transition-colors flex items-center gap-2">
                  <i class="fas fa-edit"></i> Modifier
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Maintenance -->
        <div class="glass-card rounded-3xl overflow-hidden mb-8 card-hover-effect slide-up" style="animation-delay: 0.7s">
          <div class="p-8 border-b border-white/20" style="background: var(--info-gradient);">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-4">
              <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
                <i class="fas fa-tools text-3xl text-gray-800"></i>
              </div>
              Outils de Maintenance
            </h2>
            <p class="text-gray-700 mt-2 text-lg">Actions système avec effets visuels</p>
          </div>
          <div class="p-8">
            <div class="grid gap-6 md:grid-cols-3">
              <button class="glass-card p-8 border-2 border-blue-300 rounded-2xl text-left hover:border-blue-400 transition-all duration-500 transform hover:-translate-y-4 hover:scale-105 group">
                <div class="flex items-center gap-4 mb-4">
                  <div class="p-4 bg-blue-100 rounded-2xl group-hover:bg-blue-200 transition-all duration-300">
                    <i class="fas fa-database text-blue-600 text-4xl"></i>
                  </div>
                </div>
                <h3 class="font-bold text-gray-800 mb-3 text-2xl">💾 Sauvegarde DB</h3>
                <p class="text-gray-600 mb-4 text-lg">Créer une sauvegarde complète</p>
                <div class="flex items-center text-blue-600 font-bold text-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
                  <span>Exécuter maintenant</span>
                  <i class="fas fa-rocket ml-3 text-2xl"></i>
                </div>
              </button>
              <button class="glass-card p-8 border-2 border-green-300 rounded-2xl text-left hover:border-green-400 transition-all duration-500 transform hover:-translate-y-4 hover:scale-105 group">
                <div class="flex items-center gap-4 mb-4">
                  <div class="p-4 bg-green-100 rounded-2xl group-hover:bg-green-200 transition-all duration-300">
                    <i class="fas fa-broom text-green-600 text-4xl"></i>
                  </div>
                </div>
                <h3 class="font-bold text-gray-800 mb-3 text-2xl">🧹 Nettoyer Cache</h3>
                <p class="text-gray-600 mb-4 text-lg">Vider le cache système</p>
                <div class="flex items-center text-green-600 font-bold text-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
                  <span>Nettoyer maintenant</span>
                  <i class="fas fa-magic ml-3 text-2xl"></i>
                </div>
              </button>
              <button class="glass-card p-8 border-2 border-purple-300 rounded-2xl text-left hover:border-purple-400 transition-all duration-500 transform hover:-translate-y-4 hover:scale-105 group">
                <div class="flex items-center gap-4 mb-4">
                  <div class="p-4 bg-purple-100 rounded-2xl group-hover:bg-purple-200 transition-all duration-300">
                    <i class="fas fa-file-export text-purple-600 text-4xl"></i>
                  </div>
                </div>
                <h3 class="font-bold text-gray-800 mb-3 text-2xl">📤 Exporter Données</h3>
                <p class="text-gray-600 mb-4 text-lg">Exporter toutes les données</p>
                <div class="flex items-center text-purple-600 font-bold text-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
                  <span>Exporter maintenant</span>
                  <i class="fas fa-download ml-3 text-2xl"></i>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-6 justify-end slide-up" style="animation-delay: 0.9s">
          <button class="magic-button px-12 py-6 text-white rounded-3xl font-bold shadow-2xl hover:shadow-3xl transform hover:-translate-y-3 flex items-center justify-center gap-4 text-2xl">
            <i class="fas fa-save text-3xl"></i>
            Sauvegarder les modifications
          </button>
          <button class="glass-card px-12 py-6 text-gray-800 rounded-3xl font-bold shadow-2xl hover:shadow-3xl transform hover:-translate-y-3 flex items-center justify-center gap-4 text-2xl hover:bg-gray-100 transition-all duration-300">
            <i class="fas fa-undo text-3xl"></i>
            Restaurer les paramètres par défaut
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée progressive
    const cards = document.querySelectorAll('.glass-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });

    // Effets de particules sur les boutons
    const buttons = document.querySelectorAll('.magic-button, .glass-card');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            for (let i = 0; i < 8; i++) {
                const particle = document.createElement('div');
                particle.className = 'confetti';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 3 + 's';
                particle.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                this.appendChild(particle);

                setTimeout(() => {
                    particle.remove();
                }, 3000);
            }
        });
    });

    // Animation des toggles
    const toggles = document.querySelectorAll('.toggle-slider');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            this.classList.toggle('active');

            // Effet de vibration
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'pulse 0.3s ease';
            }, 10);
        });
    });

    // Effets sonores simulés (vibration/visuelle)
    const actionButtons = document.querySelectorAll('.magic-button');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin text-3xl"></i> Traitement en cours...';
            this.disabled = true;

            // Simulation de traitement
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check text-3xl"></i> Sauvegardé avec succès !';
                this.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';

                // Créer un effet de confettis
                for (let i = 0; i < 20; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                    document.body.appendChild(confetti);

                    setTimeout(() => {
                        confetti.remove();
                    }, 3000);
                }

                setTimeout(() => {
                    location.reload();
                }, 2000);
            }, 2000);
        });
    });

    // Animation des cartes au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.card-hover-effect').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });

    // Effet de parallaxe sur le fond
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        document.body.style.backgroundPosition = `0% ${scrolled * 0.5}px`;
    });
});
</script>
</body>
</html>