import { BranchResource } from './BranchResource';
import { WarehouseResource } from './WarehouseResource';

export interface CompanyResource {
    id: string,
    ulid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches?: BranchResource[],
    warehouses?: WarehouseResource[],
}

