import React, { useState } from 'react';
import { useProducts } from '../../hooks/useProducts';
import { deleteProduct } from '../../services/api';

const CATEGORIES = ['Electronics', 'Apparel', 'Kitchen', 'Sports', 'Office'];

export default function ProductsPage() {
  const [category, setCategory]   = useState<string | undefined>(undefined);
  const { products, loading, error } = useProducts(category);

  const handleDelete = async (id: number) => {
    if (!confirm('Soft-delete this product?')) return;
    await deleteProduct(id);
    window.location.reload();
  };

  if (loading) return <p className="p-8 text-gray-500">Loading products…</p>;
  if (error)   return <p className="p-8 text-red-500">{error}</p>;

  return (
    <div className="p-8">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Products</h1>
        <a
          href="/products/new"
          className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
        >
          + New Product
        </a>
      </div>

      {/* Category filter */}
      <div className="flex gap-2 mb-6 flex-wrap">
        <button
          onClick={() => setCategory(undefined)}
          className={`px-3 py-1 rounded-full text-sm border ${!category ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:border-indigo-400'}`}
        >
          All
        </button>
        {CATEGORIES.map(c => (
          <button
            key={c}
            onClick={() => setCategory(c)}
            className={`px-3 py-1 rounded-full text-sm border ${category === c ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:border-indigo-400'}`}
          >
            {c}
          </button>
        ))}
      </div>

      <div className="overflow-x-auto rounded-xl shadow">
        <table className="min-w-full bg-white text-sm">
          <thead className="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th className="px-6 py-3 text-left">ID</th>
              <th className="px-6 py-3 text-left">Name</th>
              <th className="px-6 py-3 text-left">Category</th>
              <th className="px-6 py-3 text-right">Price</th>
              <th className="px-6 py-3 text-right">Stock</th>
              <th className="px-6 py-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {products.map(p => (
              <tr key={p.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 text-gray-400">#{p.id}</td>
                <td className="px-6 py-4 font-medium text-gray-900">{p.name}</td>
                <td className="px-6 py-4 text-gray-500">{p.category ?? '—'}</td>
                <td className="px-6 py-4 text-right font-mono">€{parseFloat(p.price).toFixed(2)}</td>
                <td className="px-6 py-4 text-right">
                  <span className={`font-semibold ${p.stock < 5 ? 'text-red-500' : 'text-green-600'}`}>
                    {p.stock}
                  </span>
                </td>
                <td className="px-6 py-4 text-center space-x-3">
                  <a href={`/products/${p.id}/edit`} className="text-indigo-600 hover:underline">Edit</a>
                  <button onClick={() => handleDelete(p.id)} className="text-red-500 hover:underline">Delete</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
