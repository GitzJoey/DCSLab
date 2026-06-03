export const comboboxRoot =
  "w-full flex flex-col gap-2.5 [&[data-multiple=true]_[data-part=clear-trigger]]:block";
export const comboboxLabel = "";
export const comboboxControl = "relative";
export const comboboxInput = "flex-none";
export const comboboxTrigger =
  "font-normal text-foreground/70 w-full border border-foreground/15 bg-background [&>div]:truncate [&>svg]:ms-auto";
export const comboboxClearTrigger =
  "text-danger/90 cursor-pointer text-xs hidden ms-1";
export const comboboxPositioner = "!z-50 !w-auto";
export const comboboxContent =
  "min-w-(--reference-width) p-0 z-(--layer-index) [&>div]:p-4 [&>div]:max-h-78 [&>div]:overflow-y-auto [&>div]:flex [&>div]:flex-col [&>div]:gap-5";
export const comboboxItemGroup = "flex flex-col gap-3";
export const comboboxItemGroupLabel = "px-1 text-xs opacity-70 mb-0.5";
export const comboboxItem =
  "flex items-center py-1 px-2.5 -mx-1.5 -my-1 hover:bg-foreground/[.04] rounded-lg cursor-pointer";
export const comboboxItemText = "";
export const comboboxItemIndicator = "ms-auto opacity-70";
