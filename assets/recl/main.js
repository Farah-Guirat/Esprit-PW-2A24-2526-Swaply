// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    
    // Active link in navigation
    function setActiveLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const links = document.querySelectorAll('nav a');
        
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage) {
                link.classList.add('active');
            }
        });
    }

    setActiveLink();

    // Simple animation for cards
    const cards = document.querySelectorAll('.card-hover');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.transitionDelay = (index * 50) + 'ms';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });

    console.log('✅ Swaply Front Office loaded successfully');
});