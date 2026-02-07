import React, { useState, useEffect } from 'react';
import PhoneIcon from './icons/PhoneIcon';
import MessagesIcon from './icons/MessagesIcon';
import ContactsIcon from './icons/ContactsIcon';
import WhatsAppIcon from './icons/WhatsAppIcon';
import FavoritesList from './FavoritesList';

export default function HomeScreen({ onNavigate }) {
    const [selectedApp, setSelectedApp] = useState(null);
    const [favorites, setFavorites] = useState([]);

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
        fetchFavorites();
    }, []);

    const fetchFavorites = async () => {
        try {
            const response = await window.axios.get('/contacts');
            setFavorites(response.data.favorites);
        } catch (error) {
            console.error('Failed to fetch favorites:', error);
        }
    };

    const removeFavorite = async (contactId) => {
        try {
            await window.axios.delete(`/contacts/${contactId}/favorite`);
            fetchFavorites();
        } catch (error) {
            console.error('Failed to remove favorite:', error);
        }
    };

    const apps = [
        {
            id: 'phone',
            name: 'Phone',
            icon: PhoneIcon,
            accentColor: 'emerald-600',
            action: 'phone',
        },
        {
            id: 'messages',
            name: 'Messages',
            icon: MessagesIcon,
            accentColor: 'sky-600',
            action: 'sms',
        },
        {
            id: 'contacts',
            name: 'Contacts',
            icon: ContactsIcon,
            accentColor: 'amber-600',
            action: 'contacts',
        },
        {
            id: 'whatsapp',
            name: 'WhatsApp',
            icon: WhatsAppIcon,
            accentColor: 'teal-600',
            action: 'whatsapp',
        },
    ];

    const handleAppClick = (app) => {
        setSelectedApp(app.id);
        onNavigate(app.action);
    };

    return (
        <div className="w-full h-full flex flex-col bg-amber-50" style={patternStyle}>
            {/* Header with Logo */}
            <div className="bg-amber-50 border-b-2 border-slate-200 p-6 shadow-sm">
                <div className="max-w-2xl mx-auto">
                    <div className="flex items-center gap-3">
                        <div className="w-12 h-12 border-3 border-slate-950 rounded-lg flex items-center justify-center relative">
                            <span className="text-2xl font-bold text-slate-950">E</span>
                            <div className="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-600 rounded-full"></div>
                        </div>
                        <h1 className="text-3xl font-bold text-slate-950">EzeFone</h1>
                    </div>
                </div>
            </div>

            {/* Content Area */}
            <div className="flex-1 overflow-y-auto p-6">
                <div className="max-w-2xl mx-auto">
                    {/* Favorites Section */}
                    {favorites.length > 0 && (
                        <div className="mb-8">
                            <h2 className="text-2xl font-bold text-slate-950 mb-3">Quick Access</h2>
                            <FavoritesList
                                favorites={favorites}
                                onRemoveFavorite={removeFavorite}
                            />
                        </div>
                    )}

                    {/* Main Navigation */}
                    <div className="mb-2">
                        <h2 className="text-2xl font-bold text-slate-950 mb-4">Services</h2>
                    </div>
                    <div className="space-y-3">
                        {apps.map((app) => (
                            <AppButton
                                key={app.id}
                                app={app}
                                isSelected={selectedApp === app.id}
                                onClick={() => handleAppClick(app)}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

function AppButton({ app, isSelected, onClick }) {
    const Icon = app.icon;

    const accentColorMap = {
        'emerald-600': 'emerald-600',
        'sky-600': 'sky-600',
        'amber-600': 'amber-600',
        'teal-600': 'teal-600',
    };

    return (
        <button
            onClick={onClick}
            className={`group w-full h-16 rounded-lg bg-white border-l-4 border border-slate-300 hover:border-slate-400 hover:shadow-md transition-all duration-200 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 flex items-center gap-4 px-5`}
            style={{ borderLeftColor: app.accentColor === 'emerald-600' ? '#059669' : app.accentColor === 'sky-600' ? '#0284c7' : app.accentColor === 'amber-600' ? '#b45309' : '#0d9488' }}
        >
            <Icon className="w-6 h-6 flex-shrink-0 text-slate-700" />
            <span className="text-lg font-bold text-slate-900 flex-1 text-left">
                {app.name}
            </span>
            <span className="text-2xl text-slate-400 flex-shrink-0 transition-colors group-hover:text-slate-600">→</span>
        </button>
    );
}
