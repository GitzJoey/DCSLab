import { Company } from "./Company";
import { Product } from "./Product";
import { Unit } from "./Unit";

export interface ProductUnit {
  id: string;
  ulid: string;
  company: Company;
  product: Product;
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
