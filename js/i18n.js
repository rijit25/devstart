const translations = {
    fr: {
        title: "DevStart - Le Hub du Développeur",
        subtitle: "Votre hub d'entraînement intensif pour devenir Expert Fullstack.",
        auth_login: "Connexion",
        auth_signup: "Inscription",
        streak_label: "Série actuelle",
        progression: "Progression Labs",
        cards: {
            html: { badge: "Architecture", title: "HTML Expert", desc: "Maîtrisez la sémantique, l'accessibilité et la structure d'une page moderne." },
            css: { badge: "Design", title: "CSS Master", desc: "Flexbox, Grid, Animations et Design System. Créez des interfaces Wow." },
            js: { badge: "Logique", title: "JavaScript", desc: "Le cœur du web. DOM, événements, API et asynchrone." },
            php: { badge: "Backend", title: "PHP Modern", desc: "Sécurité, Sessions, POO et PDO. Le backend solide et professionnel." },
            arch: { badge: "Expert", title: "Archi & Sécu", desc: "MVC, XSS, CSRF. Concevez des applications robustes et sécurisées." },
            sql: { badge: "Data", title: "MySQL", desc: "Requêtes complexes, jointures et modélisation de données." }
        },
        footer: "Développé pour les futurs experts. © 2026 DevStart."
    },
    en: {
        title: "DevStart - Developer Hub",
        subtitle: "Your intensive training hub to become a Fullstack Expert.",
        auth_login: "Login",
        auth_signup: "Sign Up",
        streak_label: "Current Streak",
        progression: "Labs Progress",
        cards: {
            html: { badge: "Architecture", title: "HTML Expert", desc: "Master semantics, accessibility, and modern page structure." },
            css: { badge: "Design", title: "CSS Master", desc: "Flexbox, Grid, Animations, and Design Systems. Create Wow interfaces." },
            js: { badge: "Logic", title: "JavaScript", desc: "The heart of the web. DOM, events, APIs, and async." },
            php: { badge: "Backend", title: "Modern PHP", desc: "Security, Sessions, OOP, and PDO. Solid professional backend." },
            arch: { badge: "Expert", title: "Arch & Secu", desc: "MVC, XSS, CSRF. Design robust and secure applications." },
            sql: { badge: "Data", title: "MySQL", desc: "Complex queries, joins, and data modeling." }
        },
        footer: "Built for future experts. © 2026 DevStart."
    }
};

function setLanguage(lang) {
    localStorage.setItem('devstart_lang', lang);
    const t = translations[lang];

    // Text Updates
    document.querySelector('h1').textContent = "DevStart."; 
    document.querySelector('.subtitle').textContent = t.subtitle;
    document.querySelector('.streak-label').textContent = t.streak_label;
    
    // Auth Buttons
    document.getElementById('btn-login').textContent = t.auth_login;
    document.getElementById('btn-signup').textContent = t.auth_signup;

    // Update Cards
    updateCard('html', t.cards.html);
    updateCard('css', t.cards.css);
    updateCard('js', t.cards.js);
    updateCard('php', t.cards.php);
    updateCard('arch', t.cards.arch);
    updateCard('sql', t.cards.sql);
    
    // Update progress labels
    document.querySelectorAll('.progress-label span:first-child').forEach(el => {
        el.textContent = t.progression;
    });

    // Toggle Active Class on Flags
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.lang === lang);
    });
}

function updateCard(id, data) {
    const card = document.querySelector(`.card-${id}`);
    if(card) {
        card.querySelector('.badge').textContent = data.badge;
        card.querySelector('.card-title').textContent = data.title;
        card.querySelector('.card-desc').textContent = data.desc;
    }
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('devstart_lang') || 'fr';
    setLanguage(savedLang);
});
