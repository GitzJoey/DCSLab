import { Loader } from "@googlemaps/js-api-loader";

type google = typeof google;

export interface MapConfig {
  apiKey: string;
  config: (google: google) => google.maps.MapOptions;
}

const initializeMap = async (mapRef: HTMLDivElement, mapConfig: MapConfig) => {
  await new Loader({
    apiKey: mapConfig.apiKey,
  }).load();

  const google = window.google;
  const map = new google.maps.Map(mapRef, mapConfig.config(google));

  return {
    map: map,
    google: google,
  };
};

export { initializeMap };
