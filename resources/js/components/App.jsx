import React, { useState } from 'react';
import HomeScreen from './HomeScreen';
import ContactsScreen from './ContactsScreen';
import PhoneScreen from './PhoneScreen';
import MessagesScreen from './MessagesScreen';
import WhatsAppScreen from './WhatsAppScreen';

export default function App() {
    const [currentScreen, setCurrentScreen] = useState('home');

    const handleNavigate = (screen) => {
        setCurrentScreen(screen);
    };

    const handleBack = () => {
        setCurrentScreen('home');
    };

    const backgroundPattern = {
        backgroundColor: '#fefdf8',
        backgroundImage: `repeating-linear-gradient(
            45deg,
            #fefdf8 0px,
            #fefdf8 8px,
            rgba(168, 162, 158, 0.5) 8px,
            rgba(168, 162, 158, 0.5) 10px
        )`
    };

    return (
        <div className="w-full h-screen" style={backgroundPattern}>
            {currentScreen === 'home' && (
                <div className="w-full h-full flex items-center justify-center">
                    <HomeScreen onNavigate={handleNavigate} />
                </div>
            )}
            {currentScreen === 'phone' && <PhoneScreen onBack={handleBack} />}
            {currentScreen === 'sms' && <MessagesScreen onBack={handleBack} />}
            {currentScreen === 'contacts' && <ContactsScreen onBack={handleBack} />}
            {currentScreen === 'whatsapp' && <WhatsAppScreen onBack={handleBack} />}
        </div>
    );
}
