import { BranchType } from "./BranchType";

export interface CompanyType {
    uuid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches: BranchType[],
}