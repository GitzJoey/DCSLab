import { CompanyType } from "./CompanyType"

export interface BrandType {
    id: string,
    ulid: string,
    company: CompanyType,
    code: string,
    name: string,
}