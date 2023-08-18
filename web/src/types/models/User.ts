import { Company } from "./Company";
import { Profile } from "./Profile";
import { Role } from "./Role";
import { Setting } from "./Setting";

export interface User {
    id: string,
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: Profile,
    roles: Array<Role>,
    companies: Array<Company>,
    settings: Setting
}