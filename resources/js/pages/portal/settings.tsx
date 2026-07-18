import { Head, useForm, usePage } from '@inertiajs/react';
import { KeyRound, UserRound } from 'lucide-react';
import { toast } from 'sonner';
import PasswordInput from '@/components/password-input';

type Props = { passwordRules?: string };

type PageProps = {
    auth: { user: { first_name: string; last_name: string; email: string } };
};

export default function PortalSettings({ passwordRules }: Props) {
    const user = usePage<PageProps>().props.auth.user;

    const profile = useForm({
        first_name: user.first_name ?? '',
        last_name: user.last_name ?? '',
        email: user.email ?? '',
    });

    const password = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitProfile = (e: React.FormEvent) => {
        e.preventDefault();
        profile.patch('/settings/profile', {
            preserveScroll: true,
            onSuccess: () => toast.success('Profile updated'),
        });
    };

    const submitPassword = (e: React.FormEvent) => {
        e.preventDefault();
        password.put('/settings/password', {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Password updated');
                password.reset();
            },
            onError: () => password.reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Settings" />

            <div className="mx-auto max-w-2xl space-y-6 py-6">
                <div>
                    <h1 className="text-xl font-bold text-portal-ink">
                        Settings
                    </h1>
                    <p className="text-sm text-neutral-500">
                        Update your details and password.
                    </p>
                </div>

                {/* Profile */}
                <Card icon={UserRound} title="Your details">
                    <form onSubmit={submitProfile} className="space-y-4">
                        <div className="grid grid-cols-2 gap-3">
                            <Field
                                label="First name"
                                error={profile.errors.first_name}
                            >
                                <input
                                    className="form-control"
                                    value={profile.data.first_name}
                                    onChange={(e) =>
                                        profile.setData(
                                            'first_name',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="given-name"
                                />
                            </Field>
                            <Field
                                label="Last name"
                                error={profile.errors.last_name}
                            >
                                <input
                                    className="form-control"
                                    value={profile.data.last_name}
                                    onChange={(e) =>
                                        profile.setData(
                                            'last_name',
                                            e.target.value,
                                        )
                                    }
                                    autoComplete="family-name"
                                />
                            </Field>
                        </div>

                        <Field label="Email" error={profile.errors.email}>
                            <input
                                type="email"
                                className="form-control"
                                value={profile.data.email}
                                onChange={(e) =>
                                    profile.setData('email', e.target.value)
                                }
                                autoComplete="email"
                            />
                        </Field>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={profile.processing}
                                className="inline-flex items-center gap-1.5 rounded-[4px] bg-portal-accent px-4 py-2 text-sm font-bold text-white transition hover:brightness-95 disabled:opacity-70"
                            >
                                Save changes
                            </button>
                        </div>
                    </form>
                </Card>

                {/* Password */}
                <Card icon={KeyRound} title="Password">
                    <form onSubmit={submitPassword} className="space-y-4">
                        <Field
                            label="Current password"
                            error={password.errors.current_password}
                        >
                            <PasswordInput
                                name="current_password"
                                autoComplete="current-password"
                                placeholder="Current password"
                                value={password.data.current_password}
                                onChange={(e) =>
                                    password.setData(
                                        'current_password',
                                        e.target.value,
                                    )
                                }
                            />
                        </Field>

                        <Field
                            label="New password"
                            error={password.errors.password}
                        >
                            <PasswordInput
                                name="password"
                                autoComplete="new-password"
                                placeholder="New password"
                                passwordrules={passwordRules}
                                value={password.data.password}
                                onChange={(e) =>
                                    password.setData('password', e.target.value)
                                }
                            />
                        </Field>

                        <Field
                            label="Confirm new password"
                            error={password.errors.password_confirmation}
                        >
                            <PasswordInput
                                name="password_confirmation"
                                autoComplete="new-password"
                                placeholder="Confirm new password"
                                passwordrules={passwordRules}
                                value={password.data.password_confirmation}
                                onChange={(e) =>
                                    password.setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                            />
                        </Field>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={password.processing}
                                className="inline-flex items-center gap-1.5 rounded-[4px] bg-portal-accent px-4 py-2 text-sm font-bold text-white transition hover:brightness-95 disabled:opacity-70"
                            >
                                Update password
                            </button>
                        </div>
                    </form>
                </Card>
            </div>
        </>
    );
}

function Card({
    icon: Icon,
    title,
    children,
}: {
    icon: React.ComponentType<{ className?: string }>;
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-[4px] border border-portal-line bg-white p-5">
            <div className="mb-4 flex items-center gap-2">
                <span className="grid size-8 place-items-center rounded-[4px] bg-portal-soft text-portal-accent">
                    <Icon className="size-4" />
                </span>
                <h2 className="font-bold text-portal-ink">{title}</h2>
            </div>
            {children}
        </section>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-xs font-medium text-neutral-600">
                {label}
            </span>
            <div className="mt-1.5">{children}</div>
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </label>
    );
}
