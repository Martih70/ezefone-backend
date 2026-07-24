import React from 'react';
import { getInitials } from '../utils/getInitials';

export default function ReplaceFavoriteDialog({ contact, favorites, onSelect, onCancel }) {
    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-6">
            <div className="bg-amber-50 rounded-lg border-2 border-slate-300 shadow-xl w-full max-w-md p-6 space-y-4">
                <div>
                    <h2 className="text-xl font-bold text-slate-950">Favorites are full</h2>
                    <p className="text-base text-slate-700 mt-1">
                        Choose a favorite to replace with <span className="font-bold">{contact.name}</span>:
                    </p>
                </div>

                <div className="space-y-2">
                    {favorites.map((favorite) => (
                        <button
                            key={favorite.id}
                            onClick={() => onSelect(favorite.contact_id)}
                            className="w-full flex items-center gap-4 p-3 bg-white rounded-lg border-2 border-slate-300 hover:border-amber-600 hover:shadow-md active:scale-[0.98] transition-all text-left"
                        >
                            <div className="flex-shrink-0 w-10 h-10 bg-slate-100 border-2 border-slate-400 rounded-full flex items-center justify-center">
                                <span className="text-sm font-bold text-slate-950">
                                    {getInitials(favorite.contact.name)}
                                </span>
                            </div>
                            <span className="text-base font-bold text-slate-950 truncate">
                                {favorite.contact.name}
                            </span>
                        </button>
                    ))}
                </div>

                <button
                    onClick={onCancel}
                    className="w-full bg-slate-100 text-slate-950 px-6 py-3 rounded-lg text-base font-bold border-2 border-slate-400 hover:bg-slate-200 hover:border-slate-500 active:scale-95 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-slate-950"
                >
                    Cancel
                </button>
            </div>
        </div>
    );
}
