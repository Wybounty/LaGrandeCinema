# Convention de nommage des branches – Le Grand Cinéma

Afin de faciliter le travail de développement et de garder un repository organisé, une convention de nommage est mise en place.

### Branches principales

- `main` : branche contenant les versions stables et validées de l'application.
- `develop` : branche principale de développement qui regroupe les fonctionnalités avant leur validation.

### Branches secondaires

Chaque nouvelle tâche doit être développée dans une branche spécifique créée à partir de `develop`.

| Type de branche | Utilisation | Exemple |
|---|---|---|
| `feature/` | Développement d'une nouvelle fonctionnalité | `feature/online-booking` |
| `bugfix/` | Correction d'un bug | `bugfix/booking-error` |
| `hotfix/` | Correction urgente en production | `hotfix/payment-error` |
| `docs/` | Modification de la documentation | `docs/api-documentation` |
| `test/` | Ajout ou modification de tests | `test/booking-tests` |

### Règles à respecter

- Utiliser uniquement des lettres minuscules.
- Séparer les mots avec des tirets (`-`).
- Utiliser un nom court qui décrit clairement la tâche.
- Ne pas développer directement sur `main`.
- Créer les branches de fonctionnalités à partir de `develop`.
- Une fois le travail terminé et testé, proposer une merge request (pull request) vers `develop`.

Cette organisation permet de faciliter le suivi des modifications, de limiter les erreurs et de conserver une version stable de l'application.
