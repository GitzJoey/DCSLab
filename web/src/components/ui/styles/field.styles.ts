import { cva, type VariantProps } from "class-variance-authority";

// Styles
export const fieldVariants = cva(
  "data-[invalid=true]:text-danger gap-2 group/field flex w-full",
  {
    variants: {
      orientation: {
        vertical: "flex-col *:w-full [&>.sr-only]:w-auto",
        horizontal:
          "flex-row items-center *:data-[part=field-label]:flex-auto has-[>[data-part=field-content]]:items-start has-[>[data-part=field-content]]:[&>[role=checkbox],[role=radio]]:mt-px",
        responsive:
          "flex-col *:w-full [&>.sr-only]:w-auto @md/field-group:flex-row @md/field-group:items-center @md/field-group:*:w-auto @md/field-group:*:data-[part=field-label]:flex-auto @md/field-group:has-[>[data-part=field-content]]:items-start @md/field-group:has-[>[data-part=field-content]]:[&>[role=checkbox],[role=radio]]:mt-px",
      },
    },
    defaultVariants: {
      orientation: "vertical",
    },
  }
);
export const fieldContent =
  "gap-0.5 group/field-content flex flex-1 flex-col leading-snug";
export const fieldDescription = [
  "text-foreground/70 text-left text-sm [[data-variant=legend]+&]:-mt-1.5 leading-normal font-normal group-has-data-horizontal/field:text-balance",
  "last:mt-0 nth-last-2:-mt-1",
  "[&>a:hover]:text-primary [&>a]:underline [&>a]:underline-offset-4",
];
export const fieldError =
  "text-danger text-sm font-normal [&>ul]:ml-4 [&>ul]:flex [&>ul]:list-disc [&>ul]:flex-col [&>ul]:gap-1";
export const fieldGroup =
  "gap-5 data-[part=checkbox-group]:gap-3 *:data-[part=field-group]:gap-4 group/field-group @container/field-group flex w-full flex-col";
export const fieldLabel = [
  "has-data-[state='checked']:bg-foreground/5 has-data-[state='checked']:border-foreground/30 dark:has-data-[state='checked']:border-foreground/20 dark:has-data-[state='checked']:bg-foreground/10 gap-2 group-data-[disabled=true]/field:opacity-50 has-[>[data-part=field]]:rounded-(--radius) has-[>[data-part=field]]:border has-[>[data-part=field]]:border-foreground/15 *:data-[part=field]:p-3.5 group/field-label peer/field-label flex w-fit leading-snug",
  "has-[>[data-part=field]]:w-full has-[>[data-part=field]]:flex-col",
];
export const fieldLegend =
  "mb-1.5 font-medium data-[variant=label]:text-sm data-[variant=legend]:text-base";
export const fieldSeparator = [
  "-my-2 h-5 text-sm group-data-[variant=outline]/field-group:-mb-2 relative",
  "[&>[data-part='separator']]:absolute [&>[data-part='separator']]:inset-0 [&>[data-part='separator']]:top-1/2",
  "[&>[data-part='field-separator-content']]:text-foreground/70 [&>[data-part='field-separator-content']]:px-2 [&>[data-part='field-separator-content']]:bg-background [&>[data-part='field-separator-content']]:relative [&>[data-part='field-separator-content']]:mx-auto [&>[data-part='field-separator-content']]:block [&>[data-part='field-separator-content']]:w-fit",
];
export const fieldSet =
  "gap-4 has-[>[data-part=checkbox-group]]:gap-3 has-[>[data-part=radio-group]]:gap-3 flex flex-col";
export const fieldTitle =
  "gap-2 text-sm font-medium group-data-[disabled=true]/field:opacity-50 flex w-fit items-center leading-snug";

// Types
export type FieldVariants = {
  orientation?: VariantProps<typeof fieldVariants>["orientation"];
};
