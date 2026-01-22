import { Company } from "./Company";

export interface Investor {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    remarks: string,
}