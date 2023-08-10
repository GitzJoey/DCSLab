import { PermissionRequest } from "./PermissionRequests";

export interface RoleRequest {
    id: string,
    display_name: string,
    permissions: PermissionRequest[],
    permission_descriptions: string,
}