import { cva, type VariantProps } from "class-variance-authority";

// Styles
export const accordionRootVariants = cva("flex flex-col", {
  variants: {
    variant: {
      default: "-mt-4 -mb-3",
      boxed: "gap-3",
    },
  },
  defaultVariants: {
    variant: "default",
  },
});
export const accordionItemVariants = cva("group", {
  variants: {
    variant: {
      default: "border-b border-b-foreground/10 last:border-b-transparent",
      boxed: "py-0 px-4",
    },
  },
  defaultVariants: {
    variant: "default",
  },
});
export const accordionTrigger =
  "group-hover:underline cursor-pointer w-full flex items-center font-medium py-4";
export const accordionItemIndicator =
  "ms-auto opacity-70 [&>svg]:size-4 data-[state=open]:rotate-180 transition";
export const accordionContent =
  "-mt-1 pb-4 opacity-80 data-[state=open]:animate-in data-[state=open]:zoom-in-95 data-[state=open]:fade-in-0";

// Types
export type AccordionRootVariants = {
  variant?: VariantProps<typeof accordionRootVariants>["variant"];
};
export type AccordionItemVariants = {
  variant?: VariantProps<typeof accordionItemVariants>["variant"];
};
