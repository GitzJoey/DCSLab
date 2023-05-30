import { CompanyType } from "./CompanyType";
import { ProfileType } from "./ProfileType";

export interface UserType {
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: ProfileType,
    companies: CompanyType[],
}