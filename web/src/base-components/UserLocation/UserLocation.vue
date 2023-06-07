<script setup lang="ts">
import { computed } from "vue";
import Breadcrumb from "../../base-components/Breadcrumb";
import Lucide from "../../base-components/Lucide";
import { Menu } from "../../base-components/Headless";
import { useUserContextStore } from "../../stores/user-context";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const userContextStore = useUserContextStore();

const userContext = computed(() => userContextStore.userContextValue);
const userLocation = computed(() => userContextStore.selectedUserLocationValue);

const userLocationText = computed(() => {
  let result = '';

  if (userLocation.value.company.name != '') {
    result = userLocation.value.company.name;
  }
  if (userLocation.value.branch.name != '') {
    result += ' - ' + userLocation.value.branch.name
  }

  return result;
});

const userLocationLists = computed(() => {
  if (userContext.value.companies !== undefined && userContext.value.companies.length > 0) {
    return userContext.value.companies;
  } else {
    return [];
  }
});
</script>

<template>
  <Breadcrumb light :class="[
    'h-[45px] md:ml-10 md:border-l border-white/[0.08] dark:border-white/[0.08] mr-auto -intro-x md:pl-6'
  ]">
    <Breadcrumb.Text>
      <Menu>
        <Menu.Button variant="primary">
          <Lucide icon="Umbrella" />
        </Menu.Button>
        <Menu.Items :class="{
          'w-96 h-12': userLocationLists.length == 0,
          'w-96 h-48': userLocationLists.length >= 1 && userLocationLists.length <= 10,
          'w-96 h-96': userLocationLists.length > 10,
          'overflow-y-auto': true
        }" placement="bottom-start">
          <template v-if="userLocationLists.length != 0">

          </template>
          <template v-else>
            <Menu.Item><span class="text-primary">{{ t('components.user-location.data_not_found') }}</span></Menu.Item>
          </template>
        </Menu.Items>
      </Menu>
    </Breadcrumb.Text>
    <Breadcrumb.Text v-if="userLocationText != ''">
      {{ userLocationText }}
    </Breadcrumb.Text>
  </Breadcrumb>
</template>