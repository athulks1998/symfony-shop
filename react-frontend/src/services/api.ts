import axios from 'axios';
import type { Product, Order, CreateProductPayload, CreateOrderPayload, OrderStatus } from '../types';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json' },
});

// ---------- Products ----------

export const getProducts = (params?: { page?: number; limit?: number; category?: string }) =>
  api.get<Product[]>('/products', { params }).then(r => r.data);

export const getProduct = (id: number) =>
  api.get<Product>(`/products/${id}`).then(r => r.data);

export const createProduct = (payload: CreateProductPayload) =>
  api.post<Product>('/products', payload).then(r => r.data);

export const updateProduct = (id: number, payload: CreateProductPayload) =>
  api.put<Product>(`/products/${id}`, payload).then(r => r.data);

export const deleteProduct = (id: number) =>
  api.delete(`/products/${id}`);

// ---------- Orders ----------

export const getOrders = (params?: { status?: OrderStatus; email?: string }) =>
  api.get<Order[]>('/orders', { params }).then(r => r.data);

export const getOrder = (id: number) =>
  api.get<Order>(`/orders/${id}`).then(r => r.data);

export const createOrder = (payload: CreateOrderPayload) =>
  api.post<Order>('/orders', payload).then(r => r.data);

export const updateOrderStatus = (id: number, status: OrderStatus) =>
  api.patch<Order>(`/orders/${id}/status`, { status }).then(r => r.data);
