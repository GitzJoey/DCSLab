import { cva, type VariantProps } from "class-variance-authority";

export const alertRootVariants = cva(
  [
    "flex flex-col gap-1 [&>svg]:size-5 [&>svg]:absolute [&>svg]:my-auto [&>svg]:inset-y-0 [&>svg]:left-5 [&>svg]:stroke-[1.5] has-[>svg]:ps-14 ps-5 pe-20 py-4",
    "rounded-xl shadow-md/5 isolate relative cursor-pointer",
    "before:absolute after:absolute before:z-[-1] after:z-[-1] before:rounded-[inherit] after:rounded-[inherit]",
  ],
  {
    variants: {
      variant: {
        ghost: "after:from-transparent bg-transparent text-foreground",
        primary: "after:from-primary/40 bg-primary text-primary-foreground",
        secondary:
          "after:from-secondary/40 bg-secondary text-secondary-foreground",
        success: "after:from-success/40 bg-success text-success-foreground",
        danger: "after:from-danger/40 bg-danger text-danger-foreground",
        pending: "after:from-pending/40 bg-pending text-pending-foreground",
        warning: "after:from-warning/40 bg-warning text-warning-foreground",
      },
      look: {
        filled: [
          "dark:outline-2 dark:outline-black/30",
          "before:inset-0 before:bg-gradient-to-b before:from-white/40 before:to-white/[.05]",
          "after:inset-[2px] after:bg-gradient-to-b after:to-white/[.08] after:rounded-[calc(var(--radius)*0.9)]",
          "dark:before:from-black/[.5] dark:before:to-black/70",
          "dark:after:from-black/[.3] dark:after:to-black/[.1]",
        ],
        outline:
          "before:bg-none before:border before:rounded-[inherit] before:inset-0 after:hidden bg-background",
        flat: "before:inset-0 dark:before:inset-0 dark:before:bg-black/60",
        text: "before:hidden shadow-none bg-transparent",
      },
    },
    compoundVariants: [
      {
        variant: "ghost",
        look: "flat",
        class: "dark:before:bg-transparent",
      },
      {
        variant: "ghost",
        look: "outline",
        class:
          "before:bg-foreground/10 dark:before:bg-foreground/[.15] before:border-foreground/40 dark:before:border-foreground/20 text-foreground",
      },
      {
        variant: "primary",
        look: "outline",
        class:
          "before:bg-primary/10 dark:before:bg-primary/[.15] before:border-primary/40 dark:before:border-primary/20 text-primary",
      },
      {
        variant: "secondary",
        look: "outline",
        class:
          "before:bg-secondary/10 dark:before:bg-secondary/[.15] before:border-secondary/40 dark:before:border-secondary/20 text-secondary",
      },
      {
        variant: "success",
        look: "outline",
        class:
          "before:bg-success/10 dark:before:bg-success/[.15] before:border-success/40 dark:before:border-success/20 text-success",
      },
      {
        variant: "danger",
        look: "outline",
        class:
          "before:bg-danger/10 dark:before:bg-danger/[.15] before:border-danger/40 dark:before:border-danger/20 text-danger",
      },
      {
        variant: "pending",
        look: "outline",
        class:
          "before:bg-pending/10 dark:before:bg-pending/[.15] before:border-pending/40 dark:before:border-pending/20 text-pending",
      },
      {
        variant: "warning",
        look: "outline",
        class:
          "before:bg-warning/10 dark:before:bg-warning/[.15] before:border-warning/40 dark:before:border-warning/20 text-warning",
      },
      {
        variant: "ghost",
        look: "text",
        class: "text-foreground",
      },
      {
        variant: "primary",
        look: "text",
        class: "text-primary",
      },
      {
        variant: "secondary",
        look: "text",
        class: "text-secondary",
      },
      {
        variant: "success",
        look: "text",
        class: "text-success",
      },
      {
        variant: "danger",
        look: "text",
        class: "text-danger",
      },
      {
        variant: "pending",
        look: "text",
        class: "text-pending",
      },
      {
        variant: "warning",
        look: "text",
        class: "text-warning",
      },
    ],
    defaultVariants: {
      variant: "primary",
      look: "flat",
    },
  }
);

export type AlertRootVariants = {
  look?: VariantProps<typeof alertRootVariants>["look"];
  variant?: VariantProps<typeof alertRootVariants>["variant"];
};

export const alertTitle = "font-medium";
export const alertDescription = "opacity-70";
export const alertCloseTrigger =
  "absolute right-5 inset-y-0 my-auto size-4 [&>svg]:size-full cursor-pointer";
