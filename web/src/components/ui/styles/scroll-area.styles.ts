export const scrollAreaRoot = "";
export const scrollAreaViewport = "h-full [scrollbar-width:none] [&::-webkit-scrollbar]:hidden";
export const scrollAreaScrollbar = [
    "flex relative bg-foreground/5 rounded-md m-2 opacity-0 transition-opacity duration-150 pointer-events-none",
    "before:content-[''] before:absolute",
    "data-[scrolling]:duration-[0ms]",
    "data-[hover]:opacity-100 data-[scrolling]:opacity-100 data-[hover]:pointer-events-auto data-[scrolling]:pointer-events-auto",
    "data-[orientation=vertical]:w-2 data-[orientation=vertical]:before:w-5 data-[orientation=vertical]:before:h-full data-[orientation=vertical]:before:left-1/2 data-[orientation=vertical]:before:-translate-x-1/2 data-[orientation=vertical]:[&:not([data-overflow-y])]:hidden",
    "data-[orientation=horizontal]:h-2 data-[orientation=horizontal]:before:w-full data-[orientation=horizontal]:before:h-5 data-[orientation=horizontal]:before:left-0 data-[orientation=horizontal]:before:right-0 data-[orientation=horizontal]:before:bottom-[-0.5rem] data-[orientation=horizontal]:[&:not([data-overflow-x])]:hidden",
];
export const scrollAreaThumb =
    "w-full rounded-[inherit] bg-foreground/20 data-[orientation=horizontal]:w-auto data-[orientation=horizontal]:h-full";
export const scrollAreaContent = "";
export const scrollAreaCorner = "bg-transparent";


