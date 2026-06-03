export const sheetTrigger = "";
export const sheetBackdrop =
  "fixed inset-0 bg-black/80 z-70 [&[data-state='open']]:animate-in [&[data-state='open']]:fade-in-0 [&[data-state='closed']]:animate-out [&[data-state='closed']]:fade-out-0";
export const sheetPositioner = "";
export const sheetContent = [
  "px-6 pt-6 pb-7 rounded-2xl outline-none backdrop-blur-lg fixed z-70",
  "[&[data-state='open']]:animate-in [&[data-state='open']]:fade-in-0 [&[data-state='open']]:duration-250",
  "[&[data-state='closed']]:animate-out [&[data-state='closed']]:fade-out-0 [&[data-state='closed']]:duration-400",
  "before:mx-2.5 after:mx-3.5",

  // Right
  "[&[data-side='right']]:inset-y-3 [&[data-side='right']]:right-3 [&[data-side='right']]:w-3/4 [&[data-side='right']]:sm:max-w-sm",
  "[&[data-side='right'][data-state='closed']]:slide-out-to-right [&[data-side='right'][data-state='open']]:slide-in-from-right",

  // Left
  "[&[data-side='left']]:inset-y-3 [&[data-side='left']]:left-3 [&[data-side='left']]:w-3/4 [&[data-side='left']]:sm:max-w-sm",
  "[&[data-side='left'][data-state='closed']]:slide-out-to-left [&[data-side='left'][data-state='open']]:slide-in-from-left",

  // Top
  "[&[data-side='top']]:inset-x-3 [&[data-side='top']]:top-3",
  "[&[data-side='top'][data-state='closed']]:slide-out-to-top [&[data-side='top'][data-state='open']]:slide-in-from-top",

  // Bottom
  "[&[data-side='bottom']]:inset-x-3 [&[data-side='bottom']]:bottom-3",
  "[&[data-side='bottom'][data-state='closed']]:slide-out-to-bottom [&[data-side='bottom'][data-state='open']]:slide-in-from-bottom",
];
export const sheetTitle = "font-medium text-lg relative -mt-.5";
export const sheetDescription = "opacity-80 py-2 relative";
export const sheetCloseTrigger =
  "absolute right-0 top-0 p-0 size-8 rounded-full -mt-2 -me-2 border border-foreground/10 bg-background dark:bg-background hover:bg-background before:hidden dark:before:block before:absolute before:-inset-px before:bg-background dark:before:bg-foreground/20 before:z-[-1] before:rounded-full";
