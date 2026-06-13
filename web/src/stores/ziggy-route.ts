import { ref, computed } from "vue";
import { defineStore } from "pinia";
import type { Config } from "ziggy-js";

const getDomain = (): string => {
  try {
    const domain = new URL(import.meta.env.VITE_BACKEND_URL);
    return domain.hostname || "localhost";
  } catch {
    return "localhost";
  }
};

const getDomainPort = (): number | undefined => {
  try {
    const domain = new URL(import.meta.env.VITE_BACKEND_URL);
    return domain.port ? Number(domain.port) : undefined;
  } catch {
    return undefined;
  }
};

export const useZiggyRouteStore = defineStore("ziggyRoute", () => {
  const ziggyRoute = ref<Config>({
    url: getDomain(),
    port: getDomainPort() as any,
    defaults: {},
    routes: {},
  });

  const getZiggy = computed((): Config => {
    const serializedZiggy = sessionStorage.getItem("ziggyRoute");
    
    if (serializedZiggy) {
      const isDebug = import.meta.env.VITE_APP_DEBUG === "true";
      try {
        const deserializedZiggy: Config = JSON.parse(
          isDebug ? serializedZiggy : atob(serializedZiggy)
        );
        ziggyRoute.value = deserializedZiggy;
      } catch (error) {
        console.error("Failed to parse Ziggy routes from session storage", error);
      }
    }
    
    return ziggyRoute.value;
  });

  const setZiggy = (ziggy: Config) => {
    if (ziggy !== undefined && ziggy !== null) {
      const isDebug = import.meta.env.VITE_APP_DEBUG === "true";
      const stringifiedZiggy = JSON.stringify(ziggy);
      
      sessionStorage.setItem(
        "ziggyRoute", 
        isDebug ? stringifiedZiggy : btoa(stringifiedZiggy)
      );
      
      ziggyRoute.value = ziggy;
    }
  };

  return {
    ziggyRoute,
    getZiggy,
    setZiggy,
  };
});