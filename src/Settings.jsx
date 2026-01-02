import React from 'react';
import Navbar from './Navbar';

export default function Settings() {
    return (
        <div className="p-6">
            <h2 className="text-2xl font-bold mb-4">Settings</h2>
            <p className="text-gray-700">Configure your EverSubscription plugin settings here.</p>
            <Navbar />
        </div>
    );
}