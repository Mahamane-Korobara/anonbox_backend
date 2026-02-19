# Documentation API AnonBox

**Date:** 19 février 2026  
**Version API:** 1.0.0  
**Base URL:** `http://localhost:8000/api`

## Authentification

Les routes privées exigent le header:

```http
X-Private-Token: <uuid>
```

Si le header est absent:

```json
{
  "success": false,
  "message": "Token manquant. Ajoutez le header X-Private-Token."
}
```

Si le token est invalide:

```json
{
  "success": false,
  "message": "Token invalide ou expiré"
}
```

## Format des réponses

Les réponses basées sur `JsonResource` sont enveloppées dans `data`.

Exemple:

```json
{
  "data": { "id": 1 },
  "success": true
}
```

## Endpoints Users

### POST `/users`
Créer un compte.

Body:

```json
{
  "display_name": "John Doe"
}
```

Validation:
- `display_name`: `required|string|min:2|max:100`

Réponse `201`:

```json
{
  "data": {
    "id": 1,
    "display_name": "John Doe",
    "handle": "john-doe",
    "public_url": "http://localhost:8000/u/john-doe",
    "stats": {
      "messages_received": 0,
      "responses_posted": 0
    },
    "links": {
      "inbox": "http://localhost:8000/inbox/<private_token>"
    }
  },
  "success": true,
  "private_token": "uuid",
  "warning": "Sauvegarde ton lien privé ! C'est ta seule clé d'accès."
}
```

### GET `/users/{handle}`
Profil public.

Réponse `200`:

```json
{
  "data": {
    "id": 1,
    "display_name": "John Doe",
    "handle": "john-doe",
    "public_url": "http://localhost:8000/u/john-doe",
    "stats": {
      "messages_received": 10,
      "responses_posted": 4
    },
    "links": {
      "inbox": "http://localhost:8000/inbox/<private_token>"
    }
  },
  "success": true
}
```

### POST `/users/verify-token`
Login par token.

Body:

```json
{
  "private_token": "uuid"
}
```

Validation:
- `private_token`: `required|uuid`

Réponse `200`:

```json
{
  "data": {
    "id": 1,
    "display_name": "John Doe",
    "handle": "john-doe",
    "public_url": "http://localhost:8000/u/john-doe",
    "stats": {
      "messages_received": 10,
      "responses_posted": 4
    },
    "links": {
      "inbox": "http://localhost:8000/inbox/<private_token>"
    }
  },
  "success": true,
  "unread_count": 3
}
```

Erreur `401`:

```json
{
  "success": false,
  "message": "Token invalide"
}
```

### GET `/me` (privé)
Infos de l'utilisateur authentifié.

Réponse `200`:

```json
{
  "data": {
    "id": 1,
    "display_name": "John Doe",
    "handle": "john-doe",
    "public_url": "http://localhost:8000/u/john-doe",
    "stats": {
      "messages_received": 10,
      "responses_posted": 4
    },
    "links": {
      "inbox": "http://localhost:8000/inbox/<private_token>"
    }
  },
  "success": true,
  "unread_count": 3
}
```

### POST `/users/regenerate-token` (privé)
Régénère le token.

Important: le contrôleur valide aussi `private_token` dans le body.

Body:

```json
{
  "private_token": "uuid"
}
```

Réponse `200`:

```json
{
  "success": true,
  "message": "Nouveau token généré",
  "data": {
    "private_token": "new-uuid",
    "inbox_url": "http://localhost:8000/inbox/new-uuid"
  }
}
```

## Endpoints Prompts

### GET `/users/{handle}/prompts`
Liste des prompts actifs d'un user.

Réponse `200`:

```json
{
  "data": [
    {
      "id": 42,
      "question_text": "Quel est votre avis ?",
      "share_url": "http://localhost:8000/u/john-doe?q=42",
      "stats": {
        "messages_received": 2,
        "times_shared": 1
      },
      "user": null,
      "created_at": "2026-02-19T10:00:00+00:00"
    }
  ],
  "success": true
}
```

### GET `/prompts/{id}`
Détail d'un prompt.

Réponse `200`:

```json
{
  "data": {
    "id": 42,
    "question_text": "Quel est votre avis ?",
    "share_url": "http://localhost:8000/u/john-doe?q=42",
    "stats": {
      "messages_received": 2,
      "times_shared": 1
    },
    "user": {
      "id": 1,
      "display_name": "John Doe",
      "handle": "john-doe",
      "public_url": "http://localhost:8000/u/john-doe",
      "stats": {
        "messages_received": 10,
        "responses_posted": 4
      },
      "links": {
        "inbox": "http://localhost:8000/inbox/<private_token>"
      }
    },
    "created_at": "2026-02-19T10:00:00+00:00"
  },
  "success": true
}
```

Erreur `404` (prompt inactif/supprimé):

```json
{
  "success": false,
  "message": "Question introuvable ou supprimée",
  "redirect_to": "http://localhost:8000/u/john-doe"
}
```

### POST `/prompts` (privé)
Créer un prompt.

Body:

```json
{
  "question_text": "Quel est votre avis ?"
}
```

Validation:
- `question_text`: `required|string|min:5|max:500`

Réponse `201`:

```json
{
  "data": {
    "id": 42,
    "question_text": "Quel est votre avis ?",
    "share_url": "http://localhost:8000/u/john-doe?q=42",
    "stats": {
      "messages_received": 0,
      "times_shared": 0
    },
    "user": null,
    "created_at": "2026-02-19T10:00:00+00:00"
  },
  "success": true,
  "message": "Question créée avec succès"
}
```

### DELETE `/prompts/{id}` (privé)
Supprimer (soft delete) un prompt appartenant à l'utilisateur.

Réponse `200`:

```json
{
  "success": true,
  "message": "Question supprimée (les liens redirigent vers ton profil)"
}
```

Erreur `404`:

```json
{
  "success": false,
  "message": "Question introuvable"
}
```

### POST `/prompts/{id}/share`
Incrémenter le compteur de partage.

Réponse `200`:

```json
{
  "success": true,
  "message": "Partage comptabilisé"
}
```

## Endpoints Messages

### POST `/messages`
Envoyer un message anonyme.

Body:

```json
{
  "user_id": 1,
  "prompt_id": 42,
  "anonymous_content": "Super projet"
}
```

Validation:
- `user_id`: `required|exists:users,id`
- `prompt_id`: `nullable|exists:prompts,id`
- `anonymous_content`: `required|string|min:1|max:1000`

Réponse `201`:

```json
{
  "data": {
    "id": 156,
    "anonymous_content": "Super projet",
    "response_content": null,
    "status": "unread",
    "prompt": null,
    "meta": {
      "has_response": false,
      "is_shared": false,
      "created_at": "2026-02-19T10:00:00+00:00",
      "read_at": null,
      "responded_at": null
    }
  },
  "success": true,
  "message": "Message envoyé"
}
```

Rate limit (`429`) retourne les clés de `RateLimit::checkLimit`, ex:

```json
{
  "blocked": true,
  "reason": "Trop de tentatives. Veuillez réessayer plus tard.",
  "retry_after": 120
}
```

Blacklist (`403`):

```json
{
  "success": false,
  "message": "Contenu inapproprié détecté"
}
```

### GET `/inbox` (privé)
Inbox de l'utilisateur authentifié.

Query optionnelle:
- `status`: `unread|read|responded`

Réponse `200`:

```json
{
  "data": [
    {
      "id": 156,
      "anonymous_content": "Super projet",
      "response_content": null,
      "status": "unread",
      "prompt": {
        "id": 42,
        "question_text": "Quel est votre avis ?",
        "share_url": "http://localhost:8000/u/john-doe?q=42",
        "stats": {
          "messages_received": 2,
          "times_shared": 1
        },
        "user": null,
        "created_at": "2026-02-19T10:00:00+00:00"
      },
      "meta": {
        "has_response": false,
        "is_shared": false,
        "created_at": "2026-02-19T10:00:00+00:00",
        "read_at": null,
        "responded_at": null
      }
    }
  ],
  "success": true,
  "stats": {
    "total": 1,
    "unread": 1
  }
}
```

### PATCH `/messages/{id}/read` (privé)
Marquer comme lu.

Réponse `200`:

```json
{
  "success": true,
  "message": "Message marqué comme lu"
}
```

### POST `/messages/{id}/respond` (privé)
Répondre à un message.

Body:

```json
{
  "response_content": "Merci pour ton message"
}
```

Validation:
- `response_content`: `required|string|min:1|max:1000`

Réponse `200`:

```json
{
  "data": {
    "id": 156,
    "anonymous_content": "Super projet",
    "response_content": "Merci pour ton message",
    "status": "responded",
    "prompt": null,
    "meta": {
      "has_response": true,
      "is_shared": false,
      "created_at": "2026-02-19T10:00:00+00:00",
      "read_at": "2026-02-19T10:05:00+00:00",
      "responded_at": "2026-02-19T10:06:00+00:00"
    }
  },
  "success": true,
  "message": "Réponse enregistrée"
}
```

### DELETE `/messages/{id}` (privé)
Supprimer un message.

Réponse `200`:

```json
{
  "success": true,
  "message": "Message supprimé"
}
```

### POST `/messages/{id}/share` (privé)
Marquer un message comme partagé.

Réponse `200`:

```json
{
  "success": true,
  "shared_at": "2026-02-19T10:07:00.000000Z"
}
```

## Endpoints Cards

### POST `/cards/generate` (privé)
Générer une image PNG depuis un message répondu.

Body:

```json
{
  "message_id": 156
}
```

Validation:
- `message_id`: `required|integer`

Réponse `200`:

```json
{
  "success": true,
  "message": "Carte générée avec succès",
  "data": {
    "card_url": "http://localhost:8000/images/cards/card_xxx.png",
    "download_url": "http://localhost:8000/api/cards/download/card_xxx.png",
    "share_text": "Envoie-moi un message anonyme 👀 : http://localhost:8000/u/john-doe"
  }
}
```

Erreurs métier:
- `404`: message introuvable
- `422`: message sans réponse

### GET `/cards/download/{filename}`
Télécharger une carte.

Retourne un fichier binaire (pas JSON). `404` si le fichier n'existe pas.

### DELETE `/cards/cleanup` (privé)
Supprimer les cartes PNG âgées de plus de 7 jours.

Réponse `200`:

```json
{
  "success": true,
  "message": "3 cartes supprimées"
}
```

## Health Check

### GET `/health`

Réponse `200`:

```json
{
  "status": "ok",
  "timestamp": "2026-02-19T10:00:00+00:00",
  "version": "1.0.0"
}
```

## Réponses d'erreur Laravel (validation)

Quand une `FormRequest` échoue, Laravel retourne typiquement `422`:

```json
{
  "message": "The question text field is required.",
  "errors": {
    "question_text": [
      "The question text field is required."
    ]
  }
}
```

Note: certaines routes ont des messages personnalisés (ex: `StorePromptRequest`, `StoreUserRequest`), mais le format reste `message` + `errors`.

## Liste complète des routes API

### Publiques
- `POST /users`
- `GET /users/{handle}`
- `POST /users/verify-token`
- `GET /prompts/{id}`
- `GET /users/{handle}/prompts`
- `POST /prompts/{id}/share`
- `POST /messages`
- `GET /cards/download/{filename}`
- `GET /health`

### Privées (header `X-Private-Token` requis)
- `GET /me`
- `GET /inbox`
- `PATCH /messages/{id}/read`
- `POST /messages/{id}/respond`
- `DELETE /messages/{id}`
- `POST /messages/{id}/share`
- `POST /prompts`
- `DELETE /prompts/{id}`
- `POST /users/regenerate-token`
- `POST /cards/generate`
- `DELETE /cards/cleanup`

## Tableau récapitulatif des endpoints

| Méthode | Endpoint | Accès | Ce que fait l'endpoint |
|---|---|---|---|
| POST | `/users` | Public | Crée un nouvel utilisateur (génère `handle` + `private_token`) et retourne son profil. |
| GET | `/users/{handle}` | Public | Retourne le profil public d'un utilisateur via son `handle`. |
| POST | `/users/verify-token` | Public | Vérifie un `private_token` et retourne le profil + le nombre de messages non lus. |
| GET | `/me` | Privé | Retourne le profil de l'utilisateur authentifié par `X-Private-Token`. |
| POST | `/users/regenerate-token` | Privé | Régénère le token privé et retourne le nouveau token + la nouvelle inbox URL. |
| GET | `/users/{handle}/prompts` | Public | Liste les prompts actifs d'un utilisateur, triés du plus récent au plus ancien. |
| GET | `/prompts/{id}` | Public | Retourne un prompt précis si actif; sinon retourne une erreur 404 avec `redirect_to`. |
| POST | `/prompts` | Privé | Crée un nouveau prompt pour l'utilisateur authentifié. |
| DELETE | `/prompts/{id}` | Privé | Supprime (soft delete) un prompt appartenant à l'utilisateur authentifié. |
| POST | `/prompts/{id}/share` | Public | Incrémente le compteur de partage d'un prompt. |
| POST | `/messages` | Public | Envoie un message anonyme à un utilisateur (avec rate limit + blacklist). |
| GET | `/inbox` | Privé | Retourne les messages reçus par l'utilisateur (filtrables par `status`). |
| PATCH | `/messages/{id}/read` | Privé | Marque un message comme lu (`status=read`, `read_at` renseigné). |
| POST | `/messages/{id}/respond` | Privé | Enregistre une réponse à un message (`status=responded`, `responded_at` renseigné). |
| DELETE | `/messages/{id}` | Privé | Supprime un message de l'inbox de l'utilisateur authentifié. |
| POST | `/messages/{id}/share` | Privé | Marque un message comme partagé (`is_shared=true`, `shared_at` renseigné). |
| POST | `/cards/generate` | Privé | Génère une carte PNG à partir d'un message répondu et retourne les URLs de carte/téléchargement. |
| GET | `/cards/download/{filename}` | Public | Télécharge une carte générée (réponse binaire, pas JSON). |
| DELETE | `/cards/cleanup` | Privé | Supprime les cartes PNG anciennes (plus de 7 jours). |
| GET | `/health` | Public | Endpoint de santé API (`status`, `timestamp`, `version`). |
