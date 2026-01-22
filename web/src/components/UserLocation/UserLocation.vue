<script setup lang="ts">
import { onMounted, computed, toRef } from "vue";
import Breadcrumb from "@/components/Base/Breadcrumb";
import Lucide from "@/components/Base/Lucide";
import { Menu } from "@/components/Base/Headless";
import { useUserContextStore } from "@/stores/user-context";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { useI18n } from "vue-i18n";
import _ from "lodash";
import { twMerge } from "tailwind-merge";

interface UserLocationProps {
  visible: boolean,
  theme?: 'rubick' | 'icewall' | 'enigma' | 'tinker',
  layout?: "side-menu" | "simple-menu" | "top-menu",
}

const props = withDefaults(defineProps<UserLocationProps>(), {
  visible: true,
  theme: 'rubick',
  layout: 'side-menu',
});

const visible = toRef(props, 'visible');
const theme = toRef(props, 'theme');
const layout = toRef(props, 'layout');

const { t } = useI18n();

const userContextStore = useUserContextStore();
const selectedUserLocationStore = useSelectedUserLocationStore();

const userContext = computed(() => userContextStore.userContext);
const userLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

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

const computedClass = computed(() =>
  twMerge([
    theme.value == 'rubick' && (layout.value == 'side-menu' || layout.value == 'simple-menu') && 'hidden mr-auto -intro-x sm:flex',
    theme.value == 'rubick' && layout.value == 'top-menu' && 'h-full md:ml-10 md:pl-10 md:border-l border-white/[0.08] mr-auto -intro-x',

    theme.value == 'icewall' && 'h-full md:ml-10 md:pl-10 md:border-l border-white/[0.08] mr-auto -intro-x',

    theme.value == 'enigma' && 'h-full md:ml-10 md:pl-10 md:border-l border-white/[0.08] mr-auto -intro-x',

    theme.value == 'tinker' && (layout.value == 'side-menu' || layout.value == 'simple-menu') && 'hidden mr-auto -intro-x sm:flex',
    theme.value == 'tinker' && layout.value == 'top-menu' && 'h-full md:ml-10 md:pl-10 md:border-l border-white/[0.08] mr-auto -intro-x'
  ])
);

const computedLight = computed(() => {
  switch (true) {
    case theme.value == 'rubick' && (layout.value == 'side-menu' || layout.value == 'simple-menu'):
      return false;
    case theme.value == 'rubick' && layout.value == 'top-menu':
      return true;

    case theme.value == 'icewall':
      return true;

    case theme.value == 'enigma':
      return true;

    case theme.value == 'tinker' && (layout.value == 'side-menu' || layout.value == 'simple-menu'):
      return false;
    case theme.value == 'tinker' && layout.value == 'top-menu':
      return true;

    default:
      return false;
  }
});

onMounted(() => {
  selectedUserLocationStore.getSelectedUserLocation;
});

const setNewUserLocation = (companyId: string, branchId: string) => {
  let company = _.find(userContext.value.companies, { id: companyId });

  if (!company) return;

  let branch = branchId == '' ? _.find(company.branches, { is_main: true }) : _.find(company.branches, { id: branchId });

  if (branch) {
    selectedUserLocationStore.clearSelectedUserLocation();
    selectedUserLocationStore.setSelectedUserLocation(company.id, company.ulid, company.code, company.name, branch.id, branch.ulid, branch.code, branch.name);
  } else {
    selectedUserLocationStore.clearSelectedUserLocation();
    selectedUserLocationStore.setSelectedUserLocation(company.id, company.ulid, company.code, company.name);
  }
};
</script>

<template>
  <Breadcrumb :light="computedLight" :class="computedClass">
    <Breadcrumb.Text>
      <Menu>
        <Menu.Button variant="primary">
          <Lucide icon="Umbrella" />
        </Menu.Button>
        <Menu.Items :class="{
          'w-96 h-12': userLocationLength == 0,
          'w-96 h-48': userLocationLength >= 1 && userLocationLength <= 10,
          'w-96 h-96': userLocationLength && userLocationLength > 10,
          'overflow-y-auto': true
        }" placement="bottom-start">
          <template v-if="userContext.companies && userContext.companies.length != 0">
            <template v-for="(c, cIdx) in userContext.companies" :key="cIdx">
              <Menu.Item @click="setNewUserLocation(c.id, '')" class="relative">
                <span :class="{ 'text-primary font-bold': true, 'underline': c.default }">{{ c.name }}</span>
              </Menu.Item>
              <Menu.Item v-for="(b, bIdx) in c.branches" :key="bIdx"
                @click="setNewUserLocation(c.id, b == null ? '' : b.id)" class="pl-6 relative">
                <div class="absolute left-3 top-0 h-full w-px bg-gray-200"></div>
                <div class="absolute left-3 top-1/2 h-px w-3 bg-gray-200"></div>
                <span v-if="b != null" class="pl-1" :class="{ 'text-primary': true, 'underline': b.is_main }">{{
                  b.name }}</span>
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