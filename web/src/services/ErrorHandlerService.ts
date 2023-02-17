export class ErrorHandlerService {
    public handleLaravelError(data: any, actions: any, forceSetToField?: string): void {
        if (data.errors !== undefined && Object.keys(data.errors).length > 0) {
            for (let key in data.errors) {
                for (let i = 0; i < data.errors[key].length; i++) {
                    actions.setFieldError(key, data.errors[key][i]);
                }
            }
        } else {
            if (forceSetToField) {
                actions.setFieldError(forceSetToField, data.status + ' ' + data.statusText +': ' + data.message);
            } else {
                console.error(data.status + ' ' + data.statusText);
            }
        }
    }
}