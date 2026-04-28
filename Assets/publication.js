document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.querySelector('textarea[name="contenu"]');
    if (textarea) {
        const counter = document.createElement('div');
        counter.className = 'text-xs text-right mt-1 text-gray-400';
        textarea.parentElement.appendChild(counter);

        function updateCounter() {
            const max = 1000;
            const current = textarea.value.length;
            counter.textContent = `${current}/${max} caractères`;
            counter.style.color = (current > max - 100) ? '#ef4444' : '#64748b';
        }

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    }


    const cards = document.querySelectorAll('.card-hover');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        setTimeout(() => {
            card.style.transition = `all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) ${i * 60}ms`;
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 200);
    });
});