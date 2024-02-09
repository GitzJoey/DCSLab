import { Company } from "./Company"
import { Branch } from "./Branch"

export interface Warehouse {
    id: string,
    ulid: string,
    company: Company,
    branch: Branch,
    code: string,
    name: string,
    address: string,
    city: string,
    contact: string,
    remarks: string,
    status: string,
}