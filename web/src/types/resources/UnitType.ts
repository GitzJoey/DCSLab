import { CompanyType } from "./CompanyType"

export interface UnitType {
    id: string,
    ulid: string,
    company: CompanyType,
    code: string,
    name: string,
    description: string,
    category: string,
}