import { DropDownOption } from "../types/services/DropDownOption";

export default class CacheService {
    protected dcslabSystems;

    constructor() {
        this.dcslabSystems = sessionStorage.getItem('DCSLAB_SYSTEM');
    }

    public getCachedDDL(ddlname: string): Array<DropDownOption> | null {
        const ddl = this.dcslabSystems == null ? new Object() : JSON.parse(atob(this.dcslabSystems));

        const hasDDL = Object.hasOwnProperty.call(ddl, ddlname);
        return hasDDL ? ddl[ddlname] as Array<DropDownOption> : null;
    }

    public setCachedDDL(ddlname: string, value: Array<DropDownOption> | null): void {
        if (value == null) return;

        const new_dcslabSystems = this.dcslabSystems == null ? new Object() : JSON.parse(this.dcslabSystems);

        new_dcslabSystems[ddlname] = value;

        sessionStorage.setItem('DCSLAB_SYSTEM', btoa(JSON.stringify(new_dcslabSystems)));
    }
}