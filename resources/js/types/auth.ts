export type User = {
    id: number;
    first_name: string;
    last_name: string;
    name: string;
    user_type: string;
    has_admin_access?: boolean;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    /** Permission names the current user has (a super user's is the full set). */
    permissions: string[];
};
