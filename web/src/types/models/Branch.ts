import { Company } from "./Company"

export interface Branch {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    address: string,
    city: string,
    contact: string,
    is_main: boolean,
    remarks: string,
    status: string,
}