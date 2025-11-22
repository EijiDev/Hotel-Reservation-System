function initMap() {
  const center = {
    lat: 16.0306,
    lng: 120.377,
  }; // Talibaew, Calasiao

  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: center,
    styles: [
      {
        elementType: "geometry",
        stylers: [
          {
            color: "#ffffff",
          },
        ],
      }, // land background white
      {
        elementType: "labels.text.fill",
        stylers: [
          {
            color: "#000000",
          },
        ],
      }, // labels black
      {
        elementType: "labels.text.stroke",
        stylers: [
          {
            color: "#ffffff",
          },
        ],
      },
      {
        featureType: "road",
        elementType: "geometry",
        stylers: [
          {
            color: "#e0e0e0",
          },
        ],
      },
      {
        featureType: "road",
        elementType: "labels.text.fill",
        stylers: [
          {
            color: "#000000",
          },
        ],
      },
      {
        featureType: "water",
        elementType: "geometry",
        stylers: [
          {
            color: "#c9f0ff",
          },
        ],
      },
      {
        featureType: "poi",
        elementType: "geometry",
        stylers: [
          {
            color: "#f5f5f5",
          },
        ],
      },
    ],
  });

  const hotels = [
    {
      name: "Hotel A",
      address: "123 Main St",
      lat: 16.0315,
      lng: 120.3775,
    },
    {
      name: "Hotel B",
      address: "456 Central Ave",
      lat: 16.0298,
      lng: 120.3762,
    },
    {
      name: "Hotel C",
      address: "789 Bay Rd",
      lat: 16.0322,
      lng: 120.3781,
    },
  ];

  hotels.forEach((hotel) => {
    const marker = new google.maps.Marker({
      position: {
        lat: hotel.lat,
        lng: hotel.lng,
      },
      map: map,
      title: hotel.name,
    });

    const infoWindow = new google.maps.InfoWindow({
      content: `<strong>${hotel.name}</strong><br>${hotel.address}`,
    });

    marker.addListener("click", () => infoWindow.open(map, marker));
  });
}
