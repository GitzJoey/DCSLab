<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import {
  CheckboxRoot,
  CheckboxLabel,
  CheckboxControl,
} from "@/components/ui/checkbox";
import {
  Field,
  FieldDescription,
  FieldGroup,
  FieldLabel,
  FieldLegend,
  FieldSeparator,
  FieldSet,
  FieldTitle,
  FieldContent,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import {
  SelectRoot,
  SelectControl,
  SelectTrigger,
  SelectValueText,
  SelectContent,
  SelectItemGroup,
  SelectItemGroupLabel,
  SelectItem,
  SelectItemText,
} from "@/components/ui/select";
import * as select from "@zag-js/select";
import { Textarea } from "@/components/ui/textarea";
import {
  RadioGroupRoot,
  RadioGroupItem,
  RadioGroupItemControl,
} from "@/components/ui/radio-group";

const comboboxData = [
  { label: "React", code: "react" },
  { label: "Solid", code: "solid" },
  { label: "Vue", code: "vue" },
  { label: "Svelte", code: "svelte" },
];

const collection = select.collection({
  items: comboboxData,
  itemToValue: (item) => item.label,
});
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid sm:grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <div class="w-full max-w-md">
          <form>
            <FieldGroup>
              <FieldSet>
                <FieldLegend>Payment Method</FieldLegend>
                <FieldDescription>
                  All transactions are secure and encrypted
                </FieldDescription>
                <FieldGroup>
                  <Field>
                    <FieldLabel htmlFor="checkout-7j9-card-name-43j">
                      Name on Card
                    </FieldLabel>
                    <Input
                      id="checkout-7j9-card-name-43j"
                      placeholder="Evil Rabbit"
                      required
                    />
                  </Field>
                  <Field>
                    <FieldLabel htmlFor="checkout-7j9-card-number-uw1">
                      Card Number
                    </FieldLabel>
                    <Input
                      id="checkout-7j9-card-number-uw1"
                      placeholder="1234 5678 9012 3456"
                      required
                    />
                    <FieldDescription>
                      Enter your 16-digit card number
                    </FieldDescription>
                  </Field>
                  <div class="grid grid-cols-3 gap-4">
                    <Field>
                      <FieldLabel htmlFor="checkout-exp-month-ts6">
                        Month
                      </FieldLabel>
                      <SelectRoot :collection="collection">
                        <SelectControl>
                          <SelectTrigger>
                            <SelectValueText placeholder="Select a Framework" />
                          </SelectTrigger>
                        </SelectControl>
                        <SelectContent>
                          <SelectItemGroup>
                            <SelectItemGroupLabel>
                              Frameworks
                            </SelectItemGroupLabel>
                            <SelectItem
                              v-for="item in collection.items"
                              :key="item.code"
                              :item="item"
                            >
                              <SelectItemText>{{ item.label }}</SelectItemText>
                            </SelectItem>
                          </SelectItemGroup>
                        </SelectContent>
                      </SelectRoot>
                    </Field>
                    <Field>
                      <FieldLabel htmlFor="checkout-7j9-exp-year-f59">
                        Year
                      </FieldLabel>
                      <SelectRoot class="w-56" :collection="collection">
                        <SelectControl>
                          <SelectTrigger>
                            <SelectValueText placeholder="Select a Framework" />
                          </SelectTrigger>
                        </SelectControl>
                        <SelectContent>
                          <SelectItemGroup>
                            <SelectItemGroupLabel>
                              Frameworks
                            </SelectItemGroupLabel>
                            <SelectItem
                              v-for="item in collection.items"
                              :key="item.code"
                              :item="item"
                            >
                              <SelectItemText>{{ item.label }}</SelectItemText>
                            </SelectItem>
                          </SelectItemGroup>
                        </SelectContent>
                      </SelectRoot>
                    </Field>
                    <Field>
                      <FieldLabel htmlFor="checkout-7j9-cvv">CVV</FieldLabel>
                      <Input id="checkout-7j9-cvv" placeholder="123" required />
                    </Field>
                  </div>
                </FieldGroup>
              </FieldSet>
              <FieldSeparator />
              <FieldSet>
                <FieldLegend>Billing Address</FieldLegend>
                <FieldDescription>
                  The billing address associated with your payment method
                </FieldDescription>
                <CheckboxRoot>
                  <CheckboxControl />
                  <CheckboxLabel class="font-normal"
                    >Same as shipping address</CheckboxLabel
                  >
                </CheckboxRoot>
              </FieldSet>
              <FieldSet>
                <FieldGroup>
                  <Field>
                    <FieldLabel htmlFor="checkout-7j9-optional-comments">
                      Comments
                    </FieldLabel>
                    <Textarea
                      id="checkout-7j9-optional-comments"
                      placeholder="Add any additional comments"
                      class="resize-none"
                    />
                  </Field>
                </FieldGroup>
              </FieldSet>
              <Field orientation="horizontal">
                <Button look="outline" type="submit">Submit</Button>
                <Button type="button"> Cancel </Button>
              </Field>
            </FieldGroup>
          </form>
        </div>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <FieldGroup class="w-full max-w-xs">
          <FieldSet>
            <FieldLegend variant="label">Compute Environment</FieldLegend>
            <FieldDescription>
              Select the compute environment for your cluster.
            </FieldDescription>
            <RadioGroupRoot defaultValue="React">
              <FieldLabel>
                <Field orientation="horizontal">
                  <FieldContent>
                    <FieldTitle>Kubernetes</FieldTitle>
                    <FieldDescription>
                      Run GPU workloads on a K8s cluster.
                    </FieldDescription>
                  </FieldContent>
                  <RadioGroupItem value="React">
                    <RadioGroupItemControl />
                  </RadioGroupItem>
                </Field>
              </FieldLabel>
              <FieldLabel>
                <Field orientation="horizontal">
                  <FieldContent>
                    <FieldTitle>Virtual Machine</FieldTitle>
                    <FieldDescription>
                      Access a cluster to run GPU workloads.
                    </FieldDescription>
                  </FieldContent>
                  <RadioGroupItem value="Solid">
                    <RadioGroupItemControl />
                  </RadioGroupItem>
                </Field>
              </FieldLabel>
            </RadioGroupRoot>
          </FieldSet>
        </FieldGroup>
      </div>
    </div>
  </div>
</template>
