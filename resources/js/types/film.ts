export type Film = {
    id: number;
    title: string;
    synopsis: string;
    duration: number;
    genre: string;
    poster: string | null;
};

export type PaginatedFilms = {
    data: Film[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};