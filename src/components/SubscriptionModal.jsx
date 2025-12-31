import React from 'react';

export default function SubscriptionModal({
  subscription,
  onClose,
  onStatusChange,
  getStatusBadgeClass,
  formatDate,
  formatCurrency,
}){
  if (!subscription) {
    return null;
  }

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div className="p-6 border-b border-gray-200 flex justify-between items-center">
          <h2 className="text-2xl font-bold text-gray-900">Subscription Details</h2>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-600"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div className="p-6 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-500">Subscription ID</label>
              <p className="mt-1 text-sm text-gray-900">#{subscription.id}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Status</label>
              <p className="mt-1">
                <span
                  className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusBadgeClass(
                    subscription.status
                  )}`}
                >
                  {subscription.status}
                </span>
              </p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Customer</label>
              <p className="mt-1 text-sm text-gray-900">{subscription.user_name}</p>
              <p className="text-sm text-gray-500">{subscription.user_email}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Product</label>
              <p className="mt-1 text-sm text-gray-900">{subscription.product_name}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Order Number</label>
              <p className="mt-1 text-sm text-gray-900">#{subscription.order_number}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Price</label>
              <p className="mt-1 text-sm text-gray-900">{formatCurrency(subscription.subscription_price)}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Billing Cycle</label>
              <p className="mt-1 text-sm text-gray-900">{subscription.billing_cycle}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Sign-up Fee</label>
              <p className="mt-1 text-sm text-gray-900">{formatCurrency(subscription.sign_up_fee)}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Start Date</label>
              <p className="mt-1 text-sm text-gray-900">{formatDate(subscription.start_date)}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Next Payment</label>
              <p className="mt-1 text-sm text-gray-900">{formatDate(subscription.next_payment_date)}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">Last Payment</label>
              <p className="mt-1 text-sm text-gray-900">{formatDate(subscription.last_payment_date)}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-500">End Date</label>
              <p className="mt-1 text-sm text-gray-900">
                {subscription.end_date ? formatDate(subscription.end_date) : 'Never'}
              </p>
            </div>
            {subscription.trial_length > 0 && (
              <>
                <div>
                  <label className="block text-sm font-medium text-gray-500">Trial Length</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {subscription.trial_length} {subscription.trial_period}(s)
                  </p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-500">Trial End Date</label>
                  <p className="mt-1 text-sm text-gray-900">{formatDate(subscription.trial_end_date)}</p>
                </div>
              </>
            )}
          </div>
          <div className="pt-4 border-t border-gray-200 flex gap-2">
            {subscription.status === 'active' && (
              <>
                <button
                  onClick={() => {
                    onStatusChange(subscription.id, 'pause');
                    onClose();
                  }}
                  className="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700"
                >
                  Pause Subscription
                </button>
                <button
                  onClick={() => {
                    onStatusChange(subscription.id, 'cancel');
                    onClose();
                  }}
                  className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                  Cancel Subscription
                </button>
              </>
            )}
            {subscription.status === 'on-hold' && (
              <button
                onClick={() => {
                  onStatusChange(subscription.id, 'resume');
                  onClose();
                }}
                className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
              >
                Resume Subscription
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

