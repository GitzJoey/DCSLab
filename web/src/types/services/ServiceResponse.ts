import { AxiosError } from 'axios';
import { ZiggyError } from './ZiggyError';

export interface ServiceResponse<T> {
    success: boolean;
    data?: T;
    error?: AxiosError | ZiggyError;
}