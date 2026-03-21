<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';

type Props = {
  imageUrl?: string | null;
  wrapperClass?: string;
  imageClass?: string;
  iconClass?: string;
  previewTitle?: string;
};

const props = withDefaults(defineProps<Props>(), {
  imageUrl: null,
  wrapperClass:
    'w-14 h-14 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in',
  imageClass: 'w-full h-full object-cover',
  iconClass: 'w-6 h-6 text-slate-400',
  previewTitle: '',
});

const { t } = useI18n();

const isPreviewOpen = ref(false);

const hasImage = computed(() => !!props.imageUrl);
const resolvedPreviewTitle = computed(() => props.previewTitle || t('views.product.fields.images'));

const openPreview = () => {
  if (!hasImage.value) return;
  isPreviewOpen.value = true;
};

const closePreview = () => {
  isPreviewOpen.value = false;
};
</script>

<template>
  <div :class="wrapperClass" @click="openPreview">
    <img v-if="hasImage" :src="imageUrl || ''" :class="imageClass" />
    <Lucide v-else icon="ImageOff" :class="iconClass" />
  </div>
  <Dialog :open="isPreviewOpen" size="lg" @close="closePreview">
    <Dialog.Panel class="flex flex-col">
      <Dialog.Title>
        <div class="flex items-center justify-between w-full">
          <div class="font-medium">
            {{ resolvedPreviewTitle }}
          </div>
          <Button type="button" variant="outline-secondary" size="sm" @click="closePreview">
            <Lucide icon="X" class="w-4 h-4" />
          </Button>
        </div>
      </Dialog.Title>
      <Dialog.Description class="bg-slate-900 flex items-center justify-center">
        <img v-if="hasImage" :src="imageUrl || ''" class="max-h-[80vh] max-w-full object-contain" />
      </Dialog.Description>
    </Dialog.Panel>
  </Dialog>
</template>
