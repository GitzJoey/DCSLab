import Dialog from "./Dialog.vue";
import Description from "./Description.vue";
import Footer from "./Footer.vue";
import Panel from "./Panel.vue";
import Title from "./Title.vue";

const DialogComponent = Object.assign({}, Dialog, {
  Description: Description,
  Footer: Footer,
  Panel: Panel,
  Title: Title,
});

export default DialogComponent;
