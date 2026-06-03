import {
  defineComponent,
  h,
  cloneVNode,
  Fragment,
  isVNode,
  type VNode,
  type PropType,
} from "vue";
import { calculateSlot, flattenItems, type AnyProps } from "./slot";

/* -------------------------------------------------------------------------------------------------
 * Vue Implementation (Component Shell)
 * -----------------------------------------------------------------------------------------------*/

export const Slot = defineComponent({
  name: "Slot",
  inheritAttrs: false,
  props: {
    children: {
      type: [Object, Array] as PropType<any>,
    },
  },
  setup(props, { attrs, slots }) {
    return () => {
      const raw = props.children ?? slots.default?.();

      const isValidVNode = (item: any): item is VNode => isVNode(item) && typeof item.type !== "symbol";

      // Use generic flatten logic with Vue-specific adapter
      // Aligned with React's flatten(children) logic
      const items = flattenItems<VNode>(
        raw as any,
        (item) => isVNode(item) && item.type === Fragment,
        (item) => (isVNode(item) && Array.isArray(item.children) ? (item.children as VNode[]) : [])
      ).filter(isValidVNode);

      // Use our vanilla logic to determine the transform
      const result = calculateSlot<VNode>({
        props: attrs as AnyProps,
        items,
        isValid: isValidVNode,
        getProps: (item) => (item.props as AnyProps) || {},
        getChildren: (item) => item.children,
      });

      // If it's a wrapper, we render a real div
      if (result.type === "wrapper") {
        return h("div", result.props, result.children as any);
      }

      // If it's slotted, we clone the target VNode with merged props
      const target = result.target;
      return cloneVNode(target, result.props, false);
    };
  },
});

export { Slot as Root };
