<script lang="ts">
  export default {
    inheritAttrs: false,
  };
</script>

<script setup lang="ts">
  import _ from 'lodash';
  import { twMerge } from 'tailwind-merge';
  import { computed, InputHTMLAttributes, useAttrs, inject, ref, onMounted, onUnmounted } from 'vue';
  import { ProvideFormInline } from './FormInline.vue';
  import { ProvideInputGroup } from './InputGroup/InputGroup.vue';
  import ProductImageService from '@/services/ProductImageService';
  import { useI18n } from 'vue-i18n';
  import { ProductImage } from '@/types/models/ProductImage';
  import { ServiceResponse } from '@/types/services/ServiceResponse';
  import Lucide from '@/components/Base/Lucide';
  import Button from '@/components/Base/Button';
  import { Dialog } from '@/components/Base/Headless';

  export interface FormInputProps extends /* @vue-ignore */ InputHTMLAttributes {
    value?: InputHTMLAttributes['value'];
    modelValue?: InputHTMLAttributes['value'];
    formInputSize?: 'sm' | 'lg';
    rounded?: boolean;
  }

  interface HTMLInputEvent extends Event {
    target: HTMLInputElement & EventTarget;
  }

  const { t } = useI18n();
  const props = defineProps<FormInputProps>();
  const attrs = useAttrs();
  const formInline = inject<ProvideFormInline>('formInline', false);
  const inputGroup = inject<ProvideInputGroup>('inputGroup', false);
  
  const productImageService = new ProductImageService();
  const isUploading = ref(false);
  const fileInput = ref<HTMLInputElement | null>(null);

  // Camera refs
  const showCameraModal = ref(false);
  const videoRef = ref<HTMLVideoElement | null>(null);
  const canvasRef = ref<HTMLCanvasElement | null>(null);
  const stream = ref<MediaStream | null>(null);

  const computedClass = computed(() =>
    twMerge([
      'disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent',
      '[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent',
      'transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80',
      props.formInputSize == 'sm' && 'text-xs py-1.5 px-2',
      props.formInputSize == 'lg' && 'text-lg py-1.5 px-4',
      props.rounded && 'rounded-full',
      formInline && 'flex-1',
      inputGroup && 'rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10',
      typeof attrs.class === 'string' && attrs.class,
    ]),
  );

  const emit = defineEmits<{
    (e: 'uploaded', value: ProductImage): void;
  }>();

  const handleUpload = async (file: File) => {
    isUploading.value = true;
    try {
      let uploadResponse: ServiceResponse<ProductImage | null> = await productImageService.upload(file);

      if (uploadResponse.success && uploadResponse.data) {
        emit('uploaded', uploadResponse.data);
      }
    } finally {
      isUploading.value = false;
    }
  };

  const onFileChange = (event: Event) => {
    const _event = event as HTMLInputEvent;
    const files = _event.target.files;
    if (files && files.length > 0) {
      handleUpload(files[0]);
    }
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

  // Camera Functions
  const startCamera = async () => {
    showCameraModal.value = true;
    try {
      stream.value = await navigator.mediaDevices.getUserMedia({ video: true });
      if (videoRef.value) {
        videoRef.value.srcObject = stream.value;
      }
    } catch (err) {
      console.error("Error accessing camera:", err);
      // Handle permission errors or no camera found
    }
  };

  const stopCamera = () => {
    if (stream.value) {
      stream.value.getTracks().forEach(track => track.stop());
      stream.value = null;
    }
    showCameraModal.value = false;
  };

  const capturePhoto = () => {
    if (videoRef.value && canvasRef.value) {
      const video = videoRef.value;
      const canvas = canvasRef.value;
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const context = canvas.getContext('2d');
      if (context) {
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => {
          if (blob) {
            const file = new File([blob], `camera_${Date.now()}.jpg`, { type: 'image/jpeg' });
            handleUpload(file);
            stopCamera();
          }
        }, 'image/jpeg');
      }
    }
  };

  onUnmounted(() => {
    stopCamera();
  });
</script>

<template>
  <div class="flex flex-col gap-4">
    <!-- Dropzone Area -->
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
      <input 
        ref="fileInput"
        type="file" 
        accept="image/*" 
        class="hidden" 
        @change="onFileChange" 
      />
    </div>

    <!-- Actions Row -->
    <div class="flex gap-2">
      <Button type="button" variant="outline-primary" class="w-full" @click="startCamera">
        <Lucide icon="Camera" class="w-4 h-4 mr-2" />
        {{ t('components.file-upload.camera') }}
      </Button>
    </div>

    <!-- Loading State -->
    <div v-if="isUploading" class="flex justify-center p-2">
        <Lucide icon="Loader2" class="w-6 h-6 animate-spin text-primary" />
    </div>

    <!-- Camera Modal -->
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
          <Button type="button" variant="outline-secondary" @click="stopCamera" class="w-24 mr-1">
            {{ t('components.buttons.cancel') }}
          </Button>
          <Button type="button" variant="primary" @click="capturePhoto" class="w-24">
            {{ t('components.file-upload.capture') }}
          </Button>
        </Dialog.Footer>
      </Dialog.Panel>
    </Dialog>
  </div>
</template>
