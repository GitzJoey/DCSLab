import { CompanyType } from "./CompanyType";

export interface UserProfileType {
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: {
        full_name: string,
        status: string,
        img_path: string,
        remarks: string,
    },
    companies: CompanyType[],
}