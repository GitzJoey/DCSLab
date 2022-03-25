import axios from "@/axios";
import _ from "lodash";

export async function canUserAccess(to, userContext) {
    try {
        if (to.matched.some(r => r.meta.acl)) {
            //userRoles = userContext.roles_description.split(',');
            console.log(userContext.roles_description);
            _.forEach(to.meta.acl, (val) => {
                
            });
        }
        /*
        const response = axios.post('/api/post/dashboard/core/user/access', {
            to: to.path
        });
        return (await response).data;
        */
       return true;
    } catch (err) {
        return false;
    }
}

export function checkPasswordExpiry(userContext) {
    if (userContext.password_expiry_day <= 0) {
        
    }
    return true;
}

export function checkUserStatus(userContext) {
    if (userContext.profile !== undefined && userContext.profile.status === 0) {
        
    }

    return true;
}