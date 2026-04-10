import { Company } from './Company';
import { Brand } from './Brand';
import { ProductCategory } from './ProductCategory';
import { ProductUnit } from './ProductUnit';
import { ProductImage } from './ProductImage';
import { VatProfile } from './VatProfile';

export interface Product {
  id: string;
  ulid: string;
  company: Company;
  code: string;
  category: ProductCategory;
  brand: Brand | null;
  default_vat_profile?: VatProfile | null;
  name: string;
  is_price_include_vat: boolean;
  is_use_serial_number: boolean;
  is_expirable: boolean;
  remarks: string;
  type: number;
  status: string;
  remaining_stock_base_unit?: number;
  product_units: ProductUnit[];
  product_images?: ProductImage[];
}
