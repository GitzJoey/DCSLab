import { CompanyType } from "./CompanyType"

export interface BranchType {
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