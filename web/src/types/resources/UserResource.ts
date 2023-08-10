import { CompanyResource } from "./CompanyResource";
import { ProfileResource } from "./ProfileResource";
import { RoleResource } from "./RoleResource";

export interface UserResource {
    id: string,
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: ProfileResource[],
    roles: RoleResource[],
    companies: CompanyResource[],
}