import axios from '../axios';

export async function canUserAccess(to) {
    try {
        const response = axios.post('/api/post/dashboard/core/user/access', {
            to: to.path
        });
        return (await response).data;
    } catch (err) {
        return false;
    }
}

export function checkPasswordExpiry() {
    
}