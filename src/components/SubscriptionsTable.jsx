import React from 'react';
import SubscriptionRow from './SubscriptionRow';

export default function SubscriptionsTable ({
  subscriptions,
  loading,
  onStatusChange,
  onView,
  onDelete,
  getStatusBadgeClass,
  formatDate,
  formatCurrency,
}) {
  if (loading) {
    return (
      <div className="p-8 text-center">
        <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p className="mt-4 text-gray-600">Loading subscriptions...</p>
      </div>
    );
  }

  if (subscriptions.length === 0) {
    return (
      <div className="p-8 text-center text-gray-500">No subscriptions found</div>
    );
  }

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full divide-y divide-gray-200">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              ID
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Customer
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Product
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Price
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Billing Cycle
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Next Payment
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {subscriptions.map((subscription) => (
            <SubscriptionRow
              key={subscription.id}
              subscription={subscription}
              onStatusChange={onStatusChange}
              onView={onView}
              onDelete={onDelete}
              getStatusBadgeClass={getStatusBadgeClass}
              formatDate={formatDate}
              formatCurrency={formatCurrency}
            />
          ))}
        </tbody>
      </table>
    </div>
  );
};
