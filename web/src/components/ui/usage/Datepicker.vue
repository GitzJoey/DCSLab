<script lang="ts" setup>
import {
  DatePickerRoot,
  DatePickerLabel,
  DatePickerControl,
  DatePickerInput,
  DatePickerTrigger,
  DatePickerClearTrigger,
  DatePickerPositioner,
  DatePickerContent,
  DatePickerYearSelect,
  DatePickerMonthSelect,
  DatePickerView,
  DatePickerViewControl,
  DatePickerPresetTrigger,
  DatePickerPrevTrigger,
  DatePickerViewTrigger,
  DatePickerNextTrigger,
  DatePickerRangeText,
  DatePickerTable,
  DatePickerTableHead,
  DatePickerTableRow,
  DatePickerTableHeader,
  DatePickerTableBody,
  DatePickerTableCell,
  DatePickerTableCellTrigger,
  DatePickerContext,
} from "@/components/ui/datepicker";
import { Badge } from "@/components/ui/badge";
import { Box } from "@/components/ui/box";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <DatePickerRoot class="w-84">
          <DatePickerLabel>Basic</DatePickerLabel>
          <DatePickerControl>
            <DatePickerInput />
            <DatePickerTrigger />
            <DatePickerClearTrigger>Clear</DatePickerClearTrigger>
          </DatePickerControl>
          <Teleport to="body">
            <DatePickerPositioner>
              <DatePickerContent>
                <DatePickerYearSelect />
                <DatePickerMonthSelect />
                <DatePickerView view="day">
                  <DatePickerContext v-slot="{ datePicker }">
                    <DatePickerViewControl>
                      <DatePickerPrevTrigger />
                      <DatePickerViewTrigger>
                        <DatePickerRangeText />
                      </DatePickerViewTrigger>
                      <DatePickerNextTrigger />
                    </DatePickerViewControl>
                    <DatePickerTable>
                      <DatePickerTableHead>
                        <DatePickerTableRow>
                          <DatePickerTableHeader
                            v-for="(weekDay, id) in datePicker?.weekDays"
                            :key="id"
                          >
                            {{ weekDay.short }}
                          </DatePickerTableHeader>
                        </DatePickerTableRow>
                      </DatePickerTableHead>
                      <DatePickerTableBody>
                        <DatePickerTableRow
                          v-for="(week, id) in datePicker?.weeks"
                          :key="id"
                        >
                          <DatePickerTableCell
                            v-for="(day, id) in week"
                            :key="id"
                            :value="day"
                          >
                            <DatePickerTableCellTrigger>
                              {{ day.day }}
                            </DatePickerTableCellTrigger>
                          </DatePickerTableCell>
                        </DatePickerTableRow>
                      </DatePickerTableBody>
                    </DatePickerTable>
                  </DatePickerContext>
                </DatePickerView>
                <DatePickerView view="month">
                  <DatePickerContext v-slot="{ datePicker }">
                    <DatePickerViewControl>
                      <DatePickerPrevTrigger />
                      <DatePickerViewTrigger>
                        <DatePickerRangeText />
                      </DatePickerViewTrigger>
                      <DatePickerNextTrigger />
                    </DatePickerViewControl>
                    <DatePickerTable>
                      <DatePickerTableBody>
                        <DatePickerTableRow
                          v-for="(months, id) in datePicker?.getMonthsGrid({
                            columns: 4,
                            format: 'short',
                          })"
                          :key="id"
                        >
                          <DatePickerTableCell
                            v-for="(month, id) in months"
                            :key="id"
                            :value="month.value"
                          >
                            <DatePickerTableCellTrigger>{{
                              month.label
                            }}</DatePickerTableCellTrigger>
                          </DatePickerTableCell>
                        </DatePickerTableRow>
                      </DatePickerTableBody>
                    </DatePickerTable>
                  </DatePickerContext>
                </DatePickerView>
                <DatePickerView view="year">
                  <DatePickerContext v-slot="{ datePicker }">
                    <DatePickerViewControl>
                      <DatePickerPrevTrigger />
                      <DatePickerViewTrigger>
                        <DatePickerRangeText />
                      </DatePickerViewTrigger>
                      <DatePickerNextTrigger />
                    </DatePickerViewControl>
                    <DatePickerTable>
                      <DatePickerTableBody>
                        <DatePickerTableRow
                          v-for="(years, id) in datePicker?.getYearsGrid({
                            columns: 4,
                          })"
                          :key="id"
                        >
                          <DatePickerTableCell
                            v-for="(year, id) in years"
                            :key="id"
                            :value="year.value"
                          >
                            <DatePickerTableCellTrigger>{{
                              year.label
                            }}</DatePickerTableCellTrigger>
                          </DatePickerTableCell>
                        </DatePickerTableRow>
                      </DatePickerTableBody>
                    </DatePickerTable>
                  </DatePickerContext>
                </DatePickerView>
              </DatePickerContent>
            </DatePickerPositioner>
          </Teleport>
        </DatePickerRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <DatePickerRoot selection-mode="range" class="w-140">
          <DatePickerLabel>Range Selection</DatePickerLabel>
          <DatePickerControl>
            <DatePickerInput :index="0" />
            <DatePickerInput :index="1" />
            <DatePickerTrigger />
            <DatePickerClearTrigger>Clear</DatePickerClearTrigger>
          </DatePickerControl>
          <div class="flex gap-1">
            <DatePickerPresetTrigger value="thisWeek" as-child>
              <Badge variant="secondary" look="outline">This Week</Badge>
            </DatePickerPresetTrigger>
            <DatePickerPresetTrigger value="lastWeek" as-child>
              <Badge variant="secondary" look="outline">Last Week</Badge>
            </DatePickerPresetTrigger>
            <DatePickerPresetTrigger value="thisMonth" as-child>
              <Badge variant="secondary" look="outline">This Month</Badge>
            </DatePickerPresetTrigger>
          </div>
          <DatePickerPositioner>
            <DatePickerContent>
              <DatePickerYearSelect />
              <DatePickerMonthSelect />
              <DatePickerView view="day">
                <DatePickerContext v-slot="{ datePicker }">
                  <DatePickerViewControl>
                    <DatePickerPrevTrigger />
                    <DatePickerViewTrigger>
                      <DatePickerRangeText />
                    </DatePickerViewTrigger>
                    <DatePickerNextTrigger />
                  </DatePickerViewControl>
                  <DatePickerTable>
                    <DatePickerTableHead>
                      <DatePickerTableRow>
                        <DatePickerTableHeader
                          v-for="(weekDay, id) in datePicker?.weekDays"
                          :key="id"
                        >
                          {{ weekDay.short }}
                        </DatePickerTableHeader>
                      </DatePickerTableRow>
                    </DatePickerTableHead>
                    <DatePickerTableBody>
                      <DatePickerTableRow
                        v-for="(week, id) in datePicker?.weeks"
                        :key="id"
                      >
                        <DatePickerTableCell
                          v-for="(day, id) in week"
                          :key="id"
                          :value="day"
                        >
                          <DatePickerTableCellTrigger>
                            {{ day.day }}
                          </DatePickerTableCellTrigger>
                        </DatePickerTableCell>
                      </DatePickerTableRow>
                    </DatePickerTableBody>
                  </DatePickerTable>
                </DatePickerContext>
              </DatePickerView>
              <DatePickerView view="month">
                <DatePickerContext v-slot="{ datePicker }">
                  <DatePickerViewControl>
                    <DatePickerPrevTrigger />
                    <DatePickerViewTrigger>
                      <DatePickerRangeText />
                    </DatePickerViewTrigger>
                    <DatePickerNextTrigger />
                  </DatePickerViewControl>
                  <DatePickerTable>
                    <DatePickerTableBody>
                      <DatePickerTableRow
                        v-for="(months, id) in datePicker?.getMonthsGrid({
                          columns: 4,
                          format: 'short',
                        })"
                        :key="id"
                      >
                        <DatePickerTableCell
                          v-for="(month, id) in months"
                          :key="id"
                          :value="month.value"
                        >
                          <DatePickerTableCellTrigger>{{
                            month.label
                          }}</DatePickerTableCellTrigger>
                        </DatePickerTableCell>
                      </DatePickerTableRow>
                    </DatePickerTableBody>
                  </DatePickerTable>
                </DatePickerContext>
              </DatePickerView>
              <DatePickerView view="year">
                <DatePickerContext v-slot="{ datePicker }">
                  <DatePickerViewControl>
                    <DatePickerPrevTrigger />
                    <DatePickerViewTrigger>
                      <DatePickerRangeText />
                    </DatePickerViewTrigger>
                    <DatePickerNextTrigger />
                  </DatePickerViewControl>
                  <DatePickerTable>
                    <DatePickerTableBody>
                      <DatePickerTableRow
                        v-for="(years, id) in datePicker?.getYearsGrid({
                          columns: 4,
                        })"
                        :key="id"
                      >
                        <DatePickerTableCell
                          v-for="(year, id) in years"
                          :key="id"
                          :value="year.value"
                        >
                          <DatePickerTableCellTrigger>{{
                            year.label
                          }}</DatePickerTableCellTrigger>
                        </DatePickerTableCell>
                      </DatePickerTableRow>
                    </DatePickerTableBody>
                  </DatePickerTable>
                </DatePickerContext>
              </DatePickerView>
            </DatePickerContent>
          </DatePickerPositioner>
        </DatePickerRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <DatePickerRoot :num-of-months="2" class="w-84">
          <DatePickerLabel>Multiple Months</DatePickerLabel>
          <DatePickerControl>
            <DatePickerInput :index="0" />
            <DatePickerTrigger />
            <DatePickerClearTrigger>Clear</DatePickerClearTrigger>
          </DatePickerControl>
          <DatePickerPositioner>
            <DatePickerContent>
              <DatePickerYearSelect />
              <DatePickerMonthSelect />
              <DatePickerViewControl>
                <DatePickerPrevTrigger />
                <DatePickerRangeText />
                <DatePickerNextTrigger />
              </DatePickerViewControl>
              <DatePickerView view="day" class="flex-row">
                <DatePickerContext v-slot="{ datePicker }">
                  <DatePickerTable>
                    <DatePickerTableHead>
                      <DatePickerTableRow>
                        <DatePickerTableHeader
                          v-for="(weekDay, id) in datePicker?.weekDays"
                          :key="id"
                        >
                          {{ weekDay.short }}
                        </DatePickerTableHeader>
                      </DatePickerTableRow>
                    </DatePickerTableHead>
                    <DatePickerTableBody>
                      <DatePickerTableRow
                        v-for="(week, id) in datePicker?.weeks"
                        :key="id"
                      >
                        <DatePickerTableCell
                          v-for="(day, id) in week"
                          :key="id"
                          :value="day"
                        >
                          <DatePickerTableCellTrigger>
                            {{ day.day }}
                          </DatePickerTableCellTrigger>
                        </DatePickerTableCell>
                      </DatePickerTableRow>
                    </DatePickerTableBody>
                  </DatePickerTable>
                </DatePickerContext>
                <DatePickerContext v-slot="{ datePicker }">
                  <DatePickerTable>
                    <DatePickerTableHead>
                      <DatePickerTableRow>
                        <DatePickerTableHeader
                          v-for="(weekDay, id) in datePicker?.weekDays"
                          :key="id"
                        >
                          {{ weekDay.short }}
                        </DatePickerTableHeader>
                      </DatePickerTableRow>
                    </DatePickerTableHead>
                    <DatePickerTableBody>
                      <DatePickerTableRow
                        v-for="(week, id) in datePicker?.getOffset({
                          months: 1,
                        }).weeks"
                        :key="id"
                      >
                        <DatePickerTableCell
                          v-for="(day, id) in week"
                          :key="id"
                          :value="day"
                          :visible-range="
                            datePicker?.getOffset({ months: 1 }).visibleRange
                          "
                        >
                          <DatePickerTableCellTrigger>
                            {{ day.day }}
                          </DatePickerTableCellTrigger>
                        </DatePickerTableCell>
                      </DatePickerTableRow>
                    </DatePickerTableBody>
                  </DatePickerTable>
                </DatePickerContext>
              </DatePickerView>
            </DatePickerContent>
          </DatePickerPositioner>
        </DatePickerRoot>
      </div>
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <DatePickerRoot open>
          <DatePickerInput class="w-84 mx-auto" />
          <Box raised="single" class="mx-auto">
            <DatePickerView view="day">
              <DatePickerContext v-slot="{ datePicker }">
                <DatePickerViewControl>
                  <DatePickerPrevTrigger />
                  <DatePickerViewTrigger>
                    <DatePickerRangeText />
                  </DatePickerViewTrigger>
                  <DatePickerNextTrigger />
                </DatePickerViewControl>
                <DatePickerTable>
                  <DatePickerTableHead>
                    <DatePickerTableRow>
                      <DatePickerTableHeader
                        v-for="(weekDay, id) in datePicker?.weekDays"
                        :key="id"
                      >
                        {{ weekDay.short }}
                      </DatePickerTableHeader>
                    </DatePickerTableRow>
                  </DatePickerTableHead>
                  <DatePickerTableBody>
                    <DatePickerTableRow
                      v-for="(week, id) in datePicker?.weeks"
                      :key="id"
                    >
                      <DatePickerTableCell
                        v-for="(day, id) in week"
                        :key="id"
                        :value="day"
                      >
                        <DatePickerTableCellTrigger>
                          {{ day.day }}
                        </DatePickerTableCellTrigger>
                      </DatePickerTableCell>
                    </DatePickerTableRow>
                  </DatePickerTableBody>
                </DatePickerTable>
              </DatePickerContext>
            </DatePickerView>
            <DatePickerView view="month">
              <DatePickerContext v-slot="{ datePicker }">
                <DatePickerViewControl>
                  <DatePickerPrevTrigger />
                  <DatePickerViewTrigger>
                    <DatePickerRangeText />
                  </DatePickerViewTrigger>
                  <DatePickerNextTrigger />
                </DatePickerViewControl>
                <DatePickerTable>
                  <DatePickerTableBody>
                    <DatePickerTableRow
                      v-for="(months, id) in datePicker?.getMonthsGrid({
                        columns: 4,
                        format: 'short',
                      })"
                      :key="id"
                    >
                      <DatePickerTableCell
                        v-for="(month, id) in months"
                        :key="id"
                        :value="month.value"
                      >
                        <DatePickerTableCellTrigger>{{
                          month.label
                        }}</DatePickerTableCellTrigger>
                      </DatePickerTableCell>
                    </DatePickerTableRow>
                  </DatePickerTableBody>
                </DatePickerTable>
              </DatePickerContext>
            </DatePickerView>
            <DatePickerView view="year">
              <DatePickerContext v-slot="{ datePicker }">
                <DatePickerViewControl>
                  <DatePickerPrevTrigger />
                  <DatePickerViewTrigger>
                    <DatePickerRangeText />
                  </DatePickerViewTrigger>
                  <DatePickerNextTrigger />
                </DatePickerViewControl>
                <DatePickerTable>
                  <DatePickerTableBody>
                    <DatePickerTableRow
                      v-for="(years, id) in datePicker?.getYearsGrid({
                        columns: 4,
                      })"
                      :key="id"
                    >
                      <DatePickerTableCell
                        v-for="(year, id) in years"
                        :key="id"
                        :value="year.value"
                      >
                        <DatePickerTableCellTrigger>{{
                          year.label
                        }}</DatePickerTableCellTrigger>
                      </DatePickerTableCell>
                    </DatePickerTableRow>
                  </DatePickerTableBody>
                </DatePickerTable>
              </DatePickerContext>
            </DatePickerView>
          </Box>
        </DatePickerRoot>
      </div>
    </div>
  </div>
</template>
