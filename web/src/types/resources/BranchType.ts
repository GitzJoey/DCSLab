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
    is_main: boolean,
    remarks: string,
    status: string,
}