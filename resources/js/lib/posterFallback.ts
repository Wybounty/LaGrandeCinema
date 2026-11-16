export const posterPlaceholderUrl = '/images/film-poster-placeholder.svg';

export function posterSrc(poster: string | null): string {
    if (!poster) {
        return posterPlaceholderUrl;
    }

    if (
        poster.startsWith('http://')
        || poster.startsWith('https://')
        || poster.startsWith('/')
    ) {
        return poster;
    }

    return `/storage/${poster.replace(/^\/+/, '')}`;
}

export function handlePosterError(event: Event): void {
    const image = event.target;

    if (image instanceof HTMLImageElement && !image.src.endsWith(posterPlaceholderUrl)) {
        image.src = posterPlaceholderUrl;
    }
}