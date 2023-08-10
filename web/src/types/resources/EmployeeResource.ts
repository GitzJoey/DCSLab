import { CompanyResource } from "./CompanyResource"
import { UserResource } from "./UserResource";
import { BranchResource } from "./BranchResource";

export interface EmployeeResource {
    id: string,
    ulid: string,
    company: CompanyResource,
    user: UserResource,
    employee_accesses: BranchResource[],
    selected_companies: string,
    selected_accesses: string,
    code: string,
    join_date: string,
    status: string,
}