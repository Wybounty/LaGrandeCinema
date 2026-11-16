const moneyFormatter = new Intl.NumberFormat('fr-CA', {
    style: 'currency',
    currency: 'CAD',
});

export function formatMoney(amount: number): string {
    return moneyFormatter.format(amount);
}