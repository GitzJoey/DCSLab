<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import Button from "../Button";
import Lucide from "../Lucide";
import jsBeautify from "js-beautify";
import hljs from "highlight.js";
import { computed, HTMLAttributes, useAttrs, ref, onMounted } from "vue";

interface HighlightProps extends /* @vue-ignore */ HTMLAttributes {
  copyButton?: boolean;
  type?: "html" | "javascript";
}

const props = withDefaults(defineProps<HighlightProps>(), {
  copyButton: true,
  type: "html",
});

const copyText = ref("Copy example code");
const highlightRef = ref<HTMLDivElement>();
const copySourceEl = ref<HTMLTextAreaElement>();
const copySource = ref("");

const attrs = useAttrs();

const buttonComputedClass = computed(() =>
  twMerge(["py-1 px-2", typeof attrs.class === "string" && attrs.class])
);

const highlightComputedClass = computed(() =>
  twMerge([
    "rounded-md overflow-hidden relative",
    props.copyButton && "mt-3",
    !props.copyButton && typeof attrs.class === "string" && attrs.class,
  ])
);

const codePreviewComputedClass = computed(() =>
  twMerge([
    "text-xs leading-relaxed [&.hljs]:bg-slate-50 [&.hljs]:px-5 [&.hljs]:py-4",
    "[&.hljs]:dark:text-slate-200 [&.hljs]:dark:bg-darkmode-700 [&.hljs_.hljs-string]:dark:text-slate-200 [&.hljs_.hljs-tag]:dark:text-slate-200 [&.hljs_.hljs-name]:dark:text-emerald-500 [&.hljs_.hljs-attr]:dark:text-sky-500",
    "before:content-['HTML'] before:font-roboto before:font-medium before:px-4 before:py-2 before:block before:absolute before:top-0 before:right-0 before:rounded-bl before:bg-slate-200 before:bg-opacity-70 before:dark:bg-darkmode-400",
    "[&.javascript]:before:content-['JS']",
    props.type,
  ])
);

const copyCode = () => {
  copyText.value = "Copied!";
  setTimeout(() => {
    copyText.value = "Copy example code";
  }, 1500);

  copySourceEl.value?.select();
  copySourceEl.value?.setSelectionRange(0, 99999);
  document.execCommand("copy");
};

onMounted(() => {
  if (highlightRef.value) {
    const codeEl = highlightRef.value.querySelectorAll("code")[0];
    let source = codeEl.innerHTML;

    // Format for beautify
    source = _.replace(source, /&lt;/g, "<");
    source = _.replace(source, /&gt;/g, ">");

    // Beautify code
    source = jsBeautify.html(source);

    // Save for copy code function
    copySource.value = source;

    // Format for highlight.js
    source = _.replace(source, /</g, "&lt;");
    source = _.replace(source, />/g, "&gt;");

    codeEl.innerHTML = source;

    hljs.highlightElement(codeEl);
  }
});
</script>

<template>
  <div>
    <Button
      v-if="props.copyButton"
      variant="outline-secondary"
      :class="buttonComputedClass"
      v-bind="_.omit(attrs, 'class')"
      @click="
        () => {
          copyCode();
        }
      "
    >
      <Lucide icon="File" class="w-4 h-4 mr-2" /> {{ copyText }}
    </Button>
    <div ref="highlightRef" :class="highlightComputedClass">
      <pre class="relative grid">
        <code :class="codePreviewComputedClass">
          <slot></slot>
        </code>
        <textarea
          ref="copySourceEl"
          :value="copySource"
          class="absolute w-0 h-0 p-0 -mt-1 -ml-1"
        ></textarea>
      </pre>
    </div>
  </div>
</template>
