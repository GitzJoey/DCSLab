<script lang="ts" setup>
import {
  Table,
  TableHeader,
  TableBody,
  TableFooter,
  TableHead,
  TableRow,
  TableCell,
  TableCaption,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";

const invoices: {
  invoice: string;
  paymentStatus: string;
  badge: "success" | "pending" | "danger";
  totalAmount: string;
  paymentMethod: string;
}[] = [
  {
    invoice: "INV001",
    paymentStatus: "Paid",
    badge: "success",
    totalAmount: "$250.00",
    paymentMethod: "Credit Card",
  },
  {
    invoice: "INV002",
    paymentStatus: "Pending",
    badge: "pending",
    totalAmount: "$150.00",
    paymentMethod: "PayPal",
  },
  {
    invoice: "INV003",
    paymentStatus: "Unpaid",
    badge: "danger",
    totalAmount: "$350.00",
    paymentMethod: "Bank Transfer",
  },
  {
    invoice: "INV004",
    paymentStatus: "Paid",
    badge: "success",
    totalAmount: "$450.00",
    paymentMethod: "Credit Card",
  },
  {
    invoice: "INV005",
    paymentStatus: "Paid",
    badge: "success",
    totalAmount: "$550.00",
    paymentMethod: "PayPal",
  },
  {
    invoice: "INV006",
    paymentStatus: "Pending",
    badge: "pending",
    totalAmount: "$200.00",
    paymentMethod: "Bank Transfer",
  },
  {
    invoice: "INV007",
    paymentStatus: "Unpaid",
    badge: "danger",
    totalAmount: "$300.00",
    paymentMethod: "Credit Card",
  },
];
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Table>
          <TableCaption>A list of your recent invoices.</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[100px]">Invoice</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Method</TableHead>
              <TableHead class="text-right">Amount</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in invoices" :key="invoice.invoice">
              <TableCell class="font-medium">
                {{ invoice.invoice }}
              </TableCell>
              <TableCell>
                <Badge look="outline" :variant="invoice.badge">
                  {{ invoice.paymentStatus }}
                </Badge>
              </TableCell>
              <TableCell>{{ invoice.paymentMethod }}</TableCell>
              <TableCell class="text-right">
                {{ invoice.totalAmount }}
              </TableCell>
            </TableRow>
          </TableBody>
          <TableFooter>
            <TableRow>
              <TableCell :colspan="3">Total</TableCell>
              <TableCell class="text-right">$2,500.00</TableCell>
            </TableRow>
          </TableFooter>
        </Table>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Table variant="boxed">
          <TableCaption>A list of your recent invoices.</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[100px]">Invoice</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Method</TableHead>
              <TableHead class="text-right">Amount</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in invoices" :key="invoice.invoice">
              <TableCell class="font-medium">
                {{ invoice.invoice }}
              </TableCell>
              <TableCell>
                <Badge look="outline" :variant="invoice.badge">
                  {{ invoice.paymentStatus }}
                </Badge>
              </TableCell>
              <TableCell>{{ invoice.paymentMethod }}</TableCell>
              <TableCell class="text-right">
                {{ invoice.totalAmount }}
              </TableCell>
            </TableRow>
          </TableBody>
          <TableFooter>
            <TableRow>
              <TableCell :colspan="3">Total</TableCell>
              <TableCell class="text-right">$2,500.00</TableCell>
            </TableRow>
          </TableFooter>
        </Table>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Table variant="boxed" raised="single">
          <TableCaption>A list of your recent invoices.</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[100px]">Invoice</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Method</TableHead>
              <TableHead class="text-right">Amount</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in invoices" :key="invoice.invoice">
              <TableCell class="font-medium">
                {{ invoice.invoice }}
              </TableCell>
              <TableCell>
                <Badge look="outline" :variant="invoice.badge">
                  {{ invoice.paymentStatus }}
                </Badge>
              </TableCell>
              <TableCell>{{ invoice.paymentMethod }}</TableCell>
              <TableCell class="text-right">
                {{ invoice.totalAmount }}
              </TableCell>
            </TableRow>
          </TableBody>
          <TableFooter>
            <TableRow>
              <TableCell :colspan="3">Total</TableCell>
              <TableCell class="text-right">$2,500.00</TableCell>
            </TableRow>
          </TableFooter>
        </Table>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Table variant="boxed" raised="double">
          <TableCaption>A list of your recent invoices.</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[100px]">Invoice</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Method</TableHead>
              <TableHead class="text-right">Amount</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in invoices" :key="invoice.invoice">
              <TableCell class="font-medium">
                {{ invoice.invoice }}
              </TableCell>
              <TableCell>
                <Badge look="outline" :variant="invoice.badge">
                  {{ invoice.paymentStatus }}
                </Badge>
              </TableCell>
              <TableCell>{{ invoice.paymentMethod }}</TableCell>
              <TableCell class="text-right">
                {{ invoice.totalAmount }}
              </TableCell>
            </TableRow>
          </TableBody>
          <TableFooter>
            <TableRow>
              <TableCell :colspan="3">Total</TableCell>
              <TableCell class="text-right">$2,500.00</TableCell>
            </TableRow>
          </TableFooter>
        </Table>
      </div>
    </div>
  </div>
</template>
