/**
 * AUTH.JS - GESTION DE L'ESTAT UTILISATEUR ET SYNCHRONISATION
 */
const Auth = {
    user: null,

    async init() {
        this.checkSession();
        if (this.user) {
            this.syncLocalToDB();
            this.updateUI();
        }
    },

    async login(email, password) {
        try {
            const res = await fetch('api_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (data.status === 'success') {
                this.user = { username: data.username, streak: data.streak };
                await this.syncLocalToDB();
                this.updateUI();
                return { success: true };
            }
            return { success: false, message: data.message };
        } catch (e) {
            return { success: false, message: "Erreur serveur." };
        }
    },

    async register(username, email, password) {
        try {
            const res = await fetch('api_register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });
            const data = await res.json();
            if (data.status === 'success') {
                this.user = { username: data.username, streak: 0 };
                await this.syncLocalToDB();
                this.updateUI();
                return { success: true };
            }
            return { success: false, message: data.message };
        } catch (e) {
            return { success: false, message: "Erreur serveur." };
        }
    },

    async logout() {
        // Simple reload to clear session or call a php logout
        location.href = 'index.html'; // Basic for now
    },

    checkSession() {
        // En PHP, la session est côté serveur. 
        // On pourrait faire un appel API check_session.php
        // Pour l'instant, on se base sur les éléments du DOM injectés ou une variable globale
    },

    async syncLocalToDB() {
        if (!this.user) return;
        const modules = ['html', 'css', 'js', 'php', 'sql', 'archSecu'];
        for (const mod of modules) {
            const localKey = mod + 'LabsCompleted';
            const labs = localStorage.getItem(localKey);
            if (labs) {
                await fetch('api_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ module: mod.replace('Secu', ''), labs_completed: labs })
                });
            }
        }
    },

    async getStats() {
        if (!this.user) return null;
        const res = await fetch('api_progress.php');
        return await res.json();
    },

    updateUI() {
        const btnLogin = document.getElementById('btn-login');
        const btnSignup = document.getElementById('btn-signup');
        if (this.user && btnLogin) {
            btnLogin.textContent = this.user.username;
            btnLogin.className = "auth-btn profile-btn";
            btnLogin.href = "#profile";
            if (btnSignup) {
                btnSignup.textContent = "Déconnexion";
                btnSignup.className = "auth-btn";
                btnSignup.onclick = () => this.logout();
            }
        }
    }
};

document.addEventListener('DOMContentLoaded', () => Auth.init());
