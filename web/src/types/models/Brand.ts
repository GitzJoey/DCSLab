import { Company } from "./Company"

export interface Brand {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
}