import { DropdownOptionType } from "../types/DropdownOptionType";

export default class CacheService {
    protected dcslabSystems;

    constructor() {
        this.dcslabSystems = sessionStorage.getItem('DCSLAB_SYSTEM');
    }

    public getCachedDDL(ddlname: string): DropdownOptionType[] | null {
        const ddl = this.dcslabSystems == null ? new Object() : JSON.parse(this.dcslabSystems);
        
        const hasDDL = Object.hasOwnProperty.call(ddl, ddlname);
        return  hasDDL ? ddl[ddlname] : null;
    }
    
    public setCachedDDL(ddlname: string, value: DropdownOptionType[]) {
        const new_dcslabSystems = this.dcslabSystems == null ? new Object() : JSON.parse(this.dcslabSystems);

        new_dcslabSystems[ddlname] = value;
    
        sessionStorage.setItem('DCSLAB_SYSTEM', JSON.stringify(new_dcslabSystems));
    }    
}