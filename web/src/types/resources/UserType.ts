import { CompanyType } from "../CompanyType";

export interface UserType {
    uuid: string,
    name: string,
    email: string,
    emailVerified: boolean,
    profile: {
        fullName: string
    },
    companies: CompanyType[],
}