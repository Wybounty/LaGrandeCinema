export const reservationStatusLabels: Record<string, string> = {
    pending: 'En attente',
    confirmed: 'Confirmée',
    cancelled: 'Annulée',
};

export function reservationStatusLabel(status: string): string {
    return reservationStatusLabels[status] ?? status;
}