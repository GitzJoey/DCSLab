import { CompanyType } from "./CompanyType"
import { BranchType } from "./BranchType"

export interface WarehouseType {
    ulid: string,
    company: CompanyType,
    branch: BranchType,
    code: string,
    name: string,
    address: string,
    city: string,
    contact: string,
    status: string,
    remarks: string,
}