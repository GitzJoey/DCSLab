import type { Company } from "./Company";
import type { Profile } from "./Profile";
import type { Role } from "./Role";
import type { Setting } from "./Setting";

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