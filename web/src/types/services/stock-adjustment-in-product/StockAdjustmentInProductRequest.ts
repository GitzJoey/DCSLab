export interface StockAdjustmentInProductReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    branch_id?: string | null;
    stock_adjustment_id?: string | null;
    search?: string | null;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface StockAdjustmentInProductReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    branch_id?: string | null;
    stock_adjustment_id?: string | null;
    search?: string | null;
    refresh: boolean;
    limit: number;
}

export interface StockAdjustmentInProductStoreRequest {
    company_id: string;
    branch_id: string;
    stock_adjustment_id: string;
    qty: number;
    product_unit_id: string;
    product_unit_conversion_value: number;
    product_unit_cogs: number;
    remarks?: string | null;
}

export interface StockAdjustmentInProductUpdateRequest {
    company_id: string;
    branch_id: string;
    stock_adjustment_id: string;
    qty: number;
    product_unit_id: string;
    product_unit_conversion_value: number;
    product_unit_cogs: number;
    remarks?: string | null;
}

