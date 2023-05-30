import Breadcrumb from "./Breadcrumb.vue";
import Link from "./Link.vue";

const BreadcrumbComponent = Object.assign({}, Breadcrumb, {
  Link: Link,
});

export default BreadcrumbComponent;
