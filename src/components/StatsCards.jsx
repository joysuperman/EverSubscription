import React from 'react';

export default function StatsCards ({ stats }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-sm font-medium text-gray-500">Total Subscriptions</div>
        <div className="text-3xl font-bold text-gray-900 mt-2">{stats.all || 0}</div>
      </div>
      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-sm font-medium text-gray-500">Active</div>
        <div className="text-3xl font-bold text-green-600 mt-2">{stats.active || 0}</div>
      </div>
      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-sm font-medium text-gray-500">Pending</div>
        <div className="text-3xl font-bold text-yellow-600 mt-2">{stats.pending || 0}</div>
      </div>
      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-sm font-medium text-gray-500">On Hold</div>
        <div className="text-3xl font-bold text-orange-600 mt-2">{stats['on-hold'] || 0}</div>
      </div>
      <div className="bg-white rounded-lg shadow p-6">
        <div className="text-sm font-medium text-gray-500">Cancelled</div>
        <div className="text-3xl font-bold text-red-600 mt-2">{stats.cancelled || 0}</div>
      </div>
    </div>
  );
};

