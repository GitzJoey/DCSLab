import { CompanyType } from "./CompanyType"

export interface BranchType {
    uuid: string,
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