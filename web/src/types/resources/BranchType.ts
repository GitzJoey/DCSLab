import { CompanyType } from "./CompanyType"

export interface BranchType {
    id: string,
    ulid: string,
    company: CompanyType,
    code: string,
    name: string,
    address: string,
    city: string,
    contact: string,
    status: string,
    is_main: boolean,
    remarks: string,
}