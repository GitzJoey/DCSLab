import { Company } from "./Company";
import { Unit } from "./Unit";

export interface ProductUnit {
    id: string;
    ulid: string;
    company: Company;
    code: string;
    is_manufacturer_sku: boolean;
    unit: Unit;
    price: number;
    is_base: boolean;
    conversion_value: number;
    is_primary_unit: boolean;
    point: number;
    remarks: string;
}
