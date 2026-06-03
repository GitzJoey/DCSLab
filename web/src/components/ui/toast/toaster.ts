import * as toast from "@zag-js/toast";

const toaster = toast.createStore({
  placement: "bottom-end",
  overlap: true,
  gap: 24,
});

export default toaster;
