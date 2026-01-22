export interface ProductUnitReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    product_id?: string | null;
    unit_id?: string | null;
    is_base?: boolean | null;
    is_primary_unit?: boolean | null;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface ProductUnitReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    product_id?: string | null;
    unit_id?: string | null;
    is_base?: boolean | null;
    is_primary_unit?: boolean | null;
    refresh: boolean;
    limit: number;
}

export interface ProductUnitStoreRequest {
    product_id?: string; // Optional because usually inferred from parent
    code: string;
    is_manufacturer_sku: boolean;
    unit_id: string;
    unit_name?: string;
    price: number;
    is_base: boolean;
    conversion_value: number;
    is_primary_unit: boolean;
    point: number;
    remarks?: string | null;
}

export interface ProductUnitUpdateRequest {
    id?: string;
    code: string;
    is_manufacturer_sku: boolean;
    unit_id: string;
    unit_name?: string;
    price: number;
    is_base: boolean;
    conversion_value: number;
    is_primary_unit: boolean;
    point: number;
    remarks?: string | null;
}