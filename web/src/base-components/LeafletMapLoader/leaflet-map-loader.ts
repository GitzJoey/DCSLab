import L, { MapOptions, Map } from "leaflet";
import "leaflet.markercluster";

export interface MapConfig {
  config: MapOptions;
}

export interface LeafletElement extends HTMLDivElement {
  map: Map;
}

const initializeMap = async (mapRef: LeafletElement, mapConfig: MapConfig) => {
  if (mapRef.map) {
    mapRef.map.remove();
  }

  const map = L.map(mapRef, mapConfig.config);
  mapRef.map = map;

  return {
    map: map,
    leaflet: L,
  };
};

export { initializeMap };
