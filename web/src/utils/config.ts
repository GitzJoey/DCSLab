export interface AppConfig {
  VITE_BACKEND_URL: string;
}

declare global {
  interface Window {
    APP_CONFIG?: AppConfig;
  }
}

export const getBackendUrl = (): string => {
  if (typeof window !== 'undefined' && window.APP_CONFIG && window.APP_CONFIG.VITE_BACKEND_URL) {
    return window.APP_CONFIG.VITE_BACKEND_URL;
  }
  return import.meta.env.VITE_BACKEND_URL;
};
