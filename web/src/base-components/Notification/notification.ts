import Toastify from "toastify-js";
import { NotificationElement, NotificationProps } from "./Notification.vue";

const toastifyClass = "_" + Math.random().toString(36).substr(2, 9);

const init = (el: NotificationElement, props: NotificationProps) => {
  el.showToast = () => {
    const clonedEl = el.cloneNode(true) as NotificationElement;
    clonedEl.classList.remove("hidden");
    clonedEl.classList.add(toastifyClass);
    clonedEl.toastify = Toastify({
      duration: -1,
      newWindow: true,
      close: true,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
      ...props.options,
      node: clonedEl,
    });
    clonedEl.toastify.showToast();
    clonedEl
      .querySelectorAll("[data-dismiss='notification']")
      .forEach(function (el) {
        el.addEventListener("click", function () {
          clonedEl.toastify.hideToast();
        });
      });

    el.hideToast = () => {
      document.querySelectorAll(`.${toastifyClass}`).forEach(function (el) {
        const toastifyEl = el as NotificationElement;
        toastifyEl.toastify.hideToast();
      });
    };
  };
};

const reInit = (el: NotificationElement) => {
  const wrapperEl = document.querySelectorAll(`.${toastifyClass}`)[0];
  if (wrapperEl) {
    wrapperEl.innerHTML = el.innerHTML;
  }
};

export { init, reInit };
