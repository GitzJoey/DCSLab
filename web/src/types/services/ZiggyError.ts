export interface ZiggyError {
    ziggy: string,
}

export function isZiggyErrorType(obj: unknown): obj is ZiggyError {
    return (obj instanceof Object) && ('ziggy' in obj);
}