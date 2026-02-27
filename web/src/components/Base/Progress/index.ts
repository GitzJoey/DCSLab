import Progress from "./Progress.vue";
import Bar from "./Bar.vue";

const ProgressComponent = Object.assign({}, Progress, {
  Bar: Bar,
});

export default ProgressComponent;
