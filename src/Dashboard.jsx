import React, { useState, useEffect } from 'react';
import Navbar from './Navbar';
import StatsCards from './components/StatsCards';
import Filters from './components/Filters';
import SubscriptionsTable from './components/SubscriptionsTable';
import Pagination from './components/Pagination';
import SubscriptionModal from './components/SubscriptionModal';

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

export default function Dashboard() {
    const [subscriptions, setSubscriptions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [stats, setStats] = useState({});
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage] = useState(20);
    const [statusFilter, setStatusFilter] = useState('');
    const [selectedSubscription, setSelectedSubscription] = useState(null);

    // --- Helper Functions (Previously Missing) ---
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString();
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', { 
            style: 'currency', 
            currency: 'USD' 
        }).format(amount || 0);
    };

    const getStatusBadgeClass = (status) => {
        const base = "px-2 py-1 rounded-full text-xs font-medium ";
        switch (status) {
            case 'active': return base + "bg-green-100 text-green-800";
            case 'cancelled': return base + "bg-red-100 text-red-800";
            case 'pending': return base + "bg-yellow-100 text-yellow-800";
            case 'on-hold': return base + "bg-orange-100 text-orange-800";
            default: return base + "bg-gray-100 text-gray-800";
        }
    };

    // Load data
    useEffect(() => {
        loadStats();
        loadSubscriptions();
    }, [currentPage, statusFilter]);

    const loadStats = async () => {
        try {
            const response = await apiFetch({
                path: `${API_NAMESPACE}/subscriptions/stats`,
                method: 'GET',
            });
            setStats(response);
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    };

    const loadSubscriptions = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                ...(statusFilter && { status: statusFilter }),
            });
            const response = await apiFetch({
                path: `${API_NAMESPACE}/subscriptions?${params.toString()}`,
                method: 'GET',
            });
            setSubscriptions(response);
        } catch (error) {
            console.error('Error loading subscriptions:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleStatusChange = async (subscriptionId, action) => {
        try {
            let endpoint = `${API_NAMESPACE}/subscriptions/${subscriptionId}`;
            
            if (['cancel', 'pause', 'resume'].includes(action)) {
                endpoint += `/${action}`;
            } else {
                endpoint += `/status`;
            }

            const body = !['cancel', 'pause', 'resume'].includes(action) ? { status: action } : {};

            await apiFetch({
                path: endpoint,
                method: 'POST',
                ...(Object.keys(body).length > 0 && { data: body }),
            });

            loadSubscriptions();
            loadStats();
        } catch (error) {
            alert('Error updating subscription: ' + error.message);
        }
    };

    const handleDelete = async (subscriptionId) => {
        if (!confirm('Are you sure you want to delete this subscription?')) return;

        try {
            await apiFetch({
                path: `${API_NAMESPACE}/subscriptions/${subscriptionId}/delete`,
                method: 'DELETE',
            });
            loadSubscriptions();
            loadStats();
        } catch (error) {
            alert('Error deleting subscription: ' + error.message);
        }
    };

    return (
        <div className="eversubscription-admin p-6 bg-gray-50 min-h-screen">
            <div className="max-w-7xl mx-auto">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-2">EverSubscription Management</h1>
                    <p className="text-gray-600">Manage all your subscription products and customers</p>

                    <Navbar />
                </div>

                <StatsCards stats={stats} />

                <Filters
                    statusFilter={statusFilter}
                    onStatusFilterChange={(val) => { setStatusFilter(val); setCurrentPage(1); }}
                    onRefresh={loadSubscriptions}
                />

                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <SubscriptionsTable
                        subscriptions={subscriptions}
                        loading={loading}
                        onStatusChange={handleStatusChange}
                        onView={setSelectedSubscription}
                        onDelete={handleDelete}
                        getStatusBadgeClass={getStatusBadgeClass}
                        formatDate={formatDate}
                        formatCurrency={formatCurrency}
                    />

                    <Pagination
                        currentPage={currentPage}
                        subscriptions={subscriptions}
                        perPage={perPage}
                        onPageChange={setCurrentPage}
                    />
                </div>

                {selectedSubscription && (
                    <SubscriptionModal
                        subscription={selectedSubscription}
                        onClose={() => setSelectedSubscription(null)}
                        onStatusChange={handleStatusChange}
                        getStatusBadgeClass={getStatusBadgeClass}
                        formatDate={formatDate}
                        formatCurrency={formatCurrency}
                    />
                )}
            </div>
        </div>
    );
}