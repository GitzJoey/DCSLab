import { CompanyRequest } from "./CompanyRequests"

export interface UnitRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    code: string,
    name: string,
    description: string,
    category: string,
}