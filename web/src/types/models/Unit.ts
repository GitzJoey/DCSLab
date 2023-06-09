import { Company } from "./Company"

export interface Unit {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    description: string,
    category: string,
}