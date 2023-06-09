import { AxiosError } from 'axios';
import { ZiggyError } from './ZiggyError';
import { ValidationError } from './ValidationError';

export interface ServiceResponse<T> {
    success: boolean;
    data?: T;
    error?: AxiosError | ValidationError | ZiggyError;
}