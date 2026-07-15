import { Form } from '@inertiajs/react';
import { useRef, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import Modal from '@/components/modal';
import PasswordInput from '@/components/password-input';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const [open, setOpen] = useState(false);

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="Delete account"
                description="Delete your account and all of its resources"
            />
            <div className="space-y-4 rounded-[4px] border border-red-100 bg-red-50 p-4">
                <div className="space-y-0.5 text-red-600">
                    <p className="font-medium">Warning</p>
                    <p className="text-sm">
                        Please proceed with caution, this cannot be undone.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setOpen(true)}
                    className="btn-red"
                    data-test="delete-user-button"
                >
                    Delete account
                </button>
            </div>

            <Modal
                open={open}
                onClose={() => setOpen(false)}
                title="Delete account"
                maxWidth="lg"
            >
                <p className="text-sm text-neutral-600">
                    Once your account is deleted, all of its resources and data
                    will also be permanently deleted. Please enter your password
                    to confirm you would like to permanently delete your account.
                </p>

                <Form
                    {...ProfileController.destroy.form()}
                    options={{ preserveScroll: true }}
                    onError={() => passwordInput.current?.focus()}
                    resetOnSuccess
                    className="mt-6 space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <label htmlFor="password" className="sr-only">
                                    Password
                                </label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    ref={passwordInput}
                                    placeholder="Password"
                                    autoComplete="current-password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex justify-end gap-2">
                                <button
                                    type="button"
                                    className="btn-light"
                                    onClick={() => setOpen(false)}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="btn-red"
                                    disabled={processing}
                                    data-test="confirm-delete-user-button"
                                >
                                    Delete account
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </Modal>
        </div>
    );
}
