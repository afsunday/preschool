import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { store } from '@/routes/register';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    return (
        <>
            <Head title="Register" />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <label
                                    htmlFor="first_name"
                                    className="text-sm font-medium text-neutral-800"
                                >
                                    First name
                                </label>
                                <input
                                    id="first_name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="given-name"
                                    name="first_name"
                                    placeholder="First name"
                                    className="form-control"
                                />
                                <InputError
                                    message={errors.first_name}
                                    className="mt-2"
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
                                    type="text"
                                    required
                                    tabIndex={2}
                                    autoComplete="family-name"
                                    name="last_name"
                                    placeholder="Last name"
                                    className="form-control"
                                />
                                <InputError
                                    message={errors.last_name}
                                    className="mt-2"
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
                                    required
                                    tabIndex={3}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                    className="form-control"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <label
                                    htmlFor="password"
                                    className="text-sm font-medium text-neutral-800"
                                >
                                    Password
                                </label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Password"
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <label
                                    htmlFor="password_confirmation"
                                    className="text-sm font-medium text-neutral-800"
                                >
                                    Confirm password
                                </label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={5}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <button
                                type="submit"
                                className="btn-brand mt-2"
                                tabIndex={6}
                                data-test="register-user-button"
                            >
                                Create account
                            </button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    eyebrow: 'Welcome',
    title: 'Sign up to WODI',
    description:
        'Follow your child’s day, message teachers, and stay in the loop.',
    altPrompt: 'Already a member?',
    altLabel: 'Sign in',
    altHref: '/login',
};
