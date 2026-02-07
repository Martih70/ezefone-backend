import React, { useState } from 'react';

export default function AddContactForm({ onSubmit }) {
    const [name, setName] = useState('');
    const [phone, setPhone] = useState('');
    const [email, setEmail] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        if (name.trim()) {
            onSubmit({ name, phone, email });
            setName('');
            setPhone('');
            setEmail('');
        }
    };

    return (
        <form onSubmit={handleSubmit} className="bg-amber-50 p-6 rounded-lg border-2 border-slate-300 space-y-4">
            <div>
                <label className="block text-base font-bold text-slate-950 mb-2">
                    Name *
                </label>
                <input
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full px-4 py-3 text-base border-2 border-slate-400 rounded-lg bg-white text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2"
                    placeholder="Enter name"
                    required
                />
            </div>

            <div>
                <label className="block text-base font-bold text-slate-950 mb-2">
                    Phone
                </label>
                <input
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full px-4 py-3 text-base border-2 border-slate-400 rounded-lg bg-white text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2"
                    placeholder="Enter phone number"
                />
            </div>

            <div>
                <label className="block text-base font-bold text-slate-950 mb-2">
                    Email
                </label>
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-4 py-3 text-base border-2 border-slate-400 rounded-lg bg-white text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2"
                    placeholder="Enter email"
                />
            </div>

            <button
                type="submit"
                className="w-full bg-emerald-600 text-white px-6 py-4 rounded-lg text-base font-bold hover:bg-emerald-700 active:scale-[0.98] transition-all focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600"
            >
                Save Contact
            </button>
        </form>
    );
}
