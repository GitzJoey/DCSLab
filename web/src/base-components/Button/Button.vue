<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { withDefaults, computed, ButtonHTMLAttributes, useAttrs } from "vue";

type Variant =
  | "primary"
  | "secondary"
  | "success"
  | "warning"
  | "pending"
  | "danger"
  | "dark"
  | "outline-primary"
  | "outline-secondary"
  | "outline-success"
  | "outline-warning"
  | "outline-pending"
  | "outline-danger"
  | "outline-dark"
  | "soft-primary"
  | "soft-secondary"
  | "soft-success"
  | "soft-warning"
  | "soft-pending"
  | "soft-danger"
  | "soft-dark"
  | "facebook"
  | "twitter"
  | "instagram"
  | "linkedin";

type Elevated = boolean;
type Size = "sm" | "lg";
type Rounded = boolean;

interface ButtonProps extends ButtonHTMLAttributes {
  as?: string | object;
  variant?: Variant;
  elevated?: Elevated;
  size?: Size;
  rounded?: Rounded;
}

const { as, size, variant, elevated, rounded } = withDefaults(
  defineProps<ButtonProps>(),
  {
    as: "button",
  }
);

const attrs = useAttrs();

// General Styles
const generalStyles = [
  "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer", // Default
  "focus:ring-4 focus:ring-primary focus:ring-opacity-20", // On focus
  "focus-visible:outline-none", // On focus visible
  "dark:focus:ring-slate-700 dark:focus:ring-opacity-50", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90", // On hover and not disabled
  "[&:not(button)]:text-center", // Not a button element
  "disabled:opacity-70 disabled:cursor-not-allowed", // Disabled
];

// Sizes
const small = ["text-xs py-1.5 px-2"];
const large = ["text-lg py-1.5 px-4"];

// Main Colors
const primary = [
  "bg-primary border-primary text-white dark:border-primary", // Default
];
const secondary = [
  "bg-secondary/70 border-secondary/70 text-slate-500", // Default
  "dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300", // Dark mode
  "[&:hover:not(:disabled)]:bg-slate-100 [&:hover:not(:disabled)]:border-slate-100", // On hover and not disabled
  "[&:hover:not(:disabled)]:dark:border-darkmode-300/80 [&:hover:not(:disabled)]:dark:bg-darkmode-300/80", // On hover and not disabled in dark mode
];
const success = [
  "bg-success border-success text-slate-900", // Default
  "dark:border-success", // Dark mode
];
const warning = [
  "bg-warning border-warning text-slate-900", // Default
  "dark:border-warning", // Dark mode
];
const pending = [
  "bg-pending border-pending text-white", // Default
  "dark:border-pending", // Dark mode
];
const danger = [
  "bg-danger border-danger text-white", // Default
  "dark:border-danger", // Dark mode
];
const dark = [
  "bg-dark border-dark text-white", // Default
  "dark:bg-darkmode-800 dark:border-transparent dark:text-slate-300", // Dark mode
  "[&:hover:not(:disabled)]:dark:dark:bg-darkmode-800/70", // On hover and not disabled in dark mode
];

// Social Media
const facebook = [
  "bg-[#3b5998] border-[#3b5998] text-white dark:border-[#3b5998]",
];
const twitter = [
  "bg-[#4ab3f4] border-[#4ab3f4] text-white dark:border-[#4ab3f4]",
];
const instagram = [
  "bg-[#517fa4] border-[#517fa4] text-white dark:border-[#517fa4]",
];
const linkedin = [
  "bg-[#0077b5] border-[#0077b5] text-white dark:border-[#0077b5]",
];

// Outline
const outlinePrimary = [
  "border-primary text-primary", // Default
  "dark:border-primary", // Dark mode
  "[&:hover:not(:disabled)]:bg-primary/10", // On hover and not disabled
];
const outlineSecondary = [
  "border-secondary text-slate-500", // Default
  "dark:border-darkmode-100/40 dark:text-slate-300", // Dark mode
  "[&:hover:not(:disabled)]:bg-secondary/20", // On hover and not disabled
  "[&:hover:not(:disabled)]:dark:bg-darkmode-100/10", // On hover and not disabled in dark mode
];
const outlineSuccess = [
  "border-success text-success", // Default
  "dark:border-success", // Dark mode
  "[&:hover:not(:disabled)]:bg-success/10", // On hover and not disabled
];
const outlineWarning = [
  "border-warning text-warning", // Default
  "dark:border-warning", // Dark mode
  "[&:hover:not(:disabled)]:bg-warning/10", // On hover and not disabled
];
const outlinePending = [
  "border-pending text-pending", // Default
  "dark:border-pending", // Dark mode
  "[&:hover:not(:disabled)]:bg-pending/10", // On hover and not disabled
];
const outlineDanger = [
  "border-danger text-danger", // Default
  "dark:border-danger", // Dark mode
  "[&:hover:not(:disabled)]:bg-danger/10", // On hover and not disabled
];
const outlineDark = [
  "border-dark text-dark", // Default
  "dark:border-darkmode-800 dark:text-slate-300", // Dark mode
  "[&:hover:not(:disabled)]:bg-darkmode-800/30", // On hover and not disabled
  "[&:hover:not(:disabled)]:dark:bg-opacity-30", // On hover and not disabled in dark mode
];

// Soft Color
const softPrimary = [
  "bg-primary border-primary bg-opacity-20 border-opacity-5 text-primary", // Default
  "dark:border-opacity-100 dark:bg-opacity-20 dark:border-primary", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10 [&:hover:not(:disabled)]:border-opacity-10", // On hover and not disabled
  "[&:hover:not(:disabled)]:dark:border-opacity-60", // On hover and not disabled in dark mode
];
const softSecondary = [
  "bg-slate-300 border-secondary bg-opacity-20 text-slate-500", // Default
  "dark:bg-darkmode-100/20 dark:border-darkmode-100/30 dark:text-slate-300", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10", // On hover and not disabled
  "[&:hover:not(:disabled)]:dark:bg-darkmode-100/10 [&:hover:not(:disabled)]:dark:border-darkmode-100/20", // On hover and not disabled in dark mode
];
const softSuccess = [
  "bg-success border-success bg-opacity-20 border-opacity-5 text-success", // Default
  "dark:border-success dark:border-opacity-20", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10 [&:hover:not(:disabled)]:border-opacity-10", // On hover and not disabled
];
const softWarning = [
  "bg-warning border-warning bg-opacity-20 border-opacity-5 text-warning", // Default
  "dark:border-warning dark:border-opacity-20", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10 [&:hover:not(:disabled)]:border-opacity-10", // On hover and not disabled
];
const softPending = [
  "bg-pending border-pending bg-opacity-20 border-opacity-5 text-pending", // Default
  "dark:border-pending dark:border-opacity-20", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10 [&:hover:not(:disabled)]:border-opacity-10", // On hover and not disabled
];
const softDanger = [
  "bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger", // Default
  "dark:border-danger dark:border-opacity-20", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10 [&:hover:not(:disabled)]:border-opacity-10", // On hover and not disabled
];
const softDark = [
  "bg-dark border-dark bg-opacity-20 border-opacity-5 text-dark", // Default
  "dark:bg-darkmode-800/30 dark:border-darkmode-800/60 dark:text-slate-300", // Dark mode
  "[&:hover:not(:disabled)]:bg-opacity-10 [&:hover:not(:disabled)]:border-opacity-10", // On hover and not disabled
  "[&:hover:not(:disabled)]:dark:bg-darkmode-800/50 [&:hover:not(:disabled)]:dark:border-darkmode-800", // On hover and not disabled in dark mode
];

const computedClass = computed(() =>
  twMerge([
    generalStyles,
    size == "sm" && small,
    size == "lg" && large,
    variant == "primary" && primary,
    variant == "secondary" && secondary,
    variant == "success" && success,
    variant == "warning" && warning,
    variant == "pending" && pending,
    variant == "danger" && danger,
    variant == "dark" && dark,
    variant == "outline-primary" && outlinePrimary,
    variant == "outline-secondary" && outlineSecondary,
    variant == "outline-success" && outlineSuccess,
    variant == "outline-warning" && outlineWarning,
    variant == "outline-pending" && outlinePending,
    variant == "outline-danger" && outlineDanger,
    variant == "outline-dark" && outlineDark,
    variant == "soft-primary" && softPrimary,
    variant == "soft-secondary" && softSecondary,
    variant == "soft-success" && softSuccess,
    variant == "soft-warning" && softWarning,
    variant == "soft-pending" && softPending,
    variant == "soft-danger" && softDanger,
    variant == "soft-dark" && softDark,
    variant == "facebook" && facebook,
    variant == "twitter" && twitter,
    variant == "instagram" && instagram,
    variant == "linkedin" && linkedin,
    rounded && "rounded-full",
    elevated && "shadow-md",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <component :is="as" :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </component>
</template>
