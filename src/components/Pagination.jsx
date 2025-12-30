import React from 'react';

export default function Pagination ({ currentPage, subscriptions, perPage, onPageChange }) {
  if (subscriptions.length === 0) {
    return null;
  }

  return (
    <div className="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
      <div className="text-sm text-gray-700">
        Page {currentPage}
      </div>
      <div className="flex gap-2">
        <button
          onClick={() => onPageChange(Math.max(1, currentPage - 1))}
          disabled={currentPage === 1}
          className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={subscriptions.length < perPage}
          className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>
  );
};

