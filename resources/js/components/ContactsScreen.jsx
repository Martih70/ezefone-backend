import React, { useState, useEffect } from 'react';
import ContactsList from './ContactsList';
import FavoritesList from './FavoritesList';
import AddContactForm from './AddContactForm';

export default function ContactsScreen({ onBack }) {
    const [contacts, setContacts] = useState([]);
    const [favorites, setFavorites] = useState([]);
    const [showAddForm, setShowAddForm] = useState(false);
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
            setFavorites(response.data.favorites);
        } catch (error) {
            console.error('Failed to fetch contacts:', error);
        } finally {
            setLoading(false);
        }
    };

    const addFavorite = async (contactId) => {
        try {
            await window.axios.post(`/contacts/${contactId}/favorite`);
            fetchContacts();
        } catch (error) {
            console.error('Failed to add favorite:', error);
        }
    };

    const removeFavorite = async (contactId) => {
        try {
            await window.axios.delete(`/contacts/${contactId}/favorite`);
            fetchContacts();
        } catch (error) {
            console.error('Failed to remove favorite:', error);
        }
    };

    const handleAddContact = async (contactData) => {
        try {
            await window.axios.post('/contacts', contactData);
            setShowAddForm(false);
            fetchContacts();
        } catch (error) {
            console.error('Failed to add contact:', error);
        }
    };

    return (
        <div className="w-full h-full flex flex-col bg-amber-50" style={patternStyle}>
            {/* Header */}
            <div className="bg-amber-50 border-b-4 border-amber-600 p-6 flex justify-between items-center shadow-sm">
                <div className="flex items-center gap-3">
                    <h1 className="text-3xl font-bold text-slate-950">Contacts</h1>
                </div>
                <button
                    onClick={onBack}
                    className="bg-slate-100 text-slate-950 px-6 py-3 rounded-lg text-base font-bold border-2 border-slate-400 hover:bg-slate-200 hover:border-slate-500 active:scale-95 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-slate-950"
                >
                    Back
                </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto flex flex-col gap-6 p-6">
                {/* Favorites Section */}
                {favorites.length > 0 && (
                    <div>
                        <h2 className="text-2xl font-bold mb-4 text-slate-950">Favorites</h2>
                        <FavoritesList
                            favorites={favorites}
                            onRemoveFavorite={removeFavorite}
                        />
                    </div>
                )}

                {/* All Contacts Section */}
                <div>
                    <h2 className="text-2xl font-bold mb-4 text-slate-950">All Contacts</h2>
                    {loading ? (
                        <p className="text-base text-slate-700 font-semibold">Loading...</p>
                    ) : (
                        <ContactsList
                            contacts={contacts}
                            favorites={favorites}
                            onAddFavorite={addFavorite}
                            onRemoveFavorite={removeFavorite}
                        />
                    )}
                </div>

                {/* Add Contact Button */}
                <button
                    onClick={() => setShowAddForm(!showAddForm)}
                    className="bg-amber-600 text-white px-6 py-4 rounded-lg text-base font-bold w-full hover:bg-amber-700 active:scale-[0.98] transition-all focus:ring-2 focus:ring-offset-2 focus:ring-amber-600"
                >
                    {showAddForm ? 'Cancel' : 'Add Contact'}
                </button>

                {/* Add Contact Form */}
                {showAddForm && (
                    <AddContactForm onSubmit={handleAddContact} />
                )}
            </div>
        </div>
    );
}
