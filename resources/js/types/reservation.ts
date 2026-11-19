export type ReservationSession = {
    starts_at: string;
    ends_at: string;
    room_name: string;
    film_title: string;
};

export type ReservationSummary = {
    id: number;
    status: 'pending' | 'confirmed' | 'cancelled';
    number_of_places: number;
    total_amount: number;
    session: ReservationSession;
};
