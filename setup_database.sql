-- STRUCTURE BASE DE DONNÉES DEVSTART (XAMPP)
-- Copier-coller tout ce code dans http://localhost/phpmyadmin > SQL

CREATE DATABASE IF NOT EXISTS devstart_db;
USE devstart_db;

-- 1. Table Utilisateurs (Sécurisée)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- SHA256 / BCRYPT
    streak_count INT DEFAULT 0,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_pro BOOLEAN DEFAULT FALSE
);

-- 2. Table Progression (Multilangages)
CREATE TABLE IF NOT EXISTS progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module VARCHAR(20) NOT NULL, -- html, css, js, php, sql, arch
    labs_completed INT DEFAULT 0,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id, module)
);

-- Index pour optimiser les performances (SEO / Rapide)
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_progress_user ON progress(user_id);
