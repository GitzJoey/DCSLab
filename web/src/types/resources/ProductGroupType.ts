import { CompanyType } from "./CompanyType"

export interface ProductGroupType {
    id: string,
    ulid: string,
    company: CompanyType,
    code: string,
    name: string,
    category: string,
}