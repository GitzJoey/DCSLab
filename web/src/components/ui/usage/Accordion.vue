<script lang="ts" setup>
import {
  AccordionRoot,
  AccordionItem,
  AccordionTrigger,
  AccordionContent,
} from "@/components/ui/accordion";
import { Box } from "@/components/ui/box";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid sm:grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Box raised="single" class="w-full">
          <AccordionRoot
            class="w-full"
            :default-value="['product-information']"
          >
            <AccordionItem value="product-information">
              <AccordionTrigger>Product Information</AccordionTrigger>
              <AccordionContent>
                <p class="mb-2">
                  Our flagship product combines cutting-edge technology with
                  sleek design. Built with premium materials, it offers
                  unparalleled performance and reliability.
                </p>
                <p>
                  Key features include advanced processing capabilities, and an
                  intuitive user interface designed for both beginners and
                  experts.
                </p>
              </AccordionContent>
            </AccordionItem>
            <AccordionItem value="shipping-details">
              <AccordionTrigger>Shipping Details</AccordionTrigger>
              <AccordionContent>
                <p class="mb-2">
                  We offer worldwide shipping through trusted courier partners.
                  Standard delivery takes 3-5 business days, while express
                  shipping ensures delivery within 1-2 business days.
                </p>
                <p>
                  All orders are carefully packaged and fully insured. Track
                  your shipment in real-time through our dedicated tracking
                  portal.
                </p>
              </AccordionContent>
            </AccordionItem>
            <AccordionItem value="return-policy">
              <AccordionTrigger>Return Policy</AccordionTrigger>
              <AccordionContent>
                <p class="mb-2">
                  We stand behind our products with a comprehensive 30-day
                  return policy. If you're not completely satisfied, simply
                  return the item in its original condition.
                </p>
                <p>
                  Our hassle-free return process includes free return shipping
                  and full refunds processed within 48 hours of receiving the
                  returned item.
                </p>
              </AccordionContent>
            </AccordionItem>
          </AccordionRoot>
        </Box>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <AccordionRoot
          class="w-full"
          variant="boxed"
          :default-value="['product-information']"
        >
          <AccordionItem raised="single" value="product-information">
            <AccordionTrigger>Product Information</AccordionTrigger>
            <AccordionContent>
              <p class="mb-2">
                Our flagship product combines cutting-edge technology with sleek
                design. Built with premium materials, it offers unparalleled
                performance and reliability.
              </p>
              <p>
                Key features include advanced processing capabilities, and an
                intuitive user interface designed for both beginners and
                experts.
              </p>
            </AccordionContent>
          </AccordionItem>
          <AccordionItem raised="single" value="shipping-details">
            <AccordionTrigger>Shipping Details</AccordionTrigger>
            <AccordionContent>
              <p class="mb-2">
                We offer worldwide shipping through trusted courier partners.
                Standard delivery takes 3-5 business days, while express
                shipping ensures delivery within 1-2 business days.
              </p>
              <p>
                All orders are carefully packaged and fully insured. Track your
                shipment in real-time through our dedicated tracking portal.
              </p>
            </AccordionContent>
          </AccordionItem>
          <AccordionItem raised="single" value="return-policy">
            <AccordionTrigger>Return Policy</AccordionTrigger>
            <AccordionContent>
              <p class="mb-2">
                We stand behind our products with a comprehensive 30-day return
                policy. If you're not completely satisfied, simply return the
                item in its original condition.
              </p>
              <p>
                Our hassle-free return process includes free return shipping and
                full refunds processed within 48 hours of receiving the returned
                item.
              </p>
            </AccordionContent>
          </AccordionItem>
        </AccordionRoot>
      </div>
    </div>
  </div>
</template>
