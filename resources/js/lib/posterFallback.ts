export const posterPlaceholderUrl = '/images/film-poster-placeholder.svg';

export function posterSrc(poster: string | null): string {
    return poster ?? posterPlaceholderUrl;
}

export function handlePosterError(event: Event): void {
    const image = event.target;

    if (image instanceof HTMLImageElement && !image.src.endsWith(posterPlaceholderUrl)) {
        image.src = posterPlaceholderUrl;
    }
}