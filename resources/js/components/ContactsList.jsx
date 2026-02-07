import React from 'react';
import { getInitials } from '../utils/getInitials';

export default function ContactsList({ contacts, favorites, onAddFavorite, onRemoveFavorite }) {
    const isFavorite = (contactId) => {
        return favorites.some(fav => fav.contact_id === contactId);
    };

    const toggleFavorite = (contact) => {
        if (isFavorite(contact.id)) {
            onRemoveFavorite(contact.id);
        } else {
            onAddFavorite(contact.id);
        }
    };

    return (
        <div className="space-y-3">
            {contacts.length === 0 ? (
                <p className="text-lg text-slate-700 py-8 text-center font-semibold">No contacts yet</p>
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
                            <div className="flex gap-3 text-sm text-slate-700 mt-1">
                                {contact.phone && <span className="truncate">Phone: {contact.phone}</span>}
                                {contact.email && <span className="truncate">Email: {contact.email}</span>}
                            </div>
                        </div>

                        {/* Star Button */}
                        <button
                            onClick={() => toggleFavorite(contact)}
                            className={`flex-shrink-0 w-10 h-10 rounded-full font-bold text-lg transition-all active:scale-90 flex items-center justify-center border-2 ${
                                isFavorite(contact.id)
                                    ? 'bg-amber-100 text-amber-700 border-amber-600'
                                    : 'bg-slate-100 text-slate-500 border-slate-300'
                            }`}
                        >
                            {isFavorite(contact.id) ? '★' : '☆'}
                        </button>
                    </div>
                ))
            )}
        </div>
    );
}
