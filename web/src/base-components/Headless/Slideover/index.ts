import Slideover from "./Slideover.vue";
import Description from "./Description.vue";
import Footer from "./Footer.vue";
import Panel from "./Panel.vue";
import Title from "./Title.vue";

const SlideoverComponent = Object.assign({}, Slideover, {
  Description: Description,
  Footer: Footer,
  Panel: Panel,
  Title: Title,
});

export default SlideoverComponent;
