import React, { useState } from 'react';
import { useOrders } from '../../hooks/useOrders';
import type { OrderStatus } from '../../types';

const STATUSES: OrderStatus[] = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

const STATUS_COLORS: Record<OrderStatus, string> = {
  pending:   'bg-yellow-100 text-yellow-800',
  confirmed: 'bg-blue-100 text-blue-800',
  shipped:   'bg-purple-100 text-purple-800',
  delivered: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
};

export default function OrdersPage() {
  const [filter, setFilter]         = useState<OrderStatus | undefined>(undefined);
  const { orders, loading, error, changeStatus } = useOrders(filter);

  if (loading) return <p className="p-8 text-gray-500">Loading orders…</p>;
  if (error)   return <p className="p-8 text-red-500">{error}</p>;

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Orders</h1>

      {/* Status filter tabs */}
      <div className="flex gap-2 mb-6 flex-wrap">
        <button
          onClick={() => setFilter(undefined)}
          className={`px-3 py-1 rounded-full text-sm border ${!filter ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600'}`}
        >
          All
        </button>
        {STATUSES.map(s => (
          <button
            key={s}
            onClick={() => setFilter(s)}
            className={`px-3 py-1 rounded-full text-sm border capitalize ${filter === s ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600'}`}
          >
            {s}
          </button>
        ))}
      </div>

      <div className="overflow-x-auto rounded-xl shadow">
        <table className="min-w-full bg-white text-sm">
          <thead className="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th className="px-6 py-3 text-left">Reference</th>
              <th className="px-6 py-3 text-left">Customer</th>
              <th className="px-6 py-3 text-center">Items</th>
              <th className="px-6 py-3 text-right">Total</th>
              <th className="px-6 py-3 text-center">Status</th>
              <th className="px-6 py-3 text-center">Update Status</th>
              <th className="px-6 py-3 text-left">Date</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {orders.map(order => (
              <tr key={order.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 font-mono text-gray-700">{order.reference}</td>
                <td className="px-6 py-4 text-gray-700">{order.customerEmail}</td>
                <td className="px-6 py-4 text-center text-gray-500">{order.items.length}</td>
                <td className="px-6 py-4 text-right font-semibold">€{parseFloat(order.totalAmount).toFixed(2)}</td>
                <td className="px-6 py-4 text-center">
                  <span className={`px-2 py-1 rounded-full text-xs font-medium capitalize ${STATUS_COLORS[order.status]}`}>
                    {order.status}
                  </span>
                </td>
                <td className="px-6 py-4 text-center">
                  <select
                    className="text-xs border border-gray-200 rounded px-2 py-1"
                    defaultValue=""
                    onChange={e => {
                      if (e.target.value) changeStatus(order.id, e.target.value as OrderStatus);
                    }}
                  >
                    <option value="" disabled>Move to…</option>
                    {STATUSES.filter(s => s !== order.status).map(s => (
                      <option key={s} value={s} className="capitalize">{s}</option>
                    ))}
                  </select>
                </td>
                <td className="px-6 py-4 text-gray-400 text-xs">
                  {new Date(order.createdAt).toLocaleDateString()}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
