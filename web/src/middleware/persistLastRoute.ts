import type { RouteLocationNormalized } from "vue-router"

export const STORAGE_KEY_LAST_ROUTE = "DCSLAB_LAST_ROUTE"

export function persistLastRoute(to: RouteLocationNormalized): void {
    const forceRemember = to.matched.some(r => r.meta.rememberLastRoute === true)

    const isPublicRoute = to.matched.some(r => r.meta.public === true)

    if ((!isPublicRoute || forceRemember) && to.name) {
        sessionStorage.setItem(STORAGE_KEY_LAST_ROUTE, to.name as string)
    }
};