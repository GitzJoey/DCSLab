import { defineStore } from "pinia";

export interface SelectedUserLocation {
    company: {
        id: string,
        ulid: string,
        name: string,
    },
    branch: {
        id: string,
        ulid: string,
        name: string,
    }
}

export interface SelectedUserLocationState {
    selectedUserLocation: SelectedUserLocation
}

export const useSelectedUserLocationStore = defineStore("selectedUserLocation", {
    state: (): SelectedUserLocationState => ({
        selectedUserLocation: {
            company: {
                id: '',
                ulid: '',
                name: '',
            },
            branch: {
                id: '',
                ulid: '',
                name: '',
            }
        }
    }),
    getters: {
        getSelectedUserLocation: state => {
            const serializedSelectedUserLocation = sessionStorage.getItem('selectedUserLocation');

            if (serializedSelectedUserLocation) {
                const debug = import.meta.env.VITE_APP_DEBUG === 'true';
                const derializedSelectedUserLocation: SelectedUserLocation = JSON.parse(debug ? serializedSelectedUserLocation : atob(serializedSelectedUserLocation));

                state.selectedUserLocation = derializedSelectedUserLocation;
            }

            return state.selectedUserLocation;
        },
        getSelectedUserCompany: state => {
            const serializedSelectedUserLocation = sessionStorage.getItem('selectedUserLocation');

            if (serializedSelectedUserLocation) {
                const debug = import.meta.env.VITE_APP_DEBUG === 'true';
                const derializedSelectedUserLocation: SelectedUserLocation = JSON.parse(debug ? serializedSelectedUserLocation : atob(serializedSelectedUserLocation));

                state.selectedUserLocation = derializedSelectedUserLocation;
            }

            return state.selectedUserLocation.company;
        },
        getSelectedUserBranch: state => {
            const serializedSelectedUserLocation = sessionStorage.getItem('selectedUserLocation');

            if (serializedSelectedUserLocation) {
                const debug = import.meta.env.VITE_APP_DEBUG === 'true';
                const derializedSelectedUserLocation: SelectedUserLocation = JSON.parse(debug ? serializedSelectedUserLocation : atob(serializedSelectedUserLocation));

                state.selectedUserLocation = derializedSelectedUserLocation;
            }

            return state.selectedUserLocation.branch;
        },
    },
    actions: {
        clearSelectedUserLocation() {
            this.selectedUserLocation.company = { id: '', ulid: '', name: '' };
            this.selectedUserLocation.branch = { id: '', ulid: '', name: '' };
        },
        setSelectedUserLocation(companyId: string, companyUlid: string, companyName: string, branchId: string, branchUlid: string, branchName: string) {
            this.clearSelectedUserLocation();

            this.selectedUserLocation.company.id = companyId;
            this.selectedUserLocation.company.ulid = companyUlid;
            this.selectedUserLocation.company.name = companyName;

            this.selectedUserLocation.branch.id = branchId;
            this.selectedUserLocation.branch.ulid = branchUlid;
            this.selectedUserLocation.branch.name = branchName;

            const debug = import.meta.env.VITE_APP_DEBUG === 'true';
            sessionStorage.setItem('selectedUserLocation', debug ? JSON.stringify(this.selectedUserLocation) : btoa(JSON.stringify(this.selectedUserLocation)));
        }
    },
});
