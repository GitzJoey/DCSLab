import { companyType } from "./companyType"

export interface branchType {
    uuid: string,
    company: companyType,
    code: string,
    name: string,
    address: string,
    city: string,
    contact: string,
    status: string,
    isMain: boolean,
    remarks: string,
}