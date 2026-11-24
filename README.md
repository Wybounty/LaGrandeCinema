# LaGrandeCinema

Studi

## Documentation API (Swagger / OpenAPI 3)

LaGrandeCinema expose surtout une interface **Inertia**. Les points d’entrée documentés en OpenAPI sont les routes **JSON, webhook et binaire** réellement présentes dans l’application.

| Ressource           | URL                                                                                                                       |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **Swagger UI**      | `/api/documentation` (ex. `http://localhost:8000/api/documentation` avec `php artisan serve` ou l’URL Herd de votre site) |
| **Fichier OpenAPI** | `/docs?api-docs.json`                                                                                                     |

Régénérer la spec après modification des annotations dans `app/OpenApi/` :

```powershell
herd php artisan l5-swagger:generate
```

En développement, vous pouvez activer `L5_SWAGGER_GENERATE_ALWAYS=true` dans `.env` pour régénérer à chaque requête.

**Paquet :** `darkaonline/l5-swagger` **11.4.1** (OpenAPI **3.0**, spec **1.1.0**).

**Contenu documenté :**

- Endpoints **JSON** : contrôle billets, webhook Stripe, QR billet, santé `/up`.
- **Administration – Interface Inertia** : films, séances, salles, réservations, utilisateurs, page de contrôle.
- **Tableau de bord** : statistiques (`GET /dashboard?period=today|week|month|year`).
- **Paramètres utilisateur** : profil, sécurité, apparence.

Les pages Inertia sont décrites avec réponses `text/html`, redirections **302** et erreurs **422** (sans schéma JSON inventé pour les props Vue).

## Réservation en ligne

Spectateurs connectés : films → séance → nombre de places → récapitulatif → **Payer avec Stripe (simulation)** → confirmation en base (`status: confirmed`).

## Paiement simulé (complément UX)

Le bouton « Payer avec Stripe » illustre le futur parcours de paiement. **Aucune intégration Stripe réelle**, aucune donnée bancaire, aucun appel à l’API Stripe. La validation met à jour la réservation côté serveur uniquement.

## Gestion des utilisateurs (dashboard Vue)

- URL : `/dashboard/users` (lien **Utilisateurs** dans la barre latérale pour les admins).
- Rôles : `administrateur` et `super_administrateur` uniquement (même connexion Fortify, pas de second panneau).

### Compte de développement

Si aucun admin n’existe encore en base :

```powershell
php artisan db:seed --class=Database\Seeders\AdminUserSeeder
```

Identifiants créés :

- E-mail : `admin@legrandcinema.test`
- Mot de passe : `CinemaAdmin2026!`
- Rôle : `super_administrateur`

**À changer avant la production.**

## Configuration

- **Entreprise** : `config/services.php` → clé `cinema` (nom, contact, adresse).
- **Tarif billet** : table `cinema_settings` (défaut `12.50` €), modifiable en base pour une future interface d’administration.

## Tests de verrouillage (MySQL / InnoDB)

Les tests de concurrence avec `lockForUpdate()` nécessitent **MySQL** (InnoDB), pas SQLite en mémoire :

```powershell
$env:DB_CONNECTION="mysql"
$env:DB_DATABASE="votre_base_de_test"
php artisan test --compact tests/Mysql/ReservationConcurrencyMysqlTest.php
```

Utilisez une base dédiée aux tests si possible (`DatabaseTransactions` sur ce fichier).
