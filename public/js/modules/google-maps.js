// ===== GOOGLE MAPS: AUTOCOMPLETE, MAPPA INTERATTIVA, CALCOLO TRATTA =====
function initGoogleMaps() {
  'use strict';

  var pickupInput = document.getElementById('addressPickup');
  var deliveryInput = document.getElementById('addressDelivery');
  if (!pickupInput || !deliveryInput) return;

  // --- Stato locale ---
  var activeField = null;
  var map = null;
  var routePreviewMap = null;
  var geocoder = null;
  var reverseTimeout = null;

  // Riferimenti globali per reset
  window._quoteRoutePolyline = null;
  window._quoteRouteMarkers = [];

  // --- Elementi DOM ---
  var mapContainer = document.getElementById('quoteMapContainer');
  var mapDiv = document.getElementById('quoteMap');
  var mapLabel = document.getElementById('quoteMapLabel');
  var mapAddress = document.getElementById('quoteMapAddress');
  var mapConfirmBtn = document.getElementById('quoteMapConfirmBtn');
  var mapCloseBtn = document.getElementById('quoteMapCloseBtn');
  var pickupMapBtn = document.getElementById('pickupMapBtn');
  var deliveryMapBtn = document.getElementById('deliveryMapBtn');
  var routeSummary = document.getElementById('routeSummary');
  var routePreviewDiv = document.getElementById('routePreviewMap');

  // --- Google Places Autocomplete ---
  var autocompleteOptions = {
    componentRestrictions: { country: 'it' },
    fields: ['formatted_address', 'geometry']
  };

  var pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput, autocompleteOptions);
  var deliveryAutocomplete = new google.maps.places.Autocomplete(deliveryInput, autocompleteOptions);

  pickupAutocomplete.addListener('place_changed', function () {
    var place = pickupAutocomplete.getPlace();
    if (place && place.geometry) {
      window._quotePickupCoords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      };
      pickupInput.value = place.formatted_address;
      pickupInput.classList.remove('is-error');
      tryCalculateRoute();
    }
  });

  deliveryAutocomplete.addListener('place_changed', function () {
    var place = deliveryAutocomplete.getPlace();
    if (place && place.geometry) {
      window._quoteDeliveryCoords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      };
      deliveryInput.value = place.formatted_address;
      deliveryInput.classList.remove('is-error');
      tryCalculateRoute();
    }
  });

  window._quotePickupCoords = null;
  window._quoteDeliveryCoords = null;

  // --- Mappa interattiva per selezione indirizzo ---
  geocoder = new google.maps.Geocoder();

  function openMapForField(field) {
    activeField = field;
    mapLabel.textContent = field === 'pickup' ? 'Seleziona indirizzo di ritiro' : 'Seleziona indirizzo di consegna';
    mapAddress.textContent = 'Sposta la mappa per selezionare l\'indirizzo';
    mapContainer.style.display = 'block';

    if (!map) {
      map = new google.maps.Map(mapDiv, {
        center: { lat: 41.9028, lng: 12.4964 },
        zoom: 6,
        disableDefaultUI: true,
        zoomControl: true,
        gestureHandling: 'greedy',
        styles: [
          { featureType: 'poi', stylers: [{ visibility: 'off' }] },
          { featureType: 'transit', stylers: [{ visibility: 'off' }] }
        ]
      });

      map.addListener('idle', function () {
        clearTimeout(reverseTimeout);
        reverseTimeout = setTimeout(function () {
          reverseGeocode(map.getCenter());
        }, 400);
      });
    }

    var existingCoords = field === 'pickup' ? window._quotePickupCoords : window._quoteDeliveryCoords;
    if (existingCoords) {
      map.setCenter(existingCoords);
      map.setZoom(15);
    } else {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (pos) {
            map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
            map.setZoom(15);
          },
          function () {
            map.setCenter({ lat: 41.9028, lng: 12.4964 });
            map.setZoom(6);
          },
          { timeout: 5000 }
        );
      }
    }

    mapContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function reverseGeocode(latlng) {
    geocoder.geocode({ location: latlng }, function (results, status) {
      if (status === 'OK' && results[0]) {
        mapAddress.textContent = results[0].formatted_address;
      } else {
        mapAddress.textContent = 'Indirizzo non trovato — sposta la mappa';
      }
    });
  }

  function confirmMapSelection() {
    var center = map.getCenter();
    var coords = { lat: center.lat(), lng: center.lng() };
    var address = mapAddress.textContent;

    if (address === 'Sposta la mappa per selezionare l\'indirizzo' || address === 'Indirizzo non trovato — sposta la mappa') {
      return;
    }

    if (activeField === 'pickup') {
      pickupInput.value = address;
      pickupInput.classList.remove('is-error');
      window._quotePickupCoords = coords;
      mapContainer.style.display = 'none';
      if (!deliveryInput.value.trim()) {
        setTimeout(function () { openMapForField('delivery'); }, 300);
      } else {
        tryCalculateRoute();
      }
    } else {
      deliveryInput.value = address;
      deliveryInput.classList.remove('is-error');
      window._quoteDeliveryCoords = coords;
      mapContainer.style.display = 'none';
      tryCalculateRoute();
    }
  }

  // --- Event listeners mappa ---
  if (pickupMapBtn) {
    pickupMapBtn.addEventListener('click', function () { openMapForField('pickup'); });
  }
  if (deliveryMapBtn) {
    deliveryMapBtn.addEventListener('click', function () { openMapForField('delivery'); });
  }
  if (mapConfirmBtn) {
    mapConfirmBtn.addEventListener('click', confirmMapSelection);
  }
  if (mapCloseBtn) {
    mapCloseBtn.addEventListener('click', function () { mapContainer.style.display = 'none'; });
  }

  // --- Calcolo rotta ---
  function tryCalculateRoute() {
    var pCoords = window._quotePickupCoords;
    var dCoords = window._quoteDeliveryCoords;
    if (!pCoords || !dCoords) return;

    routeSummary.style.display = 'block';
    routeSummary.classList.add('quote-route-summary--loading');

    var url = 'api/route-calc.php?origin_lat=' + pCoords.lat
      + '&origin_lng=' + pCoords.lng
      + '&dest_lat=' + dCoords.lat
      + '&dest_lng=' + dCoords.lng;

    fetch(url)
      .then(function (res) { return res.json(); })
      .then(function (data) {
        routeSummary.classList.remove('quote-route-summary--loading');

        if (data.error) {
          document.getElementById('routeDistance').textContent = 'Errore';
          document.getElementById('routeDuration').textContent = data.error;
          return;
        }

        document.getElementById('routeDistance').textContent = data.distance_km + ' km';
        document.getElementById('routeDuration').textContent = data.duration_text;
        document.getElementById('routeFuelCost').textContent = '€' + data.fuel_cost.toFixed(2);
        document.getElementById('routeTollCost').textContent = '€' + data.toll_cost.toFixed(2);
        document.getElementById('routeTotalCost').textContent = '€' + data.total_cost.toFixed(2);

        var minNote = document.getElementById('routeMinNote');
        if (minNote) {
          minNote.style.display = (data.fuel_cost + data.toll_cost) < 50 ? 'block' : 'none';
        }

        window._quoteRouteData = data;

        drawRoutePreview(data, pCoords, dCoords);
      })
      .catch(function () {
        routeSummary.classList.remove('quote-route-summary--loading');
        document.getElementById('routeDistance').textContent = 'Errore';
        document.getElementById('routeDuration').textContent = 'Riprova più tardi';
      });
  }

  function drawRoutePreview(data, origin, destination) {
    if (!routePreviewDiv) return;

    if (!routePreviewMap) {
      routePreviewMap = new google.maps.Map(routePreviewDiv, {
        center: { lat: 41.9028, lng: 12.4964 },
        zoom: 6,
        disableDefaultUI: true,
        gestureHandling: 'cooperative',
        styles: [
          { featureType: 'poi', stylers: [{ visibility: 'off' }] },
          { featureType: 'transit', stylers: [{ visibility: 'off' }] }
        ]
      });
    }

    if (window._quoteRoutePolyline) {
      window._quoteRoutePolyline.setMap(null);
    }
    if (window._quoteRouteMarkers) {
      window._quoteRouteMarkers.forEach(function (m) { m.setMap(null); });
    }
    window._quoteRouteMarkers = [];

    if (data.polyline) {
      var path = google.maps.geometry.encoding.decodePath(data.polyline);
      window._quoteRoutePolyline = new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: '#0284c7',
        strokeOpacity: 0.8,
        strokeWeight: 4
      });
      window._quoteRoutePolyline.setMap(routePreviewMap);

      var bounds = new google.maps.LatLngBounds();
      path.forEach(function (p) { bounds.extend(p); });
      routePreviewMap.fitBounds(bounds, 30);
    }

    var markerA = new google.maps.Marker({
      position: origin,
      map: routePreviewMap,
      label: { text: 'A', color: '#fff', fontWeight: '700' },
      title: 'Ritiro'
    });
    window._quoteRouteMarkers.push(markerA);

    var markerB = new google.maps.Marker({
      position: destination,
      map: routePreviewMap,
      label: { text: 'B', color: '#fff', fontWeight: '700' },
      title: 'Consegna'
    });
    window._quoteRouteMarkers.push(markerB);
  }
}
