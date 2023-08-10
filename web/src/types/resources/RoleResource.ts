import { PermissionResource } from "./PermissionResource";

export interface RoleResource {
    id: string,
    display_name: string,
    permissions: PermissionResource[],
    permission_descriptions: string,
}