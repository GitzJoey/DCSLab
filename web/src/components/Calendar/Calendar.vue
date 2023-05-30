<script setup lang="ts">
import "@fullcalendar/core/vdom";
import FullCalendar from "@fullcalendar/vue3";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import { CalendarOptions } from "@fullcalendar/common";

const options: CalendarOptions = {
  plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
  droppable: true,
  headerToolbar: {
    left: "prev,next today",
    center: "title",
    right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
  },
  initialDate: "2021-01-12",
  navLinks: true,
  editable: true,
  dayMaxEvents: true,
  events: [
    {
      title: "Vue Vixens Day",
      start: "2021-01-05",
      end: "2021-01-08",
    },
    {
      title: "VueConfUS",
      start: "2021-01-11",
      end: "2021-01-15",
    },
    {
      title: "VueJS Amsterdam",
      start: "2021-01-17",
      end: "2021-01-21",
    },
    {
      title: "Vue Fes Japan 2019",
      start: "2021-01-21",
      end: "2021-01-24",
    },
    {
      title: "Laracon 2021",
      start: "2021-01-24",
      end: "2021-01-27",
    },
  ],
  drop: function (info) {
    if (
      document.querySelectorAll("#checkbox-events").length &&
      (document.querySelectorAll("#checkbox-events")[0] as HTMLInputElement)
        ?.checked
    ) {
      (info.draggedEl.parentNode as HTMLElement).remove();
      if (
        document.querySelectorAll("#calendar-events")[0].children.length == 1
      ) {
        document
          .querySelectorAll("#calendar-no-events")[0]
          .classList.remove("hidden");
      }
    }
  },
};
</script>

<template>
  <div class="full-calendar">
    <FullCalendar :options="options" />
  </div>
</template>
