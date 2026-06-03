export const toastRoot = [
  "min-w-sm flex flex-col gap-1 relative",
  "[translate:var(--x)_var(--y)] [scale:var(--scale)] [z-index:var(--z-index)]",
  "[height:var(--height)] [opacity:var(--opacity)]",
  "[will-change:translate,opacity,scale]",
  "[transition:translate_400ms,scale_400ms,opacity_400ms,height_400ms,box-shadow_200ms]",
  "[transition-timing-function:cubic-bezier(0.21,1.02,0.73,1)]",
  "data-[state=closed]:[transition:translate_400ms,scale_400ms,opacity_200ms]",
  "data-[state=closed]:[transition-timing-function:cubic-bezier(0.06,0.71,0.55,1)]",
];
export const toastTitle = "font-medium text-nowrap";
export const toastDescription = "text-nowrap opacity-80";
export const toastCloseTrigger =
  "absolute right-0 top-0 p-0 size-6 rounded-full -mt-1.5 -me-1.5 border border-foreground/10 bg-background dark:bg-background hover:bg-background before:hidden dark:before:block before:absolute before:-inset-px before:bg-background dark:before:bg-foreground/20 before:z-[-1] before:rounded-full";
export const toasterContainer = "";
