import { omit } from "lodash";

export default class CacheService {
    protected debugMode = false;
    protected DCSLAB_SYSTEM_KEY = 'DCSLAB_SYSTEM';
    protected DCSLAB_LAST_ENTITY_KEY = 'DCSLAB_LAST_ENTITY';

    constructor() {
        this.debugMode = import.meta.env.VITE_APP_DEBUG;
    }

    public getLastEntity(key: string): unknown | null {
        const dcslabLastEntity = sessionStorage.getItem(this.DCSLAB_LAST_ENTITY_KEY);
        if (dcslabLastEntity == null) return null;

        const entity = this.debugMode ? JSON.parse(dcslabLastEntity) : JSON.parse(atob(dcslabLastEntity));
        if (entity == null) return null;

        key = key.toUpperCase();
        const hasEntity = Object.hasOwnProperty.call(entity, key);
        if (!hasEntity) return null;

        return entity[key];
    }

    public setLastEntity(key: string, value: unknown | null): void {
        key = key.toUpperCase();

        if (value == null) return;

        const newObj: Record<string, unknown> = {};
        newObj[key] = value;

        if (this.debugMode) {
            sessionStorage.setItem(this.DCSLAB_LAST_ENTITY_KEY, JSON.stringify(newObj));
        } else {
            sessionStorage.setItem(this.DCSLAB_LAST_ENTITY_KEY, btoa(JSON.stringify(newObj)));
        }
    }

    public removeLastEntity(key: string): void {
        const dcslabLastEntity = sessionStorage.getItem(this.DCSLAB_LAST_ENTITY_KEY);
        if (dcslabLastEntity == null) return;

        const entity = this.debugMode ? JSON.parse(dcslabLastEntity) : JSON.parse(atob(dcslabLastEntity));
        if (entity == null) return;

        key = key.toUpperCase();
        const hasEntity = Object.hasOwnProperty.call(entity, key);
        if (!hasEntity) return;

        let newObj: Record<string, unknown> = {};
        newObj = omit(entity, [key]);

        if (this.debugMode) {
            sessionStorage.setItem(this.DCSLAB_LAST_ENTITY_KEY, JSON.stringify(newObj));
        } else {
            sessionStorage.setItem(this.DCSLAB_LAST_ENTITY_KEY, btoa(JSON.stringify(newObj)));
        }
    }

    public isLastEntity(key: string): boolean {
        const dcslabLastEntity = sessionStorage.getItem(this.DCSLAB_LAST_ENTITY_KEY);
        if (dcslabLastEntity == null) return false;

        const entity = this.debugMode ? JSON.parse(dcslabLastEntity) : JSON.parse(atob(dcslabLastEntity));
        if (entity == null) return false;

        key = key.toUpperCase();

        return key in entity;
    }
}