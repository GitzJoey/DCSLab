import axios from "@/axios";

export async function canUserAccess(to, userContext) {
    try {
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