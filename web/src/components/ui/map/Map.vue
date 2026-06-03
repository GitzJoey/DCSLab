<script setup lang="ts">
import "maplibre-gl/dist/maplibre-gl.css";
import maplibregl, { type MapOptions } from "maplibre-gl";
import { ref, onMounted } from "vue";
import { Button } from "@/components/ui/button";
import { Plus, Minus, MapPin, Compass, Expand, X } from "lucide-vue-next";
import type { FeatureCollection, Point } from "geojson";
import { cn } from "@midoneui/core/utils/cn";
import { map } from "@midoneui/core/styles/map.styles";

const mapRef = ref();
const mapInstance = ref<maplibregl.Map>();
const isFullscreen = ref(false);

const {
  class: className,
  markers,
  ...props
} = defineProps<
  Partial<MapOptions> & {
    class?: string;
    markers?: FeatureCollection<Point>;
  }
>();

onMounted(() => {
  mapInstance.value = new maplibregl.Map({
    ...Object.fromEntries(Object.entries(props).filter(([_, value]) => value)),
    style: "https://tiles.openfreemap.org/styles/positron",
    container: mapRef.value,
  });

  markers &&
    mapInstance.value.on("load", () => {
      // Add source with clustering
      mapInstance.value!.addSource("markers", {
        type: "geojson",
        data: markers,
        cluster: true,
        clusterMaxZoom: 14, // Max zoom for clustering
        clusterRadius: 50, // Cluster radius in pixels
      });

      // Layer for clusters (circles)
      mapInstance.value!.addLayer({
        id: "clusters",
        type: "circle",
        source: "markers",
        filter: ["has", "point_count"],
        paint: {
          "circle-color": [
            "step",
            ["get", "point_count"],
            "#cccccc", // Color for < 5 points
            5,
            "#cccccc", // Color for 5-10 points
            10,
            "#cccccc", // Color for > 10 points
          ],
          "circle-radius": [
            "step",
            ["get", "point_count"],
            20, // Radius for < 5 points
            5,
            30, // Radius for 5-10 points
            10,
            40, // Radius for > 10 points
          ],
        },
      });

      // Layer for cluster count numbers
      mapInstance.value!.addLayer({
        id: "cluster-count",
        type: "symbol",
        source: "markers",
        filter: ["has", "point_count"],
        layout: {
          "text-field": "{point_count_abbreviated}",
          "text-font": ["Open Sans Bold"],
          "text-size": 12,
        },
      });

      // Layer for individual points (unclustered)
      mapInstance.value!.addLayer({
        id: "unclustered-point",
        type: "circle",
        source: "markers",
        filter: ["!", ["has", "point_count"]],
        paint: {
          "circle-color": "#333333",
          "circle-radius": 8,
          "circle-stroke-width": 2,
          "circle-stroke-color": "#ffffff",
        },
      });

      // Click on cluster to zoom in
      mapInstance.value!.on("click", "clusters", async (e) => {
        const features = mapInstance.value!.queryRenderedFeatures(e.point, {
          layers: ["clusters"],
        });

        const clusterId = features[0]?.properties.cluster_id;
        const source = mapInstance.value!.getSource(
          "markers"
        ) as maplibregl.GeoJSONSource;

        try {
          const zoom = await source.getClusterExpansionZoom(clusterId);

          mapInstance.value!.easeTo({
            center: (features[0]?.geometry as any).coordinates,
            zoom: zoom,
          });
        } catch (err) {
          console.error("Error getting cluster expansion zoom:", err);
        }
      });

      // Click on individual point to show popup
      mapInstance.value!.on("click", "unclustered-point", (e) => {
        const coordinates = (
          e.features![0]?.geometry as any
        ).coordinates.slice();
        const { name, type } = (e.features![0]?.properties || {}) as any;

        new maplibregl.Popup()
          .setLngLat(coordinates)
          .setHTML(`<h3>${name}</h3><p>${type}</p>`)
          .addTo(mapInstance.value!);
      });

      // Change cursor on hover cluster/point
      mapInstance.value!.on("mouseenter", "clusters", () => {
        mapInstance.value!.getCanvas().style.cursor = "pointer";
      });
      mapInstance.value!.on("mouseleave", "clusters", () => {
        mapInstance.value!.getCanvas().style.cursor = "";
      });
      mapInstance.value!.on("mouseenter", "unclustered-point", () => {
        mapInstance.value!.getCanvas().style.cursor = "pointer";
      });
      mapInstance.value!.on("mouseleave", "unclustered-point", () => {
        mapInstance.value!.getCanvas().style.cursor = "";
      });
    });
});

const zoomIn = () => {
  mapInstance.value?.zoomIn();
};

const zoomOut = () => {
  mapInstance.value?.zoomOut();
};

const resetNorth = () => {
  mapInstance.value?.easeTo({ bearing: 0, pitch: 0 });
};

const locateMe = () => {
  if (!navigator.geolocation) {
    alert("Geolocation is not supported by your browser");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { longitude, latitude } = position.coords;
      mapInstance.value?.flyTo({
        center: [longitude, latitude],
        zoom: 14,
      });

      new maplibregl.Marker({ color: "var(--color-foreground)" })
        .setLngLat([longitude, latitude])
        .addTo(mapInstance.value!);
    },
    (error) => {
      alert("Failed to get location: " + error.message);
    }
  );
};

const toggleFullscreen = () => {
  if (!mapRef.value) return;

  if (!isFullscreen.value) {
    if (mapRef.value.requestFullscreen) {
      mapRef.value.requestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  }
  isFullscreen.value = !isFullscreen.value;
};
</script>

<template>
  <div
    data-scope="map"
    data-part="root"
    ref="mapRef"
    :class="cn(map, className)"
  >
    <div data-scope="map" data-part="controls">
      <Button
        data-scope="map"
        data-part="zoom-in"
        @click="zoomIn"
        variant="ghost"
        size="sm"
      >
        <Plus />
      </Button>
      <Button
        data-scope="map"
        data-part="zoom-out"
        @click="zoomOut"
        variant="ghost"
        size="sm"
      >
        <Minus />
      </Button>
      <Button
        data-scope="map"
        data-part="reset-north"
        @click="resetNorth"
        variant="ghost"
        size="sm"
      >
        <Compass />
      </Button>
      <Button
        data-scope="map"
        data-part="locate"
        @click="locateMe"
        variant="ghost"
        size="sm"
      >
        <MapPin />
      </Button>
      <Button
        data-scope="map"
        data-part="toggle-fullscreen"
        @click="toggleFullscreen"
        variant="ghost"
        size="sm"
      >
        <Expand v-if="!isFullscreen" />
        <X v-else />
      </Button>
    </div>
  </div>
</template>
