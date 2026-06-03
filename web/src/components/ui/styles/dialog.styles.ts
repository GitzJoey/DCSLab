export const dialogTrigger = "";
export const dialogBackdrop =
  "fixed inset-0 bg-black/80 z-70 [&[data-state='open']]:animate-in [&[data-state='open']]:fade-in-0 [&[data-state='closed']]:animate-out [&[data-state='closed']]:fade-out-0";
export const dialogPositioner = "";
export const dialogContent = [
  "px-6 pt-6 pb-7 rounded-2xl outline-none backdrop-blur-lg fixed top-[50%] left-[50%] z-70 w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] sm:max-w-lg",
  "[&[data-state='open']]:animate-in [&[data-state='open']]:fade-in-0 [&[data-state='open']]:zoom-in-80 [&[data-state='open']]:duration-250",
  "[&[data-state='closed']]:animate-out [&[data-state='closed']]:fade-out-0 [&[data-state='closed']]:zoom-out-80 [&[data-state='closed']]:duration-400",
  "before:mx-2.5 after:mx-3.5",
];
export const dialogTitle = "font-medium text-base relative -mt-.5";
export const dialogDescription = "opacity-80 py-2 relative";
export const dialogCloseTrigger =
  "absolute right-0 top-0 p-0 size-8 rounded-full -mt-2 -me-2 border border-foreground/10 bg-background dark:bg-background hover:bg-background before:hidden dark:before:block before:absolute before:-inset-px before:bg-background dark:before:bg-foreground/20 before:z-[-1] before:rounded-full";
