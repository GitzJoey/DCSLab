import { CompanyResource } from "./CompanyResource"

export interface UnitResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    code: string,
    name: string,
    description: string,
    category: string,
}