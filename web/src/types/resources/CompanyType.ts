import { BranchType } from "./BranchType";

export interface CompanyType {
    id: string,
    ulid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches: BranchType[],
}