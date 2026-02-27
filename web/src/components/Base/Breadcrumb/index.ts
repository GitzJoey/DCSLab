import Breadcrumb from "./Breadcrumb.vue";
import Link from "./Link.vue";
import Text from "./Text.vue";

const BreadcrumbComponent = Object.assign({}, Breadcrumb, {
  Link: Link,
  Text: Text,
});

export default BreadcrumbComponent;
