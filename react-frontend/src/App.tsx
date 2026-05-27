import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Navbar from './components/layout/Navbar';
import ProductsPage from './components/pages/ProductsPage';
import OrdersPage from './components/pages/OrdersPage';

export default function App() {
  return (
    <BrowserRouter>
      <div className="min-h-screen bg-gray-50">
        <Navbar />
        <Routes>
          <Route path="/"         element={<Navigate to="/products" replace />} />
          <Route path="/products" element={<ProductsPage />} />
          <Route path="/orders"   element={<OrdersPage />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}
