import { BranchType } from "./BranchType";

export interface CompanyType {
    ulid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches: BranchType[],
}