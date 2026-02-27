import Pagination from "./Pagination.vue";
import Link from "./Link.vue";

const PaginationComponent = Object.assign({}, Pagination, {
  Link: Link,
});

export default PaginationComponent;
