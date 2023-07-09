import { DropDownOption } from "../types/services/DropDownOption";
import { omit } from "lodash";

export default class CacheService {
    protected DCSLAB_SYSTEM_KEY = 'DCSLAB_SYSTEM';
    protected DCSLAB_LAST_ENTITY_KEY = 'DCSLAB_LAST_ENTITY';

    public getCachedDDL(ddlname: string): Array<DropDownOption> | null {
        const dcslabSystems = sessionStorage.getItem(this.DCSLAB_SYSTEM_KEY);

        const ddl = dcslabSystems == null ? new Object() : JSON.parse(atob(dcslabSystems));

        const hasDDL = Object.hasOwnProperty.call(ddl, ddlname);

        return hasDDL ? ddl[ddlname] as Array<DropDownOption> : null;
    }

    public setCachedDDL(ddlname: string, value: Array<DropDownOption> | null): void {
        if (value == null) return;
        const dcslabSystems = sessionStorage.getItem(this.DCSLAB_SYSTEM_KEY);

        const new_dcslabSystems = dcslabSystems == null ? new Object() : JSON.parse(atob(dcslabSystems));

        new_dcslabSystems[ddlname] = value;

        sessionStorage.setItem(this.DCSLAB_SYSTEM_KEY, btoa(JSON.stringify(new_dcslabSystems)));
    }

    public getLastEntity(key: string): unknown | null {
        const dcslabLastEntity = sessionStorage.getItem(this.DCSLAB_LAST_ENTITY_KEY);

        const entity = dcslabLastEntity == null ? null : JSON.parse(atob(dcslabLastEntity));
        if (entity == null) return null;

        key = key.toUpperCase();
        const hasEntity = Object.hasOwnProperty.call(entity, key);
        if (!hasEntity) return null;

        return entity[key];
    }

    public setLastEntity(key: string, value: unknown | null) {
        key = key.toUpperCase();

        if (value == null) return;

        const newObj: Record<string, unknown> = {};
        newObj[key] = value;

        sessionStorage.setItem(this.DCSLAB_LAST_ENTITY_KEY, btoa(JSON.stringify(newObj)));
    }

    public removeLastEntity(key: string) {
        const dcslabLastEntity = sessionStorage.getItem(this.DCSLAB_LAST_ENTITY_KEY);

        const entity = dcslabLastEntity == null ? null : JSON.parse(atob(dcslabLastEntity));
        if (entity == null) return null;

        key = key.toUpperCase();
        const hasEntity = Object.hasOwnProperty.call(entity, key);
        if (!hasEntity) return null;

        let newObj: Record<string, unknown> = {};
        newObj = omit(entity, [key]);

        sessionStorage.setItem(this.DCSLAB_LAST_ENTITY_KEY, btoa(JSON.stringify(newObj)));
    }
}