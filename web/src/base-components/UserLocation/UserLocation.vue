<script setup lang="ts">
import { ref, watch, computed, inject } from "vue";
import { useI18n } from "vue-i18n";
import { useUserContextStore } from "../../stores/user-context";

const { t } = useI18n();

const selectedCompanyName = ref('');
const selectedBranchName = ref('');

const userContextStore = useUserContextStore();

const userContext = computed(() => userContextStore.userContextValue);

const userCompanyLists = computed(() => {
  if (userContext.value.companies !== undefined && userContext.value.companies.length > 0) {
    return userContext.value.companies;
  } else {
    return [];
  }
});

</script>

<template>
    <Dropdown id="company-dropdown" class="intro-x mr-auto sm:mr-6" data-tw-placement="bottom-start">
        <DropdownToggle tag="div" class="cursor-pointer" role="button">
            <div class="flex flex-row">
                <UmbrellaIcon class="dark:text-slate-300 mr-2" />
                <LoadingIcon icon="puff" v-if="selectedCompanyName === ''"/> <div class="text-gray-700 dark:text-slate-300" v-else><strong>{{ selectedCompanyName }} {{ selectedBranchName === '' ? '': '- ' + selectedBranchName }}</strong></div>
            </div>
        </DropdownToggle>
        <DropdownMenu class="w-96">
            <DropdownContent class="overflow-y-auto h-96 dark:bg-dark-6">
                <template v-for="(c, cIdx) in userCompanyLists">
                <DropdownDivider />
                <DropdownHeader :class="{ 'line-through': ['INACTIVE', 'DELETED'].includes(c.status), 'underline': c.default }">{{ c.name }}</DropdownHeader>
                <DropdownDivider />
                </template>
            </DropdownContent>
        </DropdownMenu>
    </Dropdown>
</template>