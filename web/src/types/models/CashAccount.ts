import { Company } from "./Company"
import { Branch } from "./Branch"

export interface CashAccount {
    id: string,
    ulid: string,
    company: Company,
    branch: Branch,
    code: string,
    name: string,
    is_bank: boolean,
    is_active: boolean,
    remarks: string,
}
