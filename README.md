# AnonBox — API

API REST d'une **boîte à messages anonymes** (type « pose-moi une question anonymement »). Un utilisateur crée un identifiant public (*handle*), partage son lien, et reçoit des messages anonymes qu'il peut lire, auxquels il peut répondre, et transformer en **cartes images** partageables sur les réseaux.

Ce dépôt contient le **backend Laravel**. Le frontend (Next.js 16 / React 19) est dans un dépôt séparé.

---

## Ce que ça fait

**Côté public (sans authentification)**
- Création d'un utilisateur avec un *handle* public + jeton privé secret.
- Consultation d'un profil public par *handle* et de ses *prompts* (questions à la une).
- Envoi d'un **message anonyme** à un utilisateur.

**Côté propriétaire (protégé par jeton privé)**
- **Boîte de réception** : lecture des messages, marquage lu, suppression.
- **Réponses** aux messages et marquage « partagé ».
- Gestion des **prompts** (questions mises en avant sur son profil).
- **Génération de cartes images** partageables (via Intervention Image) et nettoyage des cartes.
- Régénération du jeton privé.

## Sécurité & modération

- **Jeton privé** vérifié par un middleware dédié (`verify.private.token`) pour tout l'espace propriétaire.
- **Modération** : filtrage par mots interdits (`BlacklistedWord`).
- **Anti-abus** : limitation de débit maison (`RateLimit`) sur l'envoi de messages.

---

## Stack

Laravel 12 · PHP 8.2 · Intervention Image (génération de cartes) · Spatie Sluggable · MySQL
5 modèles (`User`, `Message`, `Prompt`, `BlacklistedWord`, `RateLimit`) · 7 migrations · API REST (~90 lignes de routes)

---

## Démarrage

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Endpoint de santé : `GET /api/health`.

---

## 👤 Auteur

Développé par **Mahamane Korobara**, développeur full-stack.

- 🌐 Portfolio & blog technique : **[sahelstack.tech](https://www.sahelstack.tech)**
- 💼 GitHub : [@Mahamane-Korobara](https://github.com/Mahamane-Korobara)
