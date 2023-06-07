<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import Breadcrumb from "../../base-components/Breadcrumb";
import Lucide from "../../base-components/Lucide";
import { Menu } from "../../base-components/Headless";
import { useUserContextStore } from "../../stores/user-context";
import fakerData from "../../utils/faker";

const selectedCompanyName = ref<string>('');
const selectedBranchName = ref<string>('');

const userContextStore = useUserContextStore();

const userContext = computed(() => userContextStore.userContextValue);

onMounted(async () => {
  selectedCompanyName.value = fakerData[0].companies[0];
  selectedBranchName.value = fakerData[0].branches[0];
});

const userLocation = computed(() => {
  return '';
});

const userCompanyLists = computed(() => {
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
        <Menu.Items class="w-96 h-96 overflow-y-auto" placement="bottom-start">
          <Menu.Item><span class="text-primary">PT ABC - Cabang Pusat</span></Menu.Item>
        </Menu.Items>
      </Menu>
    </Breadcrumb.Text>
    <Breadcrumb.Text>
      {{ userLocation }}
    </Breadcrumb.Text>
  </Breadcrumb>
</template>