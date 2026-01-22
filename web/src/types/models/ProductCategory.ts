import { Company } from "./Company"

export interface ProductCategory {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    type: number,
}