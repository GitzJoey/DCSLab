<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PurchaseCreateManual from './PurchaseCreateManual.vue';
import PurchaseService from '@/services/PurchaseService';
import { ViewMode } from '@/types/enums/ViewMode';
import type { Purchase } from '@/types/models/Purchase';

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const route = useRoute();
const router = useRouter();

const purchaseService = new PurchaseService();
const ulid = route.params.ulid.toString();
const initialPurchase = ref<Purchase | null>(null);

const loadData = async () => {
  emits('loading-state', true);
  const result = await purchaseService.read(ulid);
  emits('loading-state', false);

  if (!result.success || !result.data) {
    router.push({ name: 'side-menu-purchase-list' });
    return;
  }

  initialPurchase.value = result.data;
};

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);
  await loadData();
});
</script>

<template>
  <PurchaseCreateManual
    v-if="initialPurchase"
    form-mode="edit"
    :ulid="ulid"
    :initial-purchase="initialPurchase"
    @mode-state="emits('mode-state', $event)"
    @loading-state="emits('loading-state', $event)"
    @update-profile="emits('update-profile')"
    @show-alertplaceholder="emits('show-alertplaceholder', $event)"
    @show-notification="emits('show-notification', $event)"
  />
</template>
