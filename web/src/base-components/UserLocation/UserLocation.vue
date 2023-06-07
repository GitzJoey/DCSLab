<script setup lang="ts">
import { computed } from "vue";
import Breadcrumb from "../../base-components/Breadcrumb";
import Lucide from "../../base-components/Lucide";
import { Menu } from "../../base-components/Headless";
import { useUserContextStore } from "../../stores/user-context";
import { useI18n } from "vue-i18n";
import _ from "lodash";

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

const userLocationLength = computed((): number => {
  let result = 0;

  userContext.value.companies.forEach(c => {
    result += 1;
    if (c.branches) {
      result += c.branches.length;
    }
  });

  return result;
});

const setNewUserLocation = (companyId: string, branchId: string) => {
  let company = _.find(userContext.value.companies, { id: companyId });

  if (!company) return;

  let branch = branchId == '' ? _.find(company.branches, { is_main: true }) : _.find(company.branches, { id: branchId });

  if (branch) {
    userContextStore.setSelectedUserLocation(company.id, company.ulid, company.name, branch.id, branch.ulid, branch.name);
  }
}
</script>

<template>
  <Breadcrumb light :class="[
    'h-[45px] md:ml-10 md:border-l border-white/[0.08] dark:border-white/[0.08] mr-auto -intro-x md:pl-6']">
    <Breadcrumb.Text>
      <Menu>
        <Menu.Button var iant="primary">
          <Lucide icon="Umbrella" />
        </Menu.Button>
        <Menu.Items :class="{
          'w-96 h-12': userLocationLength == 0,
          'w-96 h-48': userLocationLength >= 1 && userLocationLength <= 10,
          'w-96 h-96': userLocationLength && userLocationLength > 10,
          'overflow-y-auto': true
        }" placement="bottom-start">
          <template v-if="userContext.companies && userContext.companies.length != 0">
            <template v-for="(c, cIdx) in  userContext.companies" :key="cIdx">
              <Menu.Item @click="setNewUserLocation(c.id, '')">
                <span :class="{ 'text-primary font-bold': true, 'underline': c.default }">{{ c.name }}</span>
              </Menu.Item>
              <Menu.Item v-for="(b, bIdx) in c.branches" :key="bIdx" @click="setNewUserLocation(c.id, b.id)">
                <span :class="{ 'text-primary': true, 'underline': b.is_main }">{{ b.name }}</span>
              </Menu.Item>
              <Menu.Divider v-if="userContext.companies.length - 1 != cIdx" />
            </template>
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