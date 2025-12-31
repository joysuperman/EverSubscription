import React from 'react';

export default function SubscriptionRow ({ subscription, onStatusChange, onView, onDelete, getStatusBadgeClass, formatDate, formatCurrency }) {
  return (
    <tr className="hover:bg-gray-50">
      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
        #{subscription.id}
      </td>
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="text-sm text-gray-900">{subscription.user_name}</div>
        <div className="text-sm text-gray-500">{subscription.user_email}</div>
      </td>
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="text-sm text-gray-900">{subscription.product_name}</div>
        <div className="text-sm text-gray-500">Order #{subscription.order_number}</div>
      </td>
      <td className="px-6 py-4 whitespace-nowrap">
        <span
          className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusBadgeClass(
            subscription.status
          )}`}
        >
          {subscription.status}
        </span>
      </td>
      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
        {formatCurrency(subscription.subscription_price)}
      </td>
      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        {subscription.billing_cycle}
      </td>
      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        {formatDate(subscription.next_payment_date)}
      </td>
      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div className="flex gap-2">
          {subscription.status === 'active' && (
            <>
              <button
                onClick={() => onStatusChange(subscription.id, 'pause')}
                className="text-orange-600 hover:text-orange-900"
              >
                Pause
              </button>
              <button
                onClick={() => onStatusChange(subscription.id, 'cancel')}
                className="text-red-600 hover:text-red-900"
              >
                Cancel
              </button>
            </>
          )}
          {subscription.status === 'on-hold' && (
            <button
              onClick={() => onStatusChange(subscription.id, 'resume')}
              className="text-green-600 hover:text-green-900"
            >
              Resume
            </button>
          )}
          <button
            onClick={() => onView(subscription)}
            className="text-blue-600 hover:text-blue-900"
          >
            View
          </button>
          <button
            onClick={() => onDelete(subscription.id)}
            className="text-red-600 hover:text-red-900"
          >
            Delete
          </button>
        </div>
      </td>
    </tr>
  );
};

