import { Branch } from "./Branch";

export interface Company {
    id: string,
    ulid: string,
    code: string,
    name: string,
    address: string,
    default: boolean,
    status: string,
    branches?: Array<Branch>,
}