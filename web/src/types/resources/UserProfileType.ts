import { CompanyType } from "./CompanyType";

export interface UserProfileType {
    uuid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: {
        full_name: string
    },
    companies: CompanyType[],
}