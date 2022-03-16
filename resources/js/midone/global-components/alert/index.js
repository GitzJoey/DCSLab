import { h, ref, defineComponent, resolveDirective, withDirectives } from "vue";
import "@left4code/tw-starter/dist/js/alert";

const init = (el, { props, emit }) => {
  const alert = tailwind.Alert.getOrCreateInstance(el);
  if (props.show) {
    alert.show();
  } else {
    alert.hide();
  }

  if (el["__initiated"] === undefined) {
    el["__initiated"] = true;

    el.addEventListener("show.tw.alert", () => {
      emit("show");
    });

    el.addEventListener("shown.tw.alert", () => {
      emit("shown");
    });

    el.addEventListener("hide.tw.alert", () => {
      emit("hide");
    });

    el.addEventListener("hidden.tw.alert", () => {
      emit("hidden");
    });
  }
};

// Alert wrapper
const Alert = defineComponent({
  name: "Alert",
  props: {
    show: {
      type: Boolean,
      default: true,
    },
  },
  directives: {
    alert: {
      mounted(el, { value }) {
        init(el, value);
      },
      updated(el, { value }) {
        init(el, value);
      },
    },
  },
  setup(props, { slots, attrs, emit }) {
    const alertRef = ref();
    const alertDirective = resolveDirective("alert");

    return () =>
      withDirectives(
        h(
          "div",
          {
            class: "alert",
            rolse: "alert",
            ref: alertRef,
          },
          slots.default({
            dismiss: () => {
              tailwind.Alert.getOrCreateInstance(alertRef.value).hide();
            },
          })
        ),
        [[alertDirective, { props, emit }]]
      );
  },
});

export { Alert };
