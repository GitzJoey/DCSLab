import { cva, type VariantProps } from "class-variance-authority";

// Styles
export const tableContainer = "relative w-full overflow-x-auto pe-2 -me-2";
export const tableVariants = cva("w-full caption-bottom", {
  variants: {
    variant: {
      default: "",
      boxed: "border-separate border-spacing-y-2.5",
    },
    raised: {
      single: "border-spacing-y-5",
      double: "border-spacing-y-7",
    },
  },
  defaultVariants: {
    variant: "default",
  },
});
export const tableHeader = "[&_tr]:border-b";
export const tableBody =
  "[&_tr:last-child]:border-0 [&_tr:hover]:bg-foreground/5";
export const tableFooter =
  "bg-foreground/5 border-t border-foreground/10 font-medium [&>tr]:last:border-b-0";
export const tableHead =
  "text-foreground h-11 px-4 text-left align-middle font-medium whitespace-nowrap [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]";
export const tableRow =
  "data-[state=selected]:bg-foreground/5 border-b border-foreground/10 transition-colors";
export const tableCellVariants = cva(
  "px-4 py-3 align-middle whitespace-nowrap [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]",
  {
    variants: {
      variant: {
        default: "",
        boxed: [
          "relative bg-background bg-gradient-to-b from-transparent to-foreground/[.03] dark:to-foreground/5 border-y first:border-s last:border-e border-foreground/10 first:rounded-s-xl last:rounded-e-xl shadow-md/5",
        ],
      },
      raised: {
        single:
          "before:absolute before:inset-x-0 first:before:start-2 last:before:end-2 before:h-2.5 before:bg-background/20 dark:before:bg-foreground/10 before:-bottom-2.5 first:before:rounded-bl-xl last:before:rounded-br-xl first:before:border-s last:before:border-e before:border-b before:border-foreground/10 before:z-[-1] before:shadow-md/5 before:opacity-60",
        double: [
          "before:absolute before:inset-x-0 first:before:start-2 last:before:end-2 before:h-2.5 before:bg-background/20 dark:before:bg-foreground/10 before:-bottom-2.5 first:before:rounded-bl-xl last:before:rounded-br-xl first:before:border-s last:before:border-e before:border-b before:border-foreground/10 before:z-[-1] before:shadow-md/5 before:opacity-60",
          "after:absolute after:inset-x-0 first:after:start-4.5 last:after:end-4.5 after:h-[0.5rem] after:bg-background/20 dark:after:bg-foreground/10 after:-bottom-[1.1rem] first:after:rounded-bl-xl last:after:rounded-br-xl first:after:border-s last:after:border-e after:border-b after:border-foreground/10 after:z-[-1] after:shadow-md/5 after:opacity-40",
        ],
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);
export const tableCaption = "text-foreground/70 mt-4 text-sm";

// Types
export type TableVariants = {
  variant?: VariantProps<typeof tableVariants>["variant"];
  raised?: VariantProps<typeof tableVariants>["raised"];
};
export type TableCellVariants = {
  variant?: VariantProps<typeof tableCellVariants>["variant"];
  raised?: VariantProps<typeof tableCellVariants>["raised"];
};
