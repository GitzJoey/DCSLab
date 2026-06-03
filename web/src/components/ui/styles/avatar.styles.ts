import { cva, type VariantProps } from "class-variance-authority";

// Styles
export const avatarRootVariants = cva(
  "size-14 rounded-xl overflow-hidden relative bg-foreground/5 flex items-center justify-center border-3 ring-1 border-transparent",
  {
    variants: {
      bordered: {
        true: "ring-foreground/20",
        false: "ring-transparent border-none",
      },
    },
    defaultVariants: {
      bordered: true,
    },
  }
);
export const avatarFallback = "font-medium";
export const avatarImage = "absolute top-0 size-full object-cover";

// Types
export type AvatarRootVariants = {
  bordered?: VariantProps<typeof avatarRootVariants>["bordered"];
};
