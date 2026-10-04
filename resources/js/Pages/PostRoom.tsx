import { Head, Link } from '@inertiajs/react';

export default function PostRoom() {
    return (
        <><Head title="Post a Room" /><main className="min-h-screen bg-slate-50 px-4 py-6 text-slate-950 sm:px-8"><div className="mx-auto max-w-3xl"><Link href="/" className="font-black text-blue-700">RoomConnect</Link><h1 className="mt-10 text-3xl font-black">Post a room</h1><p className="mt-2 text-slate-500">Tenant ya owner apni listing add kar sakta hai. Owner approval MVP me optional hai.</p><div className="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">Listing form next phase me database API ke saath connect hoga.</div></div></main></>
    );
}
