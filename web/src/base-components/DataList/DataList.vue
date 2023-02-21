<script setup lang="ts">
import Table from "../../base-components/Table";
import { FormInput } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { Menu } from "../../base-components/Headless";
import Lucide from "../../base-components/Lucide";

</script>

<template>
    <div class="intro-y box p-5 mt-5">
        <div class="grid justify-items-end">
            <div class="flex flex-row gap-2">
                <div class="relative w-56 text-slate-500">
                    <FormInput
                        type="text"
                        class="w-56 pr-10 !box"
                        placeholder="Search..."
                    />
                    <Lucide
                        icon="Search"
                        class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3"
                    />
                </div>
                <Button variant="outline-primary">
                    <Lucide icon="RefreshCw" class="w-4 h-4" />
                </Button>
                <Menu>
                    <Menu.Button :as="Button" class="px-2 !box">
                        <span class="flex items-center justify-center w-5 h-5">
                            <Lucide icon="Plus" class="w-4 h-4" />
                        </span>
                    </Menu.Button>
                    <Menu.Items class="w-40" placement="bottom-end">
                        <Menu.Item>
                            <Lucide icon="Printer" class="w-4 h-4 mr-2" /> Print
                        </Menu.Item>
                        <Menu.Item>
                            <Lucide icon="FileText" class="w-4 h-4 mr-2" /> Export to Excel
                        </Menu.Item>
                        <Menu.Item>
                            <Lucide icon="FileText" class="w-4 h-4 mr-2" /> Export to PDF
                        </Menu.Item>
                    </Menu.Items>
                </Menu>
            </div>
        </div>
        <div class="overflow-x-auto">
            <Table class="mt-5">
                <Table.Thead variant="light">
                    <Table.Tr>
                    <Table.Th class="whitespace-nowrap">#</Table.Th>
                    <Table.Th class="whitespace-nowrap"> First Name </Table.Th>
                    <Table.Th class="whitespace-nowrap"> Last Name </Table.Th>
                    <Table.Th class="whitespace-nowrap"> Username </Table.Th>
                    </Table.Tr>
                </Table.Thead>
                <Table.Tbody>
                    <Table.Tr>
                    <Table.Td>1</Table.Td>
                    <Table.Td>Angelina</Table.Td>
                    <Table.Td>Jolie</Table.Td>
                    <Table.Td>@angelinajolie</Table.Td>
                    </Table.Tr>
                    <Table.Tr>
                    <Table.Td>2</Table.Td>
                    <Table.Td>Brad</Table.Td>
                    <Table.Td>Pitt</Table.Td>
                    <Table.Td>@bradpitt</Table.Td>
                    </Table.Tr>
                    <Table.Tr>
                    <Table.Td>3</Table.Td>
                    <Table.Td>Charlie</Table.Td>
                    <Table.Td>Hunnam</Table.Td>
                    <Table.Td>@charliehunnam</Table.Td>
                    </Table.Tr>
                </Table.Tbody>
            </Table>
        </div>
    </div>
</template>