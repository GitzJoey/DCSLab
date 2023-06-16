import Pagination from "./Pagination.vue";
import Link from "./Link.vue";
import Text from "./Text.vue";

const PaginationComponent = Object.assign({}, Pagination, {
  Link: Link,
  Text: Text,
});

export default PaginationComponent;
