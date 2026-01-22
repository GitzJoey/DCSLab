import { ProductUnitStoreRequest, ProductUnitUpdateRequest } from '../product-unit/ProductUnitRequest';

export interface ProductReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;

    category_id?: string | null;
    brand_id?: string | null;
    is_taxable?: boolean | null;
    vat_rate?: number | null;
    is_price_include_vat?: boolean | null;
    is_use_serial_number?: boolean | null;
    is_expirable?: boolean | null;
    type?: number | null;
    status?: string | number;
    include_id?: string;
    
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
    is_taxable?: boolean | null;
    vat_rate?: number | null;
    is_price_include_vat?: boolean | null;
    is_use_serial_number?: boolean | null;
    is_expirable?: boolean | null;
    type?: number | null;
    status?: string | number;
    include_id?: string;

    refresh: boolean;
    limit: number;
}

export interface ProductPhysicalStoreRequest {
    company_id: string;
    code: string;
    category_id: string;
    brand_id: string;
    name: string;
    slug: string;
    is_taxable: boolean;
    vat_rate: number;
    is_price_include_vat: boolean;
    is_use_serial_number: boolean;
    is_expirable: boolean;
    remarks?: string | null;
    type: number;
    status: number;
    product_units: Array<ProductUnitStoreRequest>;
}

export interface ProductPhysicalUpdateRequest {
    company_id: string;
    code: string;
    category_id: string;
    brand_id: string;
    name: string;
    slug: string;
    is_taxable: boolean;
    vat_rate: number;
    is_price_include_vat: boolean;
    is_use_serial_number: boolean;
    is_expirable: boolean;
    remarks?: string | null;
    type: number;
    status: number;
    delete_product_unit_ids?: string[] | null;
    product_units: Array<ProductUnitUpdateRequest>;
}

export interface ProductServiceStoreRequest {
    company_id: string;
    code: string;
    category_id: string;
    name: string;
    slug: string;
    is_taxable: boolean;
    vat_rate: number;
    is_price_include_vat: boolean;
    remarks?: string | null;
    status: number;
    unit_id: string;
    price: number;
    point: number;
}

export interface ProductServiceUpdateRequest {
    company_id: string;
    code: string;
    category_id: string;
    name: string;
    slug: string;
    is_taxable: boolean;
    vat_rate: number;
    is_price_include_vat: boolean;
    remarks?: string | null;
    status: number;
    unit_id: string;
    price: number;
    point: number;
}
