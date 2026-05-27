export interface Product {
  id: number;
  name: string;
  description: string | null;
  price: string;
  category: string | null;
  stock: number;
  active: boolean;
  createdAt: string;
  updatedAt: string | null;
}

export interface OrderItem {
  id: number;
  product: Pick<Product, 'id' | 'name' | 'price'>;
  quantity: number;
  unitPrice: string;
}

export type OrderStatus = 'pending' | 'confirmed' | 'shipped' | 'delivered' | 'cancelled';

export interface Order {
  id: number;
  reference: string;
  customerEmail: string;
  status: OrderStatus;
  totalAmount: string;
  shippingAddress: string | null;
  items: OrderItem[];
  createdAt: string;
  updatedAt: string | null;
}

export interface CreateProductPayload {
  name: string;
  description?: string;
  price: number;
  category?: string;
  stock?: number;
}

export interface CreateOrderPayload {
  customerEmail: string;
  shippingAddress?: string;
  items: { productId: number; quantity: number }[];
}

export interface ApiError {
  error: string;
  message: string;
}

export interface ValidationError {
  errors: { field: string; message: string }[];
}
