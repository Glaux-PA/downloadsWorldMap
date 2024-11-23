document.addEventListener("DOMContentLoaded", () => {
  const map = L.map("map").setView([20, 0], 1); // Centro en el mundo

  //
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);

  // Función para obtener el color según el número de descargas
  function getColor(value, minValue, maxValue) {
    if (!value || value === 0) return "#FFFFFF";

    const normalizedValue = (value - minValue) / (maxValue - minValue);

    const nonLinearValue = Math.sqrt(normalizedValue);

    // Paleta de colores en tonos naranjas
    const colorScale = [
      "#FFE0B2",
      "#FFCC80",
      "#FFB74D",
      "#FF9800",
      "#FB8C00",
      "#F57C00",
      "#E65100",
    ];
    const index = Math.floor(nonLinearValue * (colorScale.length - 1));
    return colorScale[index];
  }

  // Función para crear la barra cromática (degradado)
  function createGradientLegend(minValue, maxValue) {
    const legend = L.control({ position: "topright" });
    legend.onAdd = function () {
      const div = L.DomUtil.create("div", "info legend");
      div.innerHTML += "<br><b>Descargas</b>";
      div.innerHTML = '<div class="gradient-bar"></div>';

      div.innerHTML +=
        '<div class="scale-numbers">' +
        '<span class="min-value">' +
        Math.round(minValue) +
        "</span>" +
        '<span class="max-value">' +
        Math.round(maxValue) +
        "</span>" +
        "</div>";

      return div;
    };

    legend.addTo(map);
  }

  // URL del archivo GeoJSON
  const geojsonURL =
    "https://raw.githubusercontent.com/datasets/geo-countries/master/data/countries.geojson";

  // Cargar y añadir los datos GeoJSON al mapa
  async function loadAndProcessData(geojsonURL) {
    try {
      const geojsonResponse = await fetch(geojsonURL);
      const geojsonData = await geojsonResponse.json();
      const currentUrl = window.location.href.split("/");
      const bookId = currentUrl[currentUrl.length - 1];
      const downloadsResponse = await fetch(
        `${window.location.origin}/index.php/coa/catalog/downloadsPerCountry/${bookId}`
      );

      const downloadsData = await downloadsResponse.json();
      const downloads = downloadsData;

      // Establecer valores mínimo y máximo
      const downloadValues = Object.values(downloads);
      const minValue = 0;
      const maxValueRaw = Math.max(...downloadValues);

      // Redondear el máximo al siguiente múltiplo de 10
      const maxValue = Math.ceil(maxValueRaw / 10) * 10;

      // Crear la barra cromática
      createGradientLegend(minValue, maxValue);

      // Estilo para los países
      function style(feature) {
        const downloadsCount = downloads[feature.properties.ISO_A2] || 0;
        return {
          fillColor: getColor(downloadsCount, minValue, maxValue),
          weight: 1,
          opacity: 1,
          color: "white",
          fillOpacity: 0.7,
        };
      }

      // Función para añadir popups
      function onEachFeature(feature, layer) {
        const countryCode = feature.properties.ISO_A2;
        const downloadsCount = downloads[countryCode] || 0;
        const popupContent = `
                <b>Country:</b> ${feature.properties.ADMIN} <br>
                <b>Downloads:</b> ${
                  downloadsCount > 0 ? downloadsCount : "Sin datos"
                }
            `;
        if (downloadsCount > 0) layer.bindPopup(popupContent);
      }

      // Añadir el GeoJSON al mapa
      L.geoJson(geojsonData, {
        style: style,
        onEachFeature: onEachFeature,
      }).addTo(map);
    } catch (error) {
      console.error("Error cargando datos:", error);
    }
  }

  loadAndProcessData(geojsonURL);
});
