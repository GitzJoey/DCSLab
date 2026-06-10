import type { RouteLocationNormalized, RouteLocationRaw } from "vue-router"

export function validateUser(to: RouteLocationNormalized, from: RouteLocationNormalized): boolean | RouteLocationRaw {
    const isAuthenticated = false
    
    const isProtectedRoute = to.name && to.matched.some(r => r.meta.public !== true);

    if (isProtectedRoute && !isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        }
    }

    if (to.name === 'login' && isAuthenticated) {
        return {
            name: 'dashboard-maindashboard',
        }
    }

    return true
}