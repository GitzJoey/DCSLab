export interface ZiggyError {
    ziggy: string,
}

export function isZiggyError(obj: unknown): obj is ZiggyError {
    return (obj instanceof Object) && ('ziggy' in obj);
}