import { Company } from "./Company";
import { Profile } from "./Profile";
import { Role } from "./Role";

export interface User {
    id: string,
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: Profile,
    roles: Role[],
    companies: Company[],
}