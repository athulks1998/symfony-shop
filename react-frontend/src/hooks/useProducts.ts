import { useEffect, useState } from 'react';
import { getProducts } from '../services/api';
import type { Product } from '../types';

export function useProducts(category?: string) {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading]   = useState(true);
  const [error, setError]       = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    getProducts({ category })
      .then(setProducts)
      .catch(e => setError(e.response?.data?.message ?? 'Failed to load products.'))
      .finally(() => setLoading(false));
  }, [category]);

  return { products, loading, error };
}
