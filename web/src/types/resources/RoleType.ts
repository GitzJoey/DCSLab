import { PermissionType } from "./PermissionType";

export interface RoleType {
    id: string,
    display_name: string,
    permissions: PermissionType[],
    permission_descriptions: string,
}