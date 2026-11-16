# LaGrandeCinema

Studi

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