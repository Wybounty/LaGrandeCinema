# LaGrandeCinema

Studi

## Réservation en ligne

Spectateurs connectés : films → séance → nombre de places → récapitulatif → **Payer avec Stripe (simulation)** → confirmation en base (`status: confirmed`).

## Paiement simulé (complément UX)

Le bouton « Payer avec Stripe » illustre le futur parcours de paiement. **Aucune intégration Stripe réelle**, aucune donnée bancaire, aucun appel à l’API Stripe. La validation met à jour la réservation côté serveur uniquement.

## Configuration

- `CINEMA_TICKET_PRICE` dans `.env` — tarif unitaire pour le calcul du montant (défaut : `12.50`).

## Tests de verrouillage (MySQL / InnoDB)

Les tests de concurrence avec `lockForUpdate()` nécessitent **MySQL** (InnoDB), pas SQLite en mémoire :

```powershell
$env:DB_CONNECTION="mysql"
$env:DB_DATABASE="votre_base_de_test"
php artisan test --compact tests/Mysql/ReservationConcurrencyMysqlTest.php
```

Utilisez une base dédiée aux tests si possible (`DatabaseTransactions` sur ce fichier).