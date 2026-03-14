<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FormImageUpload from '@/components/Base/Form/FormImageUpload.vue';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Dialog from '@/components/Base/Headless/Dialog';
import { ProductImage } from '@/types/models/ProductImage';

interface ImageHash {
  hash: string;
  is_thumbnail: boolean;
}

const props = withDefaults(
  defineProps<{
    modelValue: ImageHash[];
    existingImages?: ProductImage[];
    deleteImageIds?: Array<number | string>;
  }>(),
  {
    modelValue: () => [],
    existingImages: () => [],
    deleteImageIds: () => [],
  },
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: ImageHash[]): void;
  (e: 'update:existingImages', value: ProductImage[]): void;
  (e: 'update:deleteImageIds', value: Array<number | string>): void;
}>();

const { t } = useI18n();

const imageHashes = computed({
  get: () => props.modelValue,
  set: (value: ImageHash[]) => emit('update:modelValue', value),
});

const existingImages = computed({
  get: () => props.existingImages ?? [],
  set: (value: ProductImage[]) => emit('update:existingImages', value),
});

const deleteImageIds = computed({
  get: () => props.deleteImageIds ?? [],
  set: (value: Array<number | string>) => emit('update:deleteImageIds', value),
});

const newImages = ref<ProductImage[]>([]);
const previewImageUrl = ref<string | null>(null);
const isPreviewOpen = ref(false);

const isImageThumbnail = (image: ProductImage): boolean => {
  const item = imageHashes.value.find((h) => h.hash === image.hash);
  return item ? item.is_thumbnail : false;
};

const handleImageUploaded = (image: ProductImage) => {
  newImages.value.push(image);

  const hashes = [...imageHashes.value];
  hashes.push({
    hash: image.hash,
    is_thumbnail: hashes.length === 0,
  });

  imageHashes.value = hashes;
};

const openPreview = (url: string | undefined | null) => {
  if (!url) {
    return;
  }

  previewImageUrl.value = url;
  isPreviewOpen.value = true;
};

const closePreview = () => {
  isPreviewOpen.value = false;
  previewImageUrl.value = null;
};

const removeExistingImage = (index: number) => {
  const images = [...existingImages.value];
  const image = images[index];

  if (image) {
    const ids = [...deleteImageIds.value];
    if (!ids.includes(image.id)) {
      ids.push(image.id);
    }
    deleteImageIds.value = ids;

    images.splice(index, 1);
    existingImages.value = images;

    const hashes = [...imageHashes.value];
    const hashIndex = hashes.findIndex((h) => h.hash === image.hash);

    if (hashIndex !== -1) {
      hashes.splice(hashIndex, 1);
    }

    if (hashes.length > 0 && !hashes.some((img) => img.is_thumbnail)) {
      hashes[0].is_thumbnail = true;
    }

    imageHashes.value = hashes;
  }
};

const removeNewImage = (index: number) => {
  const images = [...newImages.value];
  const image = images[index];

  if (image) {
    images.splice(index, 1);
    newImages.value = images;

    const hashes = [...imageHashes.value];
    const hashIndex = hashes.findIndex((h) => h.hash === image.hash);

    if (hashIndex !== -1) {
      hashes.splice(hashIndex, 1);
    }

    if (hashes.length > 0 && !hashes.some((img) => img.is_thumbnail)) {
      hashes[0].is_thumbnail = true;
    }

    imageHashes.value = hashes;
  }
};

const setThumbnail = (image: ProductImage) => {
  const hashes = imageHashes.value.map((img) => ({
    hash: img.hash,
    is_thumbnail: img.hash === image.hash,
  }));

  imageHashes.value = hashes;

  const updatedExisting = existingImages.value.map((img) => ({
    ...img,
    is_thumbnail: img.hash === image.hash,
  }));
  existingImages.value = updatedExisting;

  const updatedNew = newImages.value.map((img) => ({
    ...img,
    is_thumbnail: img.hash === image.hash,
  }));
  newImages.value = updatedNew;
};
</script>

<template>
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-4 lg:col-span-3">
      <FormImageUpload @uploaded="handleImageUploaded" />
    </div>

    <div class="col-span-12 sm:col-span-8 lg:col-span-9">
      <div
        v-if="existingImages.length + newImages.length > 0"
        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"
      >
        <div
          v-for="(image, index) in existingImages"
          :key="`existing-${image.id}`"
          class="relative group border rounded-md overflow-hidden aspect-square bg-slate-100 dark:bg-darkmode-600 cursor-zoom-in"
          @click="openPreview(image.url)"
        >
          <img :src="image.url" class="w-full h-full object-cover" />

          <div
            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center gap-2"
          >
            <Button
              type="button"
              variant="danger"
              size="sm"
              class="w-8 h-8 rounded-full p-0"
              @click.stop="removeExistingImage(index)"
            >
              <Lucide icon="Trash2" class="w-4 h-4" />
            </Button>
            <Button
              type="button"
              :variant="isImageThumbnail(image) ? 'primary' : 'secondary'"
              size="sm"
              class="text-xs px-2 py-1"
              @click.stop="setThumbnail(image)"
            >
              {{ isImageThumbnail(image) ? 'Main' : 'Set Main' }}
            </Button>
          </div>

          <div
            v-if="isImageThumbnail(image)"
            class="absolute top-2 left-2 bg-primary text-white text-xs px-2 py-1 rounded shadow-sm"
          >
            Main
          </div>
        </div>

        <div
          v-for="(image, index) in newImages"
          :key="`new-${image.hash}`"
          class="relative group border rounded-md overflow-hidden aspect-square bg-slate-100 dark:bg-darkmode-600 cursor-zoom-in"
          @click="openPreview(image.url)"
        >
          <img :src="image.url" class="w-full h-full object-cover" />

          <div
            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center gap-2"
          >
            <Button
              type="button"
              variant="danger"
              size="sm"
              class="w-8 h-8 rounded-full p-0"
              @click.stop="removeNewImage(index)"
            >
              <Lucide icon="Trash2" class="w-4 h-4" />
            </Button>

            <Button
              type="button"
              :variant="isImageThumbnail(image) ? 'primary' : 'secondary'"
              size="sm"
              class="text-xs px-2 py-1"
              @click.stop="setThumbnail(image)"
            >
              {{ isImageThumbnail(image) ? 'Main' : 'Set Main' }}
            </Button>
          </div>

          <div
            v-if="isImageThumbnail(image)"
            class="absolute top-2 left-2 bg-primary text-white text-xs px-2 py-1 rounded shadow-sm"
          >
            Main
          </div>
        </div>
      </div>
      <div
        v-else
        class="h-full flex items-center justify-center text-slate-400 border-2 border-dashed rounded-md bg-slate-50 dark:bg-darkmode-600/20 min-h-[150px]"
      >
        {{ t('components.data-list.data_not_found') }}
      </div>
    </div>
  </div>

  <Dialog :open="isPreviewOpen" size="lg" @close="closePreview">
    <Dialog.Panel class="flex flex-col">
      <Dialog.Title>
        <div class="flex items-center justify-between w-full">
          <div class="font-medium">
            {{ t('views.product.fields.images') }}
          </div>
          <Button type="button" variant="outline-secondary" size="sm" @click="closePreview">
            <Lucide icon="X" class="w-4 h-4" />
          </Button>
        </div>
      </Dialog.Title>
      <Dialog.Description class="bg-slate-900 flex items-center justify-center">
        <img
          v-if="previewImageUrl"
          :src="previewImageUrl"
          class="max-h-[80vh] max-w-full object-contain"
        />
      </Dialog.Description>
    </Dialog.Panel>
  </Dialog>
</template>
