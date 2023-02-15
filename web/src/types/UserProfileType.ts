import { CompanyType } from "./CompanyType";
import { ProfileType } from "./ProfileType";

export interface UserProfileType {
    uuid: string,
    name: string,
    email: string,
    emailVerified: boolean,
    profile: ProfileType,
    companies: CompanyType[],
}