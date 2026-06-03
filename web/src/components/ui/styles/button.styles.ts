import { cva, type VariantProps } from "class-variance-authority";

export const buttonVariants = cva(
  [
    "shadow-md/5 isolate relative py-2 cursor-pointer inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-3.5 [&_svg]:stroke-2 [&_svg]:shrink-0",
    "before:absolute after:absolute before:z-[-1] after:z-[-1] before:rounded-[inherit] after:rounded-[inherit]",
  ],
  {
    variants: {
      variant: {
        ghost:
          "after:from-transparent bg-transparent hover:bg-foreground/5 text-foreground",
        primary: "after:from-primary/40 bg-primary text-primary-foreground",
        secondary:
          "after:from-secondary/40 bg-secondary text-secondary-foreground",
        success: "after:from-success/40 bg-success text-success-foreground",
        danger: "after:from-danger/40 bg-danger text-danger-foreground",
        pending: "after:from-pending/40 bg-pending text-pending-foreground",
        warning: "after:from-warning/40 bg-warning text-warning-foreground",
      },
      size: {
        sm: "h-9 rounded-(--radius) px-3 text-sm",
        default: "h-10 rounded-(--radius) px-4 text-sm",
        lg: "h-11 rounded-(--radius) px-5 text-base",
        xl: "h-13 rounded-(--radius) px-6 text-base",
      },
      look: {
        filled: [
          "dark:outline-2 dark:outline-black/30",
          "before:inset-0 before:bg-gradient-to-b before:from-white/40 before:to-white/[.05] hover:before:from-white/50",
          "after:inset-[2px] after:bg-gradient-to-b after:to-white/[.08] after:rounded-[calc(var(--radius)*0.7)]",
          "dark:before:from-black/[.5] dark:before:to-black/70 hover:dark:before:from-black/[.4]",
          "dark:after:from-black/[.3] dark:after:to-black/[.1]",
        ],
        outline:
          "before:bg-none before:border before:rounded-[inherit] before:inset-0 after:hidden bg-background",
        flat: "hover:before:bg-white/5 before:inset-0 dark:before:inset-0 dark:before:bg-black/60 hover:dark:before:bg-black/55",
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
          "before:bg-foreground/10 hover:before:bg-foreground/15 dark:before:bg-foreground/[.15] hover:dark:before:bg-foreground/20 before:border-foreground/40 dark:before:border-foreground/20 text-foreground",
      },
      {
        variant: "primary",
        look: "outline",
        class:
          "before:bg-primary/10 hover:before:bg-primary/15 dark:before:bg-primary/[.15] hover:dark:before:bg-primary/20 before:border-primary/40 dark:before:border-primary/20 text-primary",
      },
      {
        variant: "secondary",
        look: "outline",
        class:
          "before:bg-secondary/10 hover:before:bg-secondary/15 dark:before:bg-secondary/[.15] hover:dark:before:bg-secondary/20 before:border-secondary/40 dark:before:border-secondary/20 text-secondary",
      },
      {
        variant: "success",
        look: "outline",
        class:
          "before:bg-success/10 hover:before:bg-success/15 dark:before:bg-success/[.15] hover:dark:before:bg-success/20 before:border-success/40 dark:before:border-success/20 text-success",
      },
      {
        variant: "danger",
        look: "outline",
        class:
          "before:bg-danger/10 hover:before:bg-danger/15 dark:before:bg-danger/[.15] hover:dark:before:bg-danger/20 before:border-danger/40 dark:before:border-danger/20 text-danger",
      },
      {
        variant: "pending",
        look: "outline",
        class:
          "before:bg-pending/10 hover:before:bg-pending/15 dark:before:bg-pending/[.15] hover:dark:before:bg-pending/20 before:border-pending/40 dark:before:border-pending/20 text-pending",
      },
      {
        variant: "warning",
        look: "outline",
        class:
          "before:bg-warning/10 hover:before:bg-warning/15 dark:before:bg-warning/[.15] hover:dark:before:bg-warning/20 before:border-warning/40 dark:before:border-warning/20 text-warning",
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
      size: "default",
      look: "flat",
    },
  }
);

export type ButtonVariants = {
  look?: VariantProps<typeof buttonVariants>["look"];
  variant?: VariantProps<typeof buttonVariants>["variant"];
  size?: VariantProps<typeof buttonVariants>["size"];
};
