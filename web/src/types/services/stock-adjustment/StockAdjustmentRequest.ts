export interface StockAdjustmentReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    branch_id?: string | null;
    search?: string | null;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface StockAdjustmentReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    branch_id?: string | null;
    search?: string | null;
    refresh: boolean;
    limit: number;
}

export interface StockAdjustmentInProductNestedStoreRequest {
    qty: number;
    product_unit_id: string;
    product_unit_conversion_value: number;
    product_unit_cogs: number;
    remarks?: string | null;
}

export interface StockAdjustmentOutProductNestedStoreRequest {
    qty: number;
    product_unit_id: string;
    product_unit_conversion_value: number;
    remarks?: string | null;
}

export interface StockAdjustmentStoreRequest {
    company_id: string;
    branch_id: string;
    code: string;
    date: string;
    category_id: string;
    in_warehouse_id?: string | null;
    out_warehouse_id?: string | null;
    remarks?: string | null;
    is_posted: boolean;
    in_products?: StockAdjustmentInProductNestedStoreRequest[];
    out_products?: StockAdjustmentOutProductNestedStoreRequest[];
}

export interface StockAdjustmentInProductNestedUpdateRequest {
    id?: string;
    qty: number;
    product_unit_id: string;
    product_unit_conversion_value: number;
    product_unit_cogs: number;
    remarks?: string | null;
}

export interface StockAdjustmentOutProductNestedUpdateRequest {
    id?: string;
    qty: number;
    product_unit_id: string;
    product_unit_conversion_value: number;
    remarks?: string | null;
}

export interface StockAdjustmentUpdateRequest {
    company_id: string;
    branch_id: string;
    code: string;
    date: string;
    category_id: string;
    in_warehouse_id?: string | null;
    out_warehouse_id?: string | null;
    remarks?: string | null;
    is_posted: boolean;
    delete_in_product_ids?: string[] | null;
    in_products?: StockAdjustmentInProductNestedUpdateRequest[];
    delete_out_product_ids?: string[] | null;
    out_products?: StockAdjustmentOutProductNestedUpdateRequest[];
}
