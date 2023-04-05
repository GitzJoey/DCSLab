export default class CacheService {
    protected dcslabSystems;

    constructor() {
        this.dcslabSystems = sessionStorage.getItem('DCSLAB_SYSTEM');
    }

    public getCachedDDL(ddlname: string): any | null {
        let ddl = this.dcslabSystems == null ? new Object() : JSON.parse(this.dcslabSystems);
        
        let hasDDL = Object.hasOwnProperty.call(ddl, ddlname);
        return  hasDDL ? ddl[ddlname] : null;
    }
    
    public setCachedDDL(ddlname: string, value: any) {
        let new_dcslabSystems = this.dcslabSystems == null ? new Object() : JSON.parse(this.dcslabSystems);

        new_dcslabSystems[ddlname] = value;
    
        sessionStorage.setItem('DCSLAB_SYSTEM', JSON.stringify(new_dcslabSystems));
    }    
}