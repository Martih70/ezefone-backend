import React, { useState, useEffect } from 'react';
import { getInitials } from '../utils/getInitials';

export default function WhatsAppScreen({ onBack }) {
    const [contacts, setContacts] = useState([]);
    const [loading, setLoading] = useState(true);

    const patternStyle = {
        backgroundImage: `repeating-linear-gradient(
            45deg,
            transparent 0px,
            transparent 8px,
            rgba(168, 162, 158, 0.22) 8px,
            rgba(168, 162, 158, 0.22) 10px
        )`
    };

    useEffect(() => {
        fetchContacts();
    }, []);

    const fetchContacts = async () => {
        try {
            const response = await window.axios.get('/contacts');
            setContacts(response.data.contacts);
        } catch (error) {
            console.error('Failed to fetch contacts:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleWhatsApp = (phone) => {
        if (phone) {
            // Remove any non-digit characters and ensure proper formatting
            const cleanPhone = phone.replace(/\D/g, '');
            // Open WhatsApp web
            window.open(`https://wa.me/${cleanPhone}`, '_blank');
        }
    };

    return (
        <div className="w-full h-full flex flex-col bg-amber-50" style={patternStyle}>
            {/* Header */}
            <div className="bg-amber-50 border-b-4 border-teal-600 p-6 flex justify-between items-center shadow-sm">
                <div className="flex items-center gap-3">
                    <h1 className="text-3xl font-bold text-slate-950">WhatsApp</h1>
                </div>
                <button
                    onClick={onBack}
                    className="bg-slate-100 text-slate-950 px-6 py-3 rounded-lg text-base font-bold border-2 border-slate-400 hover:bg-slate-200 hover:border-slate-500 active:scale-95 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-slate-950"
                >
                    Back
                </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto flex flex-col gap-3 p-6">
                {loading ? (
                    <p className="text-base text-slate-700 font-semibold">Loading...</p>
                ) : contacts.length === 0 ? (
                    <p className="text-base text-slate-700 py-8 text-center font-semibold">No contacts available</p>
                ) : (
                    contacts.map((contact) => (
                        <div
                            key={contact.id}
                            className="flex items-center gap-4 p-4 bg-white rounded-lg border-2 border-slate-300 hover:shadow-md hover:border-slate-400 transition-all active:scale-[0.98]"
                        >
                            {/* Avatar */}
                            <div className="flex-shrink-0 w-12 h-12 bg-slate-100 border-2 border-slate-400 rounded-full flex items-center justify-center">
                                <span className="text-sm font-bold text-slate-950">
                                    {getInitials(contact.name)}
                                </span>
                            </div>

                            {/* Contact Info */}
                            <div className="flex-1 min-w-0">
                                <p className="text-base font-bold text-slate-950 truncate">{contact.name}</p>
                                {contact.phone && (
                                    <p className="text-sm text-slate-700 truncate">Phone: {contact.phone}</p>
                                )}
                            </div>

                            {/* WhatsApp Button */}
                            <button
                                onClick={() => handleWhatsApp(contact.phone)}
                                disabled={!contact.phone}
                                className={`flex-shrink-0 px-6 py-3 rounded-lg font-bold text-sm transition-all active:scale-95 ${
                                    contact.phone
                                        ? 'bg-teal-600 text-white hover:bg-teal-700'
                                        : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                                }`}
                            >
                                WhatsApp
                            </button>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}
