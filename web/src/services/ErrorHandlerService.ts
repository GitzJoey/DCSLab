export class ErrorHandlerService {
    public handleLaravelError(e: any, actions: any, forceSetToField?: string): void {
        if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
            for (var key in e.response.data.errors) {
                for (var i = 0; i < e.response.data.errors[key].size; i++) {
                    //actions.setFieldError(key, '');
                }
            }
        } else {
            actions.setFieldError(forceSetToField, e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message);
        }
    }
}