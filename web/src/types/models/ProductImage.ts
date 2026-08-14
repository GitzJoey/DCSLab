export interface ProductImage {
  id: string;
  product_id?: string | null;
  path: string;
  hash: string;
  url: string;
  is_main: boolean;
}
