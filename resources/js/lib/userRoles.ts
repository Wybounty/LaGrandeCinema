export const roleLabels: Record<string, string> = {
    spectateur: 'Spectateur',
    administrateur: 'Administrateur',
    super_administrateur: 'Super administrateur',
};

export const selectableRoles = (
    canAssignSuperRole: boolean,
): { value: string; label: string }[] => {
    const roles = [
        { value: 'spectateur', label: roleLabels.spectateur },
        { value: 'administrateur', label: roleLabels.administrateur },
    ];

    if (canAssignSuperRole) {
        roles.push({
            value: 'super_administrateur',
            label: roleLabels.super_administrateur,
        });
    }

    return roles;
};