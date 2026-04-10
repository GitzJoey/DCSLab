import { ProductUnitStoreRequest, ProductUnitUpdateRequest } from '../product-unit/ProductUnitRequest';

export type ProductStockFilter = 'has_stock' | 'empty' | 'valid' | 'invalid';
export type SortDirection = 'asc' | 'desc';

export interface ProductWithRemainingStockFilter {
  end_date?: string | null;
  warehouse_id?: string | null;
  stock_filter?: ProductStockFilter | null;
  less_than?: number | null;
  greater_than?: number | null;
  include_service_products?: boolean | null;
  sort_by_remaining_stock?: SortDirection | null;
}

export interface ProductReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;

  category_id?: string | null;
  brand_id?: string | null;
  default_vat_profile_id?: string | null;
  is_price_include_vat?: boolean | null;
  is_use_serial_number?: boolean | null;
  is_expirable?: boolean | null;
  type?: number | null;
  status?: string | number;
  include_id?: string;
  with_remaining_stock?: ProductWithRemainingStockFilter | null;

  refresh: boolean;
  page: number;
  per_page: number;
}

export interface ProductReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;

  category_id?: string | null;
  brand_id?: string | null;
  default_vat_profile_id?: string | null;
  is_price_include_vat?: boolean | null;
  is_use_serial_number?: boolean | null;
  is_expirable?: boolean | null;
  type?: number | null;
  status?: string | number;
  include_id?: string;
  with_remaining_stock?: ProductWithRemainingStockFilter | null;

  refresh: boolean;
  limit: number;
}

export interface ProductPhysicalStoreRequest {
  company_id: string;
  code: string;
  category_id: string;
  brand_id: string;
  name: string;
  is_price_include_vat: boolean;
  is_use_serial_number: boolean;
  is_expirable: boolean;
  remarks?: string | null;
  type: number;
  status: number;

  product_units: Array<ProductUnitStoreRequest>;
  image_hashes?: {
    hash: string;
    is_main: boolean;
  }[];
}

export interface ProductPhysicalUpdateRequest {
  company_id: string;
  code: string;
  category_id: string;
  brand_id: string;
  name: string;
  is_price_include_vat: boolean;
  is_use_serial_number: boolean;
  is_expirable: boolean;
  remarks?: string | null;
  type: number;
  status: number;

  delete_product_unit_ids?: string[] | null;
  product_units: Array<ProductUnitUpdateRequest>;
  delete_image_ids?: string[] | null;
  image_hashes?: {
    hash: string;
    is_main: boolean;
  }[];
}

export interface ProductServiceStoreRequest {
  company_id: string;
  code: string;
  category_id: string;
  name: string;
  is_price_include_vat: boolean;
  remarks?: string | null;
  status: number;
  unit_id: string;
  price: number;
  point: number;

  image_hashes?: {
    hash: string;
    is_main: boolean;
  }[];
}

export interface ProductServiceUpdateRequest {
  company_id: string;
  code: string;
  category_id: string;
  name: string;
  is_price_include_vat: boolean;
  remarks?: string | null;
  status: number;
  unit_id: string;
  price: number;
  point: number;
  
  delete_image_ids?: string[] | null;
  image_hashes?: {
    hash: string;
    is_main: boolean;
  }[];
}
