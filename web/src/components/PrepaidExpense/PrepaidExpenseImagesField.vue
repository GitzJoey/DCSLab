<script setup lang="ts">
import { computed, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Dialog from '@/components/Base/Headless/Dialog';
import PrepaidExpenseImageService from '@/services/PrepaidExpenseImageService';
import type { PrepaidExpenseImage } from '@/types/models/PrepaidExpenseImage';
import type { ServiceResponse } from '@/types/services/ServiceResponse';

interface ImageHash {
  hash: string;
  is_main: boolean;
}

const props = withDefaults(
  defineProps<{
    modelValue: ImageHash[];
    existingImages?: PrepaidExpenseImage[];
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
  (e: 'update:existingImages', value: PrepaidExpenseImage[]): void;
  (e: 'update:deleteImageIds', value: Array<number | string>): void;
}>();

const { t } = useI18n();
const prepaidExpenseImageService = new PrepaidExpenseImageService();

const imageHashes = computed({
  get: () => props.modelValue,
  set: (value: ImageHash[]) => emit('update:modelValue', value),
});

const existingImages = computed({
  get: () => props.existingImages ?? [],
  set: (value: PrepaidExpenseImage[]) => emit('update:existingImages', value),
});

const deleteImageIds = computed({
  get: () => props.deleteImageIds ?? [],
  set: (value: Array<number | string>) => emit('update:deleteImageIds', value),
});

const newImages = ref<PrepaidExpenseImage[]>([]);
const isUploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const previewImageUrl = ref<string | null>(null);
const isPreviewOpen = ref(false);

const showCameraModal = ref(false);
const videoRef = ref<HTMLVideoElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const stream = ref<MediaStream | null>(null);

const isImageMain = (image: PrepaidExpenseImage): boolean => {
  const item = imageHashes.value.find((hash) => hash.hash === image.hash);
  return item ? item.is_main : false;
};

const handleUpload = async (file: File) => {
  isUploading.value = true;

  try {
    const uploadResponse: ServiceResponse<PrepaidExpenseImage | null> = await prepaidExpenseImageService.upload(file);

    if (!uploadResponse.success || !uploadResponse.data) {
      return;
    }

    newImages.value = [...newImages.value, uploadResponse.data];

    const hashes = [...imageHashes.value];
    hashes.push({
      hash: uploadResponse.data.hash,
      is_main: hashes.length === 0,
    });

    imageHashes.value = hashes;
  } finally {
    isUploading.value = false;
  }
};

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const files = target.files;

  if (files && files.length > 0) {
    handleUpload(files[0]);
  }

  target.value = '';
};

const onDrop = (event: DragEvent) => {
  const files = event.dataTransfer?.files;

  if (files && files.length > 0) {
    handleUpload(files[0]);
  }
};

const openBrowse = () => {
  fileInput.value?.click();
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

  if (!image) {
    return;
  }

  const ids = [...deleteImageIds.value];
  if (!ids.includes(image.id)) {
    ids.push(image.id);
  }
  deleteImageIds.value = ids;

  images.splice(index, 1);
  existingImages.value = images;

  const hashes = [...imageHashes.value];
  const hashIndex = hashes.findIndex((hash) => hash.hash === image.hash);

  if (hashIndex !== -1) {
    hashes.splice(hashIndex, 1);
  }

  if (hashes.length > 0 && !hashes.some((hash) => hash.is_main)) {
    hashes[0].is_main = true;
  }

  imageHashes.value = hashes;
};

const removeNewImage = (index: number) => {
  const images = [...newImages.value];
  const image = images[index];

  if (!image) {
    return;
  }

  images.splice(index, 1);
  newImages.value = images;

  const hashes = [...imageHashes.value];
  const hashIndex = hashes.findIndex((hash) => hash.hash === image.hash);

  if (hashIndex !== -1) {
    hashes.splice(hashIndex, 1);
  }

  if (hashes.length > 0 && !hashes.some((hash) => hash.is_main)) {
    hashes[0].is_main = true;
  }

  imageHashes.value = hashes;
};

const setMainImage = (image: PrepaidExpenseImage) => {
  imageHashes.value = imageHashes.value.map((hash) => ({
    hash: hash.hash,
    is_main: hash.hash === image.hash,
  }));

  existingImages.value = existingImages.value.map((item) => ({
    ...item,
    is_main: item.hash === image.hash,
  }));

  newImages.value = newImages.value.map((item) => ({
    ...item,
    is_main: item.hash === image.hash,
  }));
};

const startCamera = async () => {
  showCameraModal.value = true;

  try {
    stream.value = await navigator.mediaDevices.getUserMedia({ video: true });

    if (videoRef.value) {
      videoRef.value.srcObject = stream.value;
    }
  } catch (error) {
    console.error('Error accessing camera:', error);
  }
};

const stopCamera = () => {
  if (stream.value) {
    stream.value.getTracks().forEach((track) => track.stop());
    stream.value = null;
  }

  showCameraModal.value = false;
};

const capturePhoto = () => {
  if (!videoRef.value || !canvasRef.value) {
    return;
  }

  const video = videoRef.value;
  const canvas = canvasRef.value;
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;

  const context = canvas.getContext('2d');

  if (!context) {
    return;
  }

  context.drawImage(video, 0, 0, canvas.width, canvas.height);
  canvas.toBlob((blob) => {
    if (!blob) {
      return;
    }

    const file = new File([blob], `prepaid_expense_${Date.now()}.jpg`, { type: 'image/jpeg' });
    handleUpload(file);
    stopCamera();
  }, 'image/jpeg');
};

onUnmounted(() => {
  stopCamera();
});
</script>

<template>
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-4 lg:col-span-3">
      <div class="flex flex-col gap-4">
        <div
          class="border-2 border-dashed border-slate-300 dark:border-darkmode-400 rounded-md p-6 flex flex-col justify-center items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-darkmode-600/50 transition-colors"
          @dragover.prevent
          @drop.prevent="onDrop"
          @click="openBrowse"
        >
          <Lucide icon="UploadCloud" class="w-10 h-10 text-slate-400 mb-2" />
          <div class="text-slate-500 text-center">
            <span class="font-medium text-primary">{{ t('components.file-upload.browse') }}</span>
            {{ t('components.file-upload.or_drag_drop') }}
          </div>
          <div class="text-slate-400 text-xs mt-1">
            JPG, PNG
          </div>
          <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
        </div>

        <div class="flex gap-2">
          <Button type="button" variant="outline-primary" class="w-full" @click="startCamera">
            <Lucide icon="Camera" class="w-4 h-4 mr-2" />
            {{ t('components.file-upload.camera') }}
          </Button>
        </div>

        <div v-if="isUploading" class="flex justify-center p-2">
          <Lucide icon="Loader2" class="w-6 h-6 animate-spin text-primary" />
        </div>
      </div>
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
              :variant="isImageMain(image) ? 'primary' : 'secondary'"
              size="sm"
              class="text-xs px-2 py-1"
              @click.stop="setMainImage(image)"
            >
              {{ isImageMain(image) ? 'Main' : 'Set Main' }}
            </Button>
          </div>

          <div
            v-if="isImageMain(image)"
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
              :variant="isImageMain(image) ? 'primary' : 'secondary'"
              size="sm"
              class="text-xs px-2 py-1"
              @click.stop="setMainImage(image)"
            >
              {{ isImageMain(image) ? 'Main' : 'Set Main' }}
            </Button>
          </div>

          <div
            v-if="isImageMain(image)"
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
        <h2 class="mr-auto text-base font-medium">
          Preview
        </h2>
      </Dialog.Title>
      <Dialog.Description class="p-0">
        <div class="bg-slate-100 dark:bg-darkmode-800 flex items-center justify-center">
          <img v-if="previewImageUrl" :src="previewImageUrl" class="max-h-[70vh] w-auto object-contain" />
        </div>
      </Dialog.Description>
      <Dialog.Footer>
        <Button type="button" variant="outline-secondary" @click="closePreview">
          {{ t('components.buttons.close') }}
        </Button>
      </Dialog.Footer>
    </Dialog.Panel>
  </Dialog>

  <Dialog :open="showCameraModal" @close="stopCamera">
    <Dialog.Panel>
      <Dialog.Title>
        <h2 class="mr-auto text-base font-medium">
          {{ t('components.file-upload.camera') }}
        </h2>
      </Dialog.Title>
      <Dialog.Description>
        <div class="relative w-full aspect-video bg-black rounded overflow-hidden">
          <video ref="videoRef" autoplay playsinline class="w-full h-full object-cover"></video>
          <canvas ref="canvasRef" class="hidden"></canvas>
        </div>
      </Dialog.Description>
      <Dialog.Footer>
        <Button type="button" variant="outline-secondary" class="w-24 mr-1" @click="stopCamera">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="primary" class="w-24" @click="capturePhoto">
          {{ t('components.file-upload.capture') }}
        </Button>
      </Dialog.Footer>
    </Dialog.Panel>
  </Dialog>
</template>
