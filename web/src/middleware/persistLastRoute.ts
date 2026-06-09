import type { RouteLocationNormalized } from "vue-router"

export const STORAGE_KEY_LAST_ROUTE = "DCSLAB_LAST_ROUTE"

export function persistLastRoute(to: RouteLocationNormalized): void {
    const shouldSkip = to.matched.some(r => r.meta.persistLastRoute === true)

    if (!shouldSkip) {
        sessionStorage.setItem(STORAGE_KEY_LAST_ROUTE, to.name as string)
    }
};