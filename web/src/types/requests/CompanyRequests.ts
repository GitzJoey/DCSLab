import { BranchRequest } from "./BranchRequests";

export interface CompanyRequest {
    id: string,
    ulid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches: BranchRequest[],
}