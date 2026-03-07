# 🚀 Tutoriel : Migration vers l'Architecture de Déploiement Atomique Laravel (v2.0)

## Architecture cible

```
/var/www/nom-du-projet
 ├── shared/          <-- Données persistantes (.env, storage)
 ├── releases/        <-- Historique des versions (Code source)
 ├── backups/         <-- Sauvegardes SQL quotidiennes (Statique)
 ├── logs/            <-- Logs serveur Apache (Statique)
 ├── backup.sh        <-- Script d'automatisation des backups
 └── current -> releases/202603XXXXXXXX
```

## Phase 0 : Préparation des variables

Identifie tes trois variables avant de commencer :

- **[USER]** : L'utilisateur linux (ex: anonbox_user).
- **[RACINE]** : Le chemin racine (ex: /var/www/anonbox).
- **[ANCIEN_DOSSIER_CODE]** : Là où se trouve ton code actuel (ex: /var/www/anonbox/app).

## Phase 1 : Structure et Dossiers Systèmes

```bash
# 1. Se placer à la racine et générer le Timestamp
cd [RACINE]
REL=$(date +%Y%m%d%H%M%S)

# 2. Créer l'arborescence complète
sudo -u [USER] mkdir -p [RACINE]/releases/$REL
sudo -u [USER] mkdir -p [RACINE]/shared/storage
sudo -u [USER] mkdir -p [RACINE]/backups
sudo -u [USER] mkdir -p [RACINE]/logs
```

## Phase 2 : Isolation des données (Shared)

```bash
# 1. Déplacer le .env et le contenu du storage
sudo -u [USER] mv [ANCIEN_DOSSIER_CODE]/.env [RACINE]/shared/.env
sudo -u [USER] mv [ANCIEN_DOSSIER_CODE]/storage/* [RACINE]/shared/storage/ 2>/dev/null

# 2. Supprimer l'ancien dossier storage physique
sudo rm -rf [ANCIEN_DOSSIER_CODE]/storage
```

## Phase 3 : Migration du Code et Chaînage (Symlinks)

```bash
# 1. Déplacer tout le code vers la nouvelle release
sudo -u [USER] mv [ANCIEN_DOSSIER_CODE]/* [RACINE]/releases/$REL/
sudo -u [USER] mv [ANCIEN_DOSSIER_CODE]/.* [RACINE]/releases/$REL/ 2>/dev/null

# 2. Créer les liens vers le shared
sudo -u [USER] ln -s [RACINE]/shared/.env [RACINE]/releases/$REL/.env
sudo -u [USER] ln -s [RACINE]/shared/storage [RACINE]/releases/$REL/storage

# 3. Activer la version avec le lien 'current'
sudo -u [USER] ln -sfn [RACINE]/releases/$REL [RACINE]/current
```

## Phase 4 : Sécurité et Permissions

```bash
# 1. Verrouiller la racine et donner le storage à Apache
sudo chown -R [USER]:www-data [RACINE]
sudo chmod 750 [RACINE]
sudo chmod -R 775 [RACINE]/shared/storage

# 2. Sécuriser le .env et les backups
sudo chmod 640 [RACINE]/shared/.env
sudo chmod 700 [RACINE]/backups
```

## Phase 5 : Automatisation des Backups (BDD)

C'est ici qu'on ajoute la protection de tes données SQL.

### 1. Créer le script backup.sh

```bash
sudo nano [RACINE]/backup.sh
```

```bash
#!/bin/bash
# Extraction auto des accès depuis le .env
DB_USER=$(grep DB_USERNAME [RACINE]/shared/.env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD [RACINE]/shared/.env | cut -d '=' -f2)
DB_NAME=$(grep DB_DATABASE [RACINE]/shared/.env | cut -d '=' -f2)

# Dump sans warning et sans erreur de privilèges
export MYSQL_PWD=$DB_PASS
mysqldump -u $DB_USER --no-tablespaces $DB_NAME > [RACINE]/backups/db_auto_$(date +%Y%m%d_%H%M).sql

# Nettoyage : garder les 7 derniers jours seulement
find [RACINE]/backups -type f -name "db_auto_*" -mtime +7 -delete
unset MYSQL_PWD
```

### 2. Rendre exécutable et programmer (Cron)

```bash
sudo chmod +x [RACINE]/backup.sh
# Ajouter au cron : sudo crontab -e
# Ligne : 0 0 * * * [RACINE]/backup.sh > /dev/null 2>&1
```

## Phase 6 : Cache Laravel et Apache

```bash
# 1. Nettoyage physique du cache
cd [RACINE]/current
sudo rm -f bootstrap/cache/*.php

# 2. Recréer le cache proprement
sudo -u [USER] php artisan config:clear
sudo -u [USER] php artisan cache:clear
```

3. **Update Apache** : Modifie ton VirtualHost pour que DocumentRoot pointe sur `[RACINE]/current/public`.

## Phase 7 : Tests de validation

- **Test Visuel** : `ls -la [RACINE]/current` (doit montrer les flèches ->).
- **Test Backup** : `sudo [RACINE]/backup.sh` puis `ls -lh [RACINE]/backups` (taille > 0).
- **Test Restauration** : Essaye d'importer un backup dans une base vide.

## Phase 8️⃣ : Créer le script deploy.sh

Édite le script :

```bash
nano /var/www/anonbox/deploy.sh
```

Voici un exemple de contenu pour un projet Laravel :

```bash
#!/bin/bash

# Variables
APP_DIR="/var/www/anonbox"
REPO_URL="git@github.com:Mahamane-Korobara/anonbox_backend.git"
BRANCH="main"
RELEASE_DIR="$APP_DIR/releases/$(date +%Y%m%d%H%M%S)"

# Créer le dossier de release
mkdir -p $RELEASE_DIR

# Cloner le repo
git clone -b $BRANCH $REPO_URL $RELEASE_DIR

# Copier les fichiers persistants
cp $APP_DIR/shared/.env $RELEASE_DIR/.env
cp -r $APP_DIR/shared/storage $RELEASE_DIR/storage

# Installer les dépendances
cd $RELEASE_DIR
composer install --no-dev --optimize-autoloader

# Migration Laravel
php artisan migrate --force

# Mettre à jour le lien symbolique
ln -sfn $RELEASE_DIR $APP_DIR/current

# Redémarrer le serveur si nécessaire (php-fpm, queue, etc.)
# systemctl restart php8.2-fpm

echo "Déploiement terminé !"
```

⚠️ **Important** :

- Ton `.env` et `storage` doivent rester dans `shared/`
- `ln -sfn` change le lien symbolique `current` pour pointer sur la nouvelle release
- Tu peux adapter le restart des services selon ton serveur

## Phase 9️⃣ : Préparer SSH pour GitHub Actions

### 9.1 Générer une clé SSH sans passphrase

```bash
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/id_ed25519_github
```

Quand il demande la passphrase → laisser vide (ENTRÉE)

- **Clé privée** : `~/.ssh/id_ed25519_github` → à mettre sur GitHub
- **Clé publique** : `~/.ssh/id_ed25519_github.pub` → à mettre sur VPS

### 9.2 Ajouter la clé publique sur le VPS

```bash
mkdir -p ~/.ssh
nano ~/.ssh/authorized_keys
```

Colle la clé publique (ED25519) :

```
ssh-ed25519 AAAAC3Nz… ton_email
```

Puis :

```bash
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### 9.3 Ajouter la clé privée sur GitHub

Dépôt → Settings → Secrets → Actions → New repository secret

- **Name** : `SSH_PRIVATE_KEY`
- **Secret** : contenu de `~/.ssh/id_ed25519_github`

- **Name** : `HOST`
- **Secret** : IP de ton VPS (ex. 72.61.194.76)

### 9.4 Tester la connexion depuis ton PC

```bash
ssh -i ~/.ssh/id_ed25519_github root@72.61.194.76
```

Si tu arrives sans mot de passe → ✅ tout est prêt pour GitHub Actions.

## Phase 🔟 : Créer le workflow GitHub Actions

Dans ton projet, crée :

`.github/workflows/deploy.yml`

Contenu :

```yaml
name: Deploy AnonBox

on:
    push:
        branches: [main] # Déclenchement sur push main

jobs:
    deploy:
        runs-on: ubuntu-latest
        steps:
            - name: Deploy via SSH
              uses: appleboy/ssh-action@master
              with:
                  host: ${{ secrets.HOST }}
                  username: root
                  key: ${{ secrets.SSH_PRIVATE_KEY }}
                  script: |
                      /var/www/anonbox/deploy.sh
```

## Phase 1️⃣1️⃣ : Tester le déploiement

Commit et push ton code :

```bash
git add .
git commit -m "Initial deploy"
git push origin main
```

Sur GitHub → Actions

- Clique sur ton workflow → Voir les logs
- Si tout est correct, tu verras :

```
root
srv1373882
Déploiement terminé !
```

## Phase 1️⃣2️⃣ : Vérifier sur le VPS

```bash
ls -l /var/www/anonbox/current
```

Tu dois voir ta dernière release.

## Phase 1️⃣3️⃣ : Astuces DevOps

- **Zero-downtime** : toujours utiliser `ln -sfn current` pour changer de version sans interrompre le site
- **Rollback** : garder plusieurs dossiers `releases/` pour revenir en arrière si besoin
- **Sécurité** : créer un utilisateur `deploy` plutôt que `root`
- **Services** : relancer `php-fpm`, queue Laravel, nginx si nécessaire

## 🧹 Nettoyage final

Si tout fonctionne (200 OK sur ton domaine) :

```bash
sudo rm -rf [ANCIEN_DOSSIER_CODE]
```
