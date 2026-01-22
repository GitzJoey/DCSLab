import { Company } from "./Company";
import { Brand } from "./Brand";
import { ProductCategory } from "./ProductCategory";
import { ProductUnit } from "./ProductUnit";

export interface Product {
    id: string;
    ulid: string;
    company: Company;
    code: string;
    category: ProductCategory;
    brand: Brand;
    name: string;
    slug: string;
    is_taxable: boolean;
    vat_rate: number;
    is_price_include_vat: boolean;
    is_use_serial_number: boolean;
    is_expirable: boolean;
    remarks: string;
    type: number;
    status: string;
    product_units: ProductUnit[];
}
