import axios from '../axios';

export async function canUserAccess(to) {
    try {
        const response = axios.get('/api/get/dashboard/core/user/access');
        return (await response).data;
    } catch (err) {
        return false;
    }
}

export function checkPasswordExpiry() {
    
}