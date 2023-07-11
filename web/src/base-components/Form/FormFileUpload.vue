<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, InputHTMLAttributes, useAttrs, inject, ref } from "vue";
import { ProvideFormInline } from "./FormInline.vue";
import { ProvideInputGroup } from "./InputGroup/InputGroup.vue";

interface FormInputProps extends /* @vue-ignore */ InputHTMLAttributes {
  value?: InputHTMLAttributes["value"];
  modelValue?: InputHTMLAttributes["value"];
  formInputSize?: "sm" | "lg";
  rounded?: boolean;
}

interface HTMLInputEvent extends Event {
    target: HTMLInputElement & EventTarget;
}

const props = defineProps<FormInputProps>();
const attrs = useAttrs();
const formInline = inject<ProvideFormInline>("formInline", false);
const inputGroup = inject<ProvideInputGroup>("inputGroup", false);
const imageUrl = ref<string>('')

const isAuto = ref<boolean>(true);

const computedClass = computed(() =>
  twMerge([
    "disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent",
    "[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent",
    "transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80",
    props.formInputSize == "sm" && "text-xs py-1.5 px-2",
    props.formInputSize == "lg" && "text-lg py-1.5 px-4",
    props.rounded && "rounded-full",
    formInline && "flex-1",
    inputGroup &&
      "rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10",
    typeof attrs.class === "string" && attrs.class,
  ])
);

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void,
}>();

const localValue = computed({
  get() {
    return props.modelValue === undefined ? props.value : props.modelValue;

  },
  set(newValue) {
    emit("update:modelValue", newValue);
  },
});

function handleUpload(files : FileList | null) {

}

function handleChangeInput(e: any) {
  let url = ''

  //  Fungsi Upload
  handleUpload(e.target.files)

  url = url ? url : 'https://dummyimage.com/600x400/000/fff&text=Preview'
  imageUrl.value = url
  localValue.value = url

}
</script>

<template>
  <div 
    v-if="imageUrl"
    :class="['flex', 'justify-center' , 'mt-2',  'w-full',  'align-center', {'bg-slate-100' : imageUrl}, 'rounded',  'p-4']"
  >
    <img 
      class="rounded aspect-auto" 
      :src=" imageUrl ? imageUrl : '' " 
      alt="Image Preview"
      >
  </div>
  <div class="flex gap-2 mt-4">
    <input 
    disabled
    :class="computedClass"
    :type="props.type"
    v-bind="_.omit(attrs, 'class')"
    v-model="localValue"
    />
    <input
        id="upload"
        type="file"
        hidden
        @change="(e) => handleChangeInput(e)"
    />
    <label class="border-slate-200 border w-[8%] rounded bg-slate-100 cursor-pointer flex justify-center items-center" for="upload" >Choose File</label>
  </div>
</template>