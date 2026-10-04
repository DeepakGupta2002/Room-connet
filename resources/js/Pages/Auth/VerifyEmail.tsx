import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import AuthLayout from '../../Components/AuthLayout';
import { postJson } from '../../lib/auth';

export default function VerifyEmail() {
    const { flash } = usePage<{ flash?: { status?: string } }>().props;
    const [sent, setSent] = useState(false);
    const [error, setError] = useState('');

    async function resend() {
        setError('');
        try {
            await postJson('/email/verification-notification');
            setSent(true);
        } catch {
            setError('Email resend nahi ho saka. Thodi der baad try karein.');
        }
    }

    return (
        <AuthLayout title="Verify your email">
            <div className="mt-5 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-900">Inbox check karein aur verification link open karein. Contact unlock verification ke baad available hoga.</div>
            {flash?.status && <p className="mt-4 rounded-xl bg-amber-50 p-3 text-sm leading-6 text-amber-900">{flash.status}</p>}
            {sent && <p className="mt-4 text-sm font-medium text-emerald-700">Verification email dobara bhej diya gaya.</p>}
            {error && <p className="mt-4 text-sm text-red-600">{error}</p>}
            <button onClick={resend} className="button mt-6">Resend verification email</button>
        </AuthLayout>
    );
}
