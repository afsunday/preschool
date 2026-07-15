import { Form, Head, Link, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Profile"
                    description="Update your name and email address"
                />

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <label
                                    htmlFor="first_name"
                                    className="text-sm font-medium text-neutral-800"
                                >
                                    First name
                                </label>

                                <input
                                    id="first_name"
                                    className="form-control mt-1 block w-full"
                                    defaultValue={auth.user.first_name}
                                    name="first_name"
                                    required
                                    autoComplete="given-name"
                                    placeholder="First name"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.first_name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <label
                                    htmlFor="last_name"
                                    className="text-sm font-medium text-neutral-800"
                                >
                                    Last name
                                </label>

                                <input
                                    id="last_name"
                                    className="form-control mt-1 block w-full"
                                    defaultValue={auth.user.last_name}
                                    name="last_name"
                                    required
                                    autoComplete="family-name"
                                    placeholder="Last name"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.last_name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <label
                                    htmlFor="email"
                                    className="text-sm font-medium text-neutral-800"
                                >
                                    Email address
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    className="form-control mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="Email address"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!"
                                            >
                                                Click here to re-send the
                                                verification email.
                                            </Link>
                                        </p>

                                        {status ===
                                            'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                A new verification link has been
                                                sent to your email address.
                                            </div>
                                        )}
                                    </div>
                                )}

                            <div className="flex items-center gap-4">
                                <button
                                    type="submit"
                                    className="btn-black"
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Save
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profile settings',
            href: edit(),
        },
    ],
};
