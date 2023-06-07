import { CompanyType } from "./CompanyType";

export interface UserProfileType {
    id: string,
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: {
        full_name: string,
        first_name: string,
        last_name: string,
        address: string,
        city: string,
        postal_code: string,
        country: string,
        status: string,
        tax_id: number,
        ic_num: number,
        img_path: string,
        remarks: string,
    },
    companies: CompanyType[],
}