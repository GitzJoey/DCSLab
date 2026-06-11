import type { Permission } from "./Permission";

export interface Role {
    id: string,
    display_name: string,
    permissions?: Array<Permission>,
}