import { defineStore } from 'pinia';

export interface SelectedUserLocation {
  company: {
    id: string;
    ulid: string;
    code: string;
    name: string;
  };
  branch: {
    id: string;
    ulid: string;
    code: string;
    name: string;
  };
}

export interface SelectedUserLocationState {
  isUserLocationSelected: boolean;
  selectedUserLocation: SelectedUserLocation;
}

const SELECTED_USER_LOCATION_STORAGE_KEY = 'selectedUserLocation';

const createEmptySelectedUserLocation = (): SelectedUserLocation => ({
  company: {
    id: '',
    ulid: '',
    code: '',
    name: '',
  },
  branch: {
    id: '',
    ulid: '',
    code: '',
    name: '',
  },
});

const isDebugMode = (): boolean => import.meta.env.VITE_APP_DEBUG === 'true';

const serializeSelectedUserLocation = (selectedUserLocation: SelectedUserLocation): string => {
  const serializedSelectedUserLocation = JSON.stringify(selectedUserLocation);

  return isDebugMode() ? serializedSelectedUserLocation : btoa(serializedSelectedUserLocation);
};

const deserializeSelectedUserLocation = (serializedSelectedUserLocation: string): SelectedUserLocation => {
  return JSON.parse(isDebugMode() ? serializedSelectedUserLocation : atob(serializedSelectedUserLocation));
};

const clearStoredSelectedUserLocation = (): void => {
  localStorage.removeItem(SELECTED_USER_LOCATION_STORAGE_KEY);
  sessionStorage.removeItem(SELECTED_USER_LOCATION_STORAGE_KEY);
};

const getStoredSelectedUserLocation = (): SelectedUserLocation | null => {
  const serializedSelectedUserLocation =
    localStorage.getItem(SELECTED_USER_LOCATION_STORAGE_KEY) ??
    sessionStorage.getItem(SELECTED_USER_LOCATION_STORAGE_KEY);

  if (!serializedSelectedUserLocation) {
    return null;
  }

  try {
    const selectedUserLocation = deserializeSelectedUserLocation(serializedSelectedUserLocation);

    // Migrate legacy session storage selection to local storage for new tabs.
    localStorage.setItem(
      SELECTED_USER_LOCATION_STORAGE_KEY,
      serializeSelectedUserLocation(selectedUserLocation),
    );
    sessionStorage.removeItem(SELECTED_USER_LOCATION_STORAGE_KEY);

    return selectedUserLocation;
  } catch (_error) {
    clearStoredSelectedUserLocation();

    return null;
  }
};

const initialSelectedUserLocation = getStoredSelectedUserLocation();

export const useSelectedUserLocationStore = defineStore('selectedUserLocation', {
  state: (): SelectedUserLocationState => ({
    isUserLocationSelected: initialSelectedUserLocation !== null,
    selectedUserLocation: initialSelectedUserLocation ?? createEmptySelectedUserLocation(),
  }),
  getters: {
    getSelectedUserLocation: (state) => state.selectedUserLocation,
    getSelectedUserCompany: (state) => state.selectedUserLocation.company,
    getSelectedUserBranch: (state) => state.selectedUserLocation.branch,
  },
  actions: {
    clearSelectedUserLocation() {
      this.selectedUserLocation = createEmptySelectedUserLocation();
      this.isUserLocationSelected = false;

      clearStoredSelectedUserLocation();
    },
    setSelectedUserLocation(
      companyId: string,
      companyUlid: string,
      companyCode: string,
      companyName: string,
      branchId?: string,
      branchUlid?: string,
      branchCode?: string,
      branchName?: string,
    ) {
      this.clearSelectedUserLocation();

      this.selectedUserLocation.company.id = companyId;
      this.selectedUserLocation.company.ulid = companyUlid;
      this.selectedUserLocation.company.code = companyCode;
      this.selectedUserLocation.company.name = companyName;

      if (branchId) this.selectedUserLocation.branch.id = branchId;

      if (branchUlid) this.selectedUserLocation.branch.ulid = branchUlid;

      if (branchCode) this.selectedUserLocation.branch.code = branchCode;

      if (branchName) this.selectedUserLocation.branch.name = branchName;

      localStorage.setItem(
        SELECTED_USER_LOCATION_STORAGE_KEY,
        serializeSelectedUserLocation(this.selectedUserLocation),
      );
      sessionStorage.removeItem(SELECTED_USER_LOCATION_STORAGE_KEY);

      this.isUserLocationSelected = true;

      window.location.reload();
    },
  },
});
