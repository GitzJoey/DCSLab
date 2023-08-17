import Table from "./Table.vue";
import Thead from "./Thead.vue";
import Tbody from "./Tbody.vue";
import Tr from "./Tr.vue";
import Th from "./Th.vue";
import Td from "./Td.vue";

const TableComponent = Object.assign({}, Table, {
  Thead: Thead,
  Tbody: Tbody,
  Tr: Tr,
  Th: Th,
  Td: Td,
});

export default TableComponent;
