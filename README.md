# Mini Simulio — Test Technique

> Livrable, démarrage en ~5 minutes.

## 👀 Aperçu
- **Backend (PHP / Symfony)** → `http://localhost:${PHP_PORT:-8000}`
- **Backend (Python / FastAPI)** → service interne appelé par le PHP
- **Frontend (Vue + Vuetify)** → `http://localhost:${FRONTEND_PORT:-5173}`
- **MySQL** → `localhost:3307` (évite les conflits locaux)
- **phpMyAdmin** → `http://localhost:${PMA_PORT:-8081}`

## ✅ Prérequis
- Docker Desktop + Docker Compose v2
- Ports libres : 8000 (API), 5173 (front), 3307 (MySQL), 8081 (phpMyAdmin)

---

## ✨ Fonctionnalités
- Authentification requise pour **faire une simulation**
- Intégration du **simulateur Python** via route API PHP
- **Clients** (CRUD) + attribution d’une simulation à un client (Bonus)
- **Historique** : liste des simulations, suppression, export **PDF** (Bonus)
- **Bouton télécharger un PDF**
- **CORS** configuré (front → API) 

---

## 🚀 Démarrage rapide

```bash
# 1) Variables d'environnement
# Linux/Mac (bash)
cp .envexemple .env
cp frontend/.envexemple frontend/.env

# Windows (PowerShell)
# copy .envexemple .env
# copy frontend\.envexemple frontend\.env

# 2) Lancer l'environnement
docker compose up -d --build

# 3) Dépendances PHP
docker compose exec backend-php composer install

# 4) (Optionnel) Migrations/fixtures si utilisées
# docker compose exec backend-php php bin/console doctrine:migrations:migrate -n
# docker compose exec backend-php php bin/console doctrine:fixtures:load -n
