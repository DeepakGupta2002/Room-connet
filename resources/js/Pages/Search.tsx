import { Head, Link, router, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';

type Listing = { id: number; title: string; slug: string; listed_by_label: string; rent_amount: string; room_type: string; city: string; area: string; location_radius_meters: number; owner_contact_available: boolean };
type Props = { listings?: { data: Listing[]; total: number }; filters?: Record<string, string> };

export default function Search() {
    const { listings, filters = {} } = usePage<Props>().props;
    const [q, setQ] = useState(filters.q ?? '');

    function submit(event: FormEvent) {
        event.preventDefault();
        router.get('/search', { ...filters, q }, { preserveState: true, replace: true });
    }

    return <><Head title="Search Rooms" /><main className="min-h-screen bg-slate-50 px-4 py-6 text-slate-950 sm:px-8"><div className="mx-auto max-w-5xl"><div className="flex items-center justify-between"><Link href="/" className="font-black text-blue-700">RoomConnect</Link><Link href="/my-listings" className="text-sm font-semibold text-slate-600">My listings</Link></div><div className="mt-10"><p className="text-sm font-semibold text-blue-700">Broker-free discovery</p><h1 className="mt-2 text-3xl font-black">Find your next room</h1><p className="mt-2 text-slate-500">Approximate location only. Owner contact unlock ke baad milega.</p></div><form onSubmit={submit} className="mt-6 flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row"><input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search city, area or room" className="field" /><button className="button sm:w-auto">Search</button></form><div className="mt-5 flex items-center justify-between text-sm text-slate-500"><span>{listings?.total ?? 0} rooms found</span><span>Public results</span></div><div className="mt-4 grid gap-4 md:grid-cols-2">{listings?.data?.map((listing) => <article key={listing.id} className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><div className="flex items-start justify-between gap-3"><div><span className="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{listing.listed_by_label}</span><h2 className="mt-3 text-lg font-bold">{listing.title}</h2></div><span className="text-lg font-black text-slate-900">₹{Number(listing.rent_amount).toLocaleString('en-IN')}<small className="text-xs font-medium text-slate-500">/mo</small></span></div><p className="mt-3 text-sm capitalize text-slate-600">{listing.room_type.replaceAll('_', ' ')} · {listing.area}, {listing.city}</p><div className="mt-4 flex flex-wrap gap-2 text-xs"><span className="rounded-full bg-slate-100 px-3 py-1 text-slate-600">Approx. {listing.location_radius_meters}m zone</span>{listing.owner_contact_available && <span className="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">Owner contact available</span>}<span className="rounded-full bg-slate-100 px-3 py-1 text-slate-600">Phone protected</span></div><Link href={`/rooms/${listing.slug}`} className="mt-5 block w-full rounded-xl border border-blue-200 px-4 py-2.5 text-center text-sm font-bold text-blue-700">View room</Link></article>)}{(!listings?.data?.length) && <div className="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 md:col-span-2">No rooms found. Search another area.</div>}</div></div></main></>;
}
