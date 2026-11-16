<?php

namespace App;

enum FilmGenre: string
{
    case Action = 'Action';
    case Animation = 'Animation';
    case Aventure = 'Aventure';
    case Comedie = 'Comédie';
    case Documentaire = 'Documentaire';
    case Drame = 'Drame';
    case Fantastique = 'Fantastique';
    case Horreur = 'Horreur';
    case Policier = 'Policier';
    case Romance = 'Romance';
    case ScienceFiction = 'Science-fiction';
    case Thriller = 'Thriller';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
