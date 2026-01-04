import React, { useEffect, useState } from 'react';

// API namespace used by the admin app
const API_NAMESPACE = 'eversubscription/v1';
const root = (window.eversubscriptionApi && window.eversubscriptionApi.root) ? window.eversubscriptionApi.root : '/wp-json';
export default function Settings() {
    const [settings, setSettings] = useState({
        enabled: true,
        subscription_message: '',
        default_interval: 1,
        default_period: 'month',
        admin_email: ''
    });
    const [status, setStatus] = useState('');

    useEffect(() => {
        
        fetch(root + `${API_NAMESPACE}/settings`)
            .then(res => res.json())
            .then(data => setSettings(prev => ({ ...prev, ...data })))
            .catch(() => {});
    }, []);

    const save = () => {
        setStatus('saving');
        fetch(root + `${API_NAMESPACE}/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.eversubscriptionApi ? window.eversubscriptionApi.nonce : ''
            },
            body: JSON.stringify(settings),
        })
            .then(res => {
                if (!res.ok) throw new Error('save-failed');
                return res.json();
            })
            .then(() => {
                setStatus('saved');
                setTimeout(() => setStatus(''), 2000);
            })
            .catch(() => setStatus('error'));
    };

    const reset = () => {
        setStatus('resetting');
        fetch(root + `${API_NAMESPACE}/settings`)
            .then(res => res.json())
            .then(data => {
                setSettings(prev => ({ ...prev, ...data }));
                setStatus('');
            })
            .catch(() => setStatus(''));
    };

    const roles = (window.eversubscriptionApi && window.eversubscriptionApi.roles) ? window.eversubscriptionApi.roles : {};

    return (
        <>
            <div className="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm">

                {/* Button Text */}
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Button Text</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Add to Cart Button Text</label>
                        <input
                            type="text"
                            value={settings.add_to_cart_button_text || ''}
                            onChange={e => setSettings({ ...settings, add_to_cart_button_text: e.target.value })}
                            placeholder="Sign up now"
                            className="mt-2 px-3 py-2 w-full border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Place Order Button Text</label>
                        <input
                            type="text"
                            value={settings.order_button_text || ''}
                            onChange={e => setSettings({ ...settings, order_button_text: e.target.value })}
                            placeholder="Sign up now"
                            className="mt-2 px-3 py-2 w-full border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                        />
                    </div>
                </div>

                {/* Roles */}
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Roles</h2>
                <p className="text-sm text-gray-600 dark:text-gray-300 mb-4">Choose the default roles to assign to active and inactive subscribers. Administrators are never automatically assigned these roles.</p>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Subscriber Default Role</label>
                        <select
                            value={settings.subscriber_role || 'subscriber'}
                            onChange={e => setSettings({ ...settings, subscriber_role: e.target.value })}
                            className="mt-2 px-3 py-2 w-full border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            {Object.entries(roles).map(([key, label]) => (
                                <option key={key} value={key}>{label}</option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Inactive Subscriber Role</label>
                        <select
                            value={settings.cancelled_role || 'customer'}
                            onChange={e => setSettings({ ...settings, cancelled_role: e.target.value })}
                            className="mt-2 px-3 py-2 w-full border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            {Object.entries(roles).map(([key, label]) => (
                                <option key={key} value={key}>{label}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Miscellaneous */}
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Miscellaneous</h2>
                <div className="grid grid-cols-1 gap-4 mb-6">
                    <div className="flex items-start space-x-3">
                        <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.zero_initial_payment_allows_without_payment_method} onChange={e => setSettings({ ...settings, zero_initial_payment_allows_without_payment_method: e.target.checked })} />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">$0 Initial Checkout</div>
                            <div className="text-sm text-gray-600 dark:text-gray-300">Allow a subscription product with a $0 initial payment to be purchased without providing a payment method.</div>
                        </div>
                    </div>

                    <div className="flex items-start space-x-3">
                        <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.drip_downloadable_on_renewal} onChange={e => setSettings({ ...settings, drip_downloadable_on_renewal: e.target.checked })} />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">Drip Downloadable Content</div>
                            <div className="text-sm text-gray-600 dark:text-gray-300">Enable dripping for downloadable content on subscription products.</div>
                        </div>
                    </div>

                    <div className="flex items-center space-x-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Customer Suspensions</label>
                            <select className="mt-2 px-3 py-2 border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 w-40" value={String(settings.max_customer_suspensions || 0)} onChange={e => setSettings({ ...settings, max_customer_suspensions: e.target.value })}>
                                {Array.from({ length: 13 }).map((_, i) => <option key={i} value={i}>{i}</option>)}
                                <option value="unlimited">Unlimited</option>
                            </select>
                            <p className="text-sm text-gray-600 dark:text-gray-300">suspensions per billing period.</p>
                        </div>

                        <div className="flex items-start space-x-3">
                            <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.multiple_purchase} onChange={e => setSettings({ ...settings, multiple_purchase: e.target.checked })} />
                            <div>
                                <div className="font-medium text-gray-900 dark:text-gray-100">Mixed Checkout</div>
                                <div className="text-sm text-gray-600 dark:text-gray-300">Allow multiple subscriptions and products to be purchased simultaneously.</div>
                            </div>
                        </div>

                        <div className="flex items-start space-x-3">
                            <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.enable_retry} onChange={e => setSettings({ ...settings, enable_retry: e.target.checked })} />
                            <div>
                                <div className="font-medium text-gray-900 dark:text-gray-100">Retry Failed Payments</div>
                                <div className="text-sm text-gray-600 dark:text-gray-300">Enable automatic retry of failed recurring payments.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Renewals */}
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Renewals</h2>
                <div className="grid grid-cols-1 gap-4 mb-6">
                    <div className="flex items-start space-x-3">
                        <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.accept_manual_renewals} onChange={e => setSettings({ ...settings, accept_manual_renewals: e.target.checked })} />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">Manual Renewal Payments</div>
                            <div className="text-sm text-gray-600 dark:text-gray-300">With manual renewals customers must login and pay to renew.</div>
                        </div>
                    </div>

                    <div className="flex items-start space-x-3">
                        <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.turn_off_automatic_payments} onChange={e => setSettings({ ...settings, turn_off_automatic_payments: e.target.checked })} />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">Turn off Automatic Payments</div>
                            <div className="text-sm text-gray-600 dark:text-gray-300">Disable automatic charging for new subscriptions.</div>
                        </div>
                    </div>
                </div>

                {/* Synchronisation */}
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Synchronisation</h2>
                <div className="grid grid-cols-1 gap-4 mb-6">
                    <div className="flex items-start space-x-3">
                        <input type="checkbox" className="mt-1 h-4 w-4" checked={!!settings.sync_payments} onChange={e => setSettings({ ...settings, sync_payments: e.target.checked })} />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">Synchronise renewals</div>
                            <div className="text-sm text-gray-600 dark:text-gray-300">Align subscription renewal to a specific day of the week, month or year.</div>
                        </div>
                    </div>

                    <div className="md:flex md:items-center md:space-x-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Prorate First Renewal</label>
                            <select value={settings.prorate_synced_payments || 'no'} onChange={e => setSettings({ ...settings, prorate_synced_payments: e.target.value })} className="mt-2 px-3 py-2 border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="no">Never (do not charge any recurring amount)</option>
                                <option value="recurring">Never (charge the full recurring amount at sign-up)</option>
                                <option value="virtual">For Virtual Subscription Products Only</option>
                                <option value="yes">For All Subscription Products</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Sign-up grace period (days)</label>
                            <input type="number" min="0" value={settings.days_no_fee || 0} onChange={e => setSettings({ ...settings, days_no_fee: parseInt(e.target.value || '0', 10) })} className="mt-2 px-3 py-2 w-32 border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
                            <p className="text-sm text-gray-600 dark:text-gray-300">days prior to Renewal Day</p>
                        </div>
                    </div>
                </div>

                {/* Switching */}
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Switching</h2>
                <div className="grid grid-cols-1 gap-4 mb-6">
                    <div className="flex items-start space-x-3">
                        <div className="space-y-2">
                            <label className="flex items-center space-x-2"><input type="checkbox" className="h-4 w-4" checked={!!settings.allow_switching_variable} onChange={e => setSettings({ ...settings, allow_switching_variable: e.target.checked })} /><span className="text-gray-900 dark:text-gray-100">Between Subscription Variations</span></label>
                            <label className="flex items-center space-x-2"><input type="checkbox" className="h-4 w-4" checked={!!settings.allow_switching_grouped} onChange={e => setSettings({ ...settings, allow_switching_grouped: e.target.checked })} /><span className="text-gray-900 dark:text-gray-100">Between Grouped Subscriptions</span></label>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">Switch Button Text</label>
                        <input type="text" value={settings.switch_button_text || ''} onChange={e => setSettings({ ...settings, switch_button_text: e.target.value })} className="mt-2 px-3 py-2 w-full border rounded-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                </div>

                <div className="flex items-center space-x-3 mt-2">
                    <button onClick={save} className="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Save settings</button>
                    <button onClick={reset} className="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md">Reset</button>

                    {status === 'saving' && <span className="text-sm text-gray-600 dark:text-gray-300">Saving…</span>}
                    {status === 'saved' && <span className="text-sm text-green-600">Saved</span>}
                    {status === 'error' && <span className="text-sm text-red-600">Error saving</span>}
                </div>
            </div>
        </>
    );
}