import { useEffect, useState } from 'react';
import { getOrders, updateOrderStatus } from '../services/api';
import type { Order, OrderStatus } from '../types';

export function useOrders(statusFilter?: OrderStatus) {
  const [orders, setOrders]   = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState<string | null>(null);

  const refresh = () => {
    setLoading(true);
    getOrders({ status: statusFilter })
      .then(setOrders)
      .catch(e => setError(e.response?.data?.message ?? 'Failed to load orders.'))
      .finally(() => setLoading(false));
  };

  useEffect(() => { refresh(); }, [statusFilter]);

  const changeStatus = async (id: number, status: OrderStatus) => {
    const updated = await updateOrderStatus(id, status);
    setOrders(prev => prev.map(o => (o.id === id ? updated : o)));
  };

  return { orders, loading, error, refresh, changeStatus };
}
