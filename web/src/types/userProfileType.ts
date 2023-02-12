import { companyType } from "./companyType";
import { profileType } from "./profileType";

export interface userProfileType {
    uuid: string,
    name: string,
    email: string,
    emailVerified: boolean,
    profile: profileType,
    companies: companyType[],
}