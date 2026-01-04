import React, { useState, useEffect } from 'react';
import { Routes, Route } from "react-router-dom";
import Navbar from './Navbar';
import Dashboard from './Dashboard';
import Settings from './Settings';
import Footer from './Footer';

// Define the API Namespace
const API_NAMESPACE = '/eversubscription/v1';

// Improved apiFetch handler
const apiFetch = async (options) => {
    // 1. Try to use the native WordPress apiFetch if available
    if (window.wp && window.wp.apiFetch) {
        return window.wp.apiFetch(options);
    }

    // 2. Fallback to standard fetch using localized settings
    const apiSettings = window.eversubscriptionApi || {};
    const basePath = apiSettings.root || '/wp-json/';
    // Ensure path doesn't have leading slash if root has trailing, and vice versa
    const cleanPath = options.path.startsWith('/') ? options.path.substring(1) : options.path;
    const url = basePath + cleanPath;

    const response = await fetch(url, {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': apiSettings.nonce || '',
        },
        ...(options.data && { body: JSON.stringify(options.data) }),
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Request failed');
    }
    return response.json();
};

export default function App() {

    return (
        <>
            <div className="eversubscription-admin relative p-6 bg-gray-50 min-h-screen">
                <div className="container mx-auto">
                    <div className="mb-4">
                        <h1 className="text-3xl font-bold text-gray-900 mb-2">EverSubscription Management</h1>
                        <p className="text-gray-600">Manage all your subscription products and customers</p>
                    </div>
                    <Navbar />
                    <Routes>
                        <Route path="/" element={<Dashboard apiFetch={apiFetch} />} />
                        <Route path="/settings" element={<Settings apiFetch={apiFetch} />} />
                    </Routes>
                </div>
            </div>
            <Footer />
        </>
    );
}