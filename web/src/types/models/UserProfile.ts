import type { Company } from "./Company";
import type { Profile } from "./Profile";
import type { Role } from "./Role";
import type { Setting } from "./Setting";

export interface UserProfile {
    id: string,
    ulid: string,
    name: string,
    email: string,
    email_verified: boolean,
    profile: Profile,
    roles: Array<Role>,
    companies: Array<Company>,
    settings: Setting,
    two_factor: boolean,
    personal_access_tokens: number,
}