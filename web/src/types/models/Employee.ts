import { Company } from "./Company"
import { User } from "./User";
import { Branch } from "./Branch";

export interface Employee {
    id: string,
    ulid: string,
    company: Company,
    user: User,
    employee_accesses: Array<Branch>,
    selected_companies: string,
    selected_accesses: string,
    code: string,
    join_date: string,
    status: string,
}