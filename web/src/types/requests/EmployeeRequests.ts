import { CompanyRequest } from "./CompanyRequests"
import { UserRequest } from "./UserRequests";
import { BranchRequest } from "./BranchFormRequest";

export interface EmployeeRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    user: UserRequest,
    employee_accesses: BranchRequest[],
    selected_companies: string,
    selected_accesses: string,
    code: string,
    join_date: string,
    status: string,
}