import { branchType } from "./branchType";

export interface companyType {
    uuid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches: branchType[],
}