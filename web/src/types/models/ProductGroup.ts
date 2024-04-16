import { Company } from "./Company"

export interface ProductGroup {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    category: string,
}