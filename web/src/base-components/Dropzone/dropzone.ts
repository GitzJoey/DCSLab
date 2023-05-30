import Dropzone, { DropzoneOptions } from "dropzone";
import { DropzoneElement } from "./Dropzone.vue";

const init = (
  el: DropzoneElement,
  props: {
    options: DropzoneOptions;
    refKey?: string;
  }
) => {
  Dropzone.autoDiscover = false;
  if (!el.dropzone) {
    el.dropzone = new Dropzone(el, props.options);
  }
};

export { init };
