import { DropDownOption } from "../types/services/DropDownOption";
import { omit } from "lodash";

export default class CacheService {
    protected dcslabSystems;
    protected dcslabLastEntity;

    constructor() {
        this.dcslabSystems = sessionStorage.getItem('DCSLAB_SYSTEM');
        this.dcslabLastEntity = sessionStorage.getItem('DCSLAB_LAST_ENTITY');
    }

    public getCachedDDL(ddlname: string): Array<DropDownOption> | null {
        const ddl = this.dcslabSystems == null ? new Object() : JSON.parse(atob(this.dcslabSystems));

        const hasDDL = Object.hasOwnProperty.call(ddl, ddlname);
        return hasDDL ? ddl[ddlname] as Array<DropDownOption> : null;
    }

    public setCachedDDL(ddlname: string, value: Array<DropDownOption> | null): void {
        if (value == null) return;

        const new_dcslabSystems = this.dcslabSystems == null ? new Object() : JSON.parse(atob(this.dcslabSystems));

        new_dcslabSystems[ddlname] = value;

        sessionStorage.setItem('DCSLAB_SYSTEM', btoa(JSON.stringify(new_dcslabSystems)));
    }

    public getLastEntity(key: string): unknown | null {
        const entity = this.dcslabLastEntity == null ? null : JSON.parse(atob(this.dcslabLastEntity));
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

        sessionStorage.setItem('DCSLAB_LAST_ENTITY', btoa(JSON.stringify(newObj)));
    }

    public removeLastEntity(key: string) {
        const entity = this.dcslabLastEntity == null ? null : JSON.parse(atob(this.dcslabLastEntity));
        if (entity == null) return null;

        key = key.toUpperCase();
        const hasEntity = Object.hasOwnProperty.call(entity, key);
        if (!hasEntity) return null;

        let newObj: Record<string, unknown> = {};
        newObj = omit(entity, [key]);

        sessionStorage.setItem('DCSLAB_LAST_ENTITY', btoa(JSON.stringify(newObj)));
    }
}