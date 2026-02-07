import React from 'react';
import { getInitials } from '../utils/getInitials';

export default function FavoritesList({ favorites, onRemoveFavorite }) {
    const slots = 5;
    const emptySlots = Array.from({ length: Math.max(0, slots - favorites.length) });

    return (
        <div className="flex gap-3 overflow-x-auto pb-2">
            {/* Favorite contacts */}
            {favorites.map((favorite) => (
                <button
                    key={favorite.id}
                    onClick={() => onRemoveFavorite(favorite.contact_id)}
                    className="flex-shrink-0 w-24 h-24 flex flex-col items-center justify-center gap-2 bg-white border-2 border-slate-300 rounded-xl shadow-sm hover:shadow-md active:scale-95 transition-all group relative"
                >
                    <div className="absolute top-1 right-1 text-amber-600 opacity-0 group-hover:opacity-100 transition-opacity text-lg">★</div>
                    <div className="w-12 h-12 bg-slate-100 border-2 border-slate-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <span className="text-sm font-bold text-slate-950">
                            {getInitials(favorite.contact.name)}
                        </span>
                    </div>
                    <p className="text-xs font-bold text-slate-950 text-center line-clamp-1 px-1 group-hover:hidden">
                        {favorite.contact.name}
                    </p>
                    <p className="text-xs text-slate-700 hidden group-hover:block">Remove</p>
                </button>
            ))}

            {/* Empty placeholders */}
            {emptySlots.map((_, index) => (
                <div
                    key={`empty-${index}`}
                    className="flex-shrink-0 w-24 h-24 flex items-center justify-center bg-slate-50 rounded-xl border-2 border-dashed border-slate-400"
                >
                    <span className="text-2xl text-slate-500 font-bold">+</span>
                </div>
            ))}
        </div>
    );
}
