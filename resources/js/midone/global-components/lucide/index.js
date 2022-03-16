import { h, defineComponent } from "vue";
import * as lucideIcons from "lucide-vue-next";

const icons = [];
for (const [key, icon] of Object.entries(lucideIcons)) {
  icons[`${key}Icon`] = defineComponent({
    name: `${key}Icon`,
    setup(props, { slots, attrs, emit }) {
      return () =>
        h(icon, {
          class: "lucide",
        });
    },
  });
}

export default icons;
