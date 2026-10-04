import { Head, Link } from '@inertiajs/react';

export default function Search() {
    return (
        <><Head title="Search Rooms" /><main className="min-h-screen bg-slate-50 px-4 py-6 text-slate-950 sm:px-8"><div className="mx-auto max-w-5xl"><Link href="/" className="font-black text-blue-700">RoomConnect</Link><h1 className="mt-10 text-3xl font-black">Search rooms</h1><p className="mt-2 text-slate-500">Verified listings aur approximate locations yahan dikhenge.</p><div className="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">Search API next phase me connect hogi.</div></div></main></>
    );
}
