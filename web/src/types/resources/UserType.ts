import { CompanyType } from "./CompanyType";
import { ProfileType } from "./ProfileType";
import { RoleType } from "./RoleType";

export interface UserType {
    id: string,
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: ProfileType,
    roles: RoleType[],
    companies: CompanyType[],
}