export type ManagedUserRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type ManagedUserForm = {
    id: number;
    name: string;
    email: string;
    role: string;
};