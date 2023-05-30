import { CompanyType } from "./CompanyType"
import { UserType } from "./UserType";
import { BranchType } from "./BranchType";

export interface EmployeeType {
    id: string,
    ulid: string,
    company: CompanyType,
    user: UserType,
    employee_accesses: BranchType[],
    selected_companies: string,
    selected_accesses: string,
    code: string,
    join_date: string,
    status: string,
}