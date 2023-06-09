import { Company } from "./Company"

export interface CustomerGroup {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    max_open_invoice: number,
    max_outstanding_invoice: number,
    max_invoice_age: number,
    payment_term_type: string,
    payment_term: number,
    selling_point: number,
    selling_point_multiple: number,
    sell_at_cost: boolean,
    price_markup_percent: number,
    price_markup_nominal: number,
    price_markdown_percent: number,
    price_markdown_nominal: number,
    round_on: string,
    round_digit: number,
    remarks: string,
}