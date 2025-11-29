<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/location.style.css">
    <title>Map</title>
</head>
<body>
    <div class="location-section">
        <h2 class="location-header">Where We Are Located</h2>
        <p class="location-subtext">
            Explore our hotels across the city. Click on any marker to see the hotel's address and details.
        </p>
        <div id="map"></div>
    </div>
    <script src="../public/js/map.js"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDP_Dm6gXSM-LooM12RF2FWAIsWVCoJW-E&callback=initMap"></script>
</body>
</html>
