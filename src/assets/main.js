function setActiveLink() {

    const params = new URLSearchParams(window.location.search);
    const currentAction = params.get('action') || 'home';

    const links = document.querySelectorAll('nav a');

    links.forEach(link => {

        const href = link.getAttribute('href');

        if (href && href.includes('action=' + currentAction)) {
            link.classList.add('active');
        }
    });
}