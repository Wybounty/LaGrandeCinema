<?php

namespace Database\Seeders;

use App\Models\Film;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Affiches : liens vers images libres (Unsplash / Pexels).
     */
    public function run(): void
    {
        $films = [
            [
                'title' => 'L\'Ombre des répliques',
                'synopsis' => 'Une comédienne de théâtre découvre que son double mystérieux tourne dans un film sans son accord. Entre jalousie et fascination, elle remonte une piste qui la mène jusqu\'aux coulisses du Grand Cinéma.',
                'duration' => 118,
                'genre' => 'Thriller',
                'poster' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Soleil d\'hiver',
                'synopsis' => 'En plein mois de décembre, une projectionniste solitaire décide de rouvrir une salle abandonnée pour offrir gratuitement des classiques du cinéma français aux habitués du quartier.',
                'duration' => 102,
                'genre' => 'Drame',
                'poster' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Les Voyages de Mathilde',
                'synopsis' => 'Mathilde, douze ans, collectionne les tickets de cinéma de sa grand-mère. Chaque billet la propulse dans le film correspondant, où elle doit résoudre une énigme avant la fin de la séance.',
                'duration' => 135,
                'genre' => 'Aventure',
                'poster' => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Rires en cascade',
                'synopsis' => 'Le duo excentrique d\'un club de cinéma muet rate chaque avant-première pour des raisons absurdes. Le public adore, et la légende des « gaffes légendaires » dépasse vite les murs du Grand Cinéma.',
                'duration' => 96,
                'genre' => 'Comédie',
                'poster' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Nuit blanche à Lyon',
                'synopsis' => 'Pendant une marathon de films policiers, un détective en retrait croise une enquête réelle dont les indices semblent calqués sur les projections de la nuit.',
                'duration' => 112,
                'genre' => 'Policier',
                'poster' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Petites lumières',
                'synopsis' => 'Dans un monde où les projecteurs ont perdu leur éclat, une jeune technicienne invente une lumière faite de souvenirs des spectateurs. Chaque souvenir ranime une bobine oubliée.',
                'duration' => 89,
                'genre' => 'Animation',
                'poster' => 'https://images.unsplash.com/photo-1596436889106-be35e843f974?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Le Dernier acte',
                'synopsis' => 'Un metteur en scène légendaire revient pour monter un film autobiographique. Les acteurs apprennent que la version finale décidera aussi de l\'avenir de la salle mythique du Grand Cinéma.',
                'duration' => 128,
                'genre' => 'Drame',
                'poster' => 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=400&h=600',
            ],
            [
                'title' => 'Échos du passé',
                'synopsis' => 'Un signal radio capte des dialogues de films jamais tournés. Une équipe de documentaristes filme les enregistrements en direct sur l\'écran du cinéma, révélant des futurs possibles.',
                'duration' => 124,
                'genre' => 'Science-fiction',
                'poster' => 'https://images.pexels.com/photos/7991138/pexels-photo-7991138.jpeg?auto=compress&cs=tinysrgb&w=400&h=600',
            ],
        ];

        for ($cycle = 1; $cycle <= 4; $cycle++) {
            foreach ($films as $filmData) {
                $title = $cycle === 1
                    ? $filmData['title']
                    : $filmData['title'].' (cycle '.$cycle.')';

                Film::query()->updateOrCreate(
                    ['title' => $title],
                    [
                        'synopsis' => $filmData['synopsis'],
                        'duration' => $filmData['duration'],
                        'genre' => $filmData['genre'],
                        'poster' => $filmData['poster'],
                    ],
                );
            }
        }
    }
}
