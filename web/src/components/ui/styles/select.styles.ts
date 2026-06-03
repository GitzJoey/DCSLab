export const selectRoot =
  "w-full flex flex-col gap-2.5 [&[data-multiple=true]_[data-part=clear-trigger]]:block";
export const selectLabel = "";
export const selectControl = "relative";
export const selectTrigger =
  "w-full font-normal text-foreground/70 border border-foreground/15 bg-background";
export const selectValueText = "truncate";
export const selectIndicator =
  "ms-auto transition data-[state=open]:rotate-180";
export const selectClearTrigger =
  "text-danger/90 cursor-pointer text-xs hidden ms-1";
export const selectPositioner = "!z-50 !w-auto";
export const selectContent =
  "min-w-(--reference-width) p-0 z-(--layer-index) [&>div]:p-4 [&>div]:max-h-78 [&>div]:overflow-y-auto [&>div]:flex [&>div]:flex-col [&>div]:gap-5";
export const selectItemGroup = "flex flex-col gap-3";
export const selectItemGroupLabel = "px-1 text-xs opacity-70 mb-0.5";
export const selectItem =
  "flex items-center py-1 px-2.5 -mx-1.5 -my-1 hover:bg-foreground/[.04] rounded-lg cursor-pointer";
export const selectItemText = "";
export const selectItemIndicator = "ms-auto opacity-70";
export const selectHiddenSelect = "";
