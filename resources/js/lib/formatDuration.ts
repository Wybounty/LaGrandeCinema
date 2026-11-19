export function formatDuration(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours === 0) {
        return `${remainingMinutes} min`;
    }

    if (remainingMinutes === 0) {
        return `${hours} h`;
    }

    return `${hours} h ${remainingMinutes}`;
}

const dateFormatter = new Intl.DateTimeFormat('fr-CA', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const timeFormatter = new Intl.DateTimeFormat('fr-CA', {
    hour: '2-digit',
    minute: '2-digit',
});

export function formatSessionDate(isoDateTime: string): string {
    const formatted = dateFormatter.format(new Date(isoDateTime));
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
}

export function formatSessionTime(isoDateTime: string): string {
    return timeFormatter.format(new Date(isoDateTime));
}
