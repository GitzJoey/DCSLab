import dayjs from "dayjs";
import Litepicker from "litepicker";
import {
  LitepickerElement,
  LitepickerProps,
  LitepickerEmit,
} from "./Litepicker.vue";

const getDateFormat = (format: string | undefined) => {
  return format !== undefined ? format : "D MMM, YYYY";
};

const setValue = (props: LitepickerProps, emit: LitepickerEmit) => {
  const format = getDateFormat(props.options.format);
  if (!props.modelValue.length) {
    let date = dayjs().format(format);
    date +=
      !props.options.singleMode && props.options.singleMode !== undefined
        ? " - " + dayjs().add(1, "month").format(format)
        : "";
    emit("update:modelValue", date);
  }
};

const init = (
  el: LitepickerElement,
  props: LitepickerProps,
  emit: LitepickerEmit
) => {
  const format = getDateFormat(props.options.format);
  el.litePickerInstance = new Litepicker({
    ...props.options,
    element: el,
    format: format,
    setup: (picker) => {
      if (picker.on) {
        picker.on("selected", (startDate, endDate) => {
          let date = dayjs(startDate.dateInstance).format(format);
          date +=
            endDate !== undefined && endDate !== null
              ? " - " + dayjs(endDate.dateInstance).format(format)
              : "";
          emit("update:modelValue", date);
        });
      }
    },
  });
};

const reInit = (
  el: LitepickerElement,
  props: LitepickerProps,
  emit: LitepickerEmit
) => {
  el.litePickerInstance.destroy();
  init(el, props, emit);
};

export { setValue, init, reInit };
