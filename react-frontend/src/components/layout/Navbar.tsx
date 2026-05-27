import React from 'react';
import { NavLink } from 'react-router-dom';

export default function Navbar() {
  const linkClass = ({ isActive }: { isActive: boolean }) =>
    `px-4 py-2 rounded-lg text-sm font-medium transition ${
      isActive ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'
    }`;

  return (
    <nav className="bg-white border-b border-gray-200 px-8 py-3 flex items-center gap-4 shadow-sm">
      <span className="text-lg font-bold text-indigo-700 mr-6">🛍 ShopAdmin</span>
      <NavLink to="/products" className={linkClass}>Products</NavLink>
      <NavLink to="/orders"   className={linkClass}>Orders</NavLink>
    </nav>
  );
}
