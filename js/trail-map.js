var map = L.map('trail-map').setView([51.505, -0.09], 13);

L.tileLayer('http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png', {
	attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
}).addTo(map);

var gpx_url = data.gpx_file;
new L.GPX(gpx_url, {
	async: true,
 	marker_options: {
        startIconUrl: 'http://github.com/mpetazzoni/leaflet-gpx/raw/master/pin-icon-start.png',
        endIconUrl:   'http://github.com/mpetazzoni/leaflet-gpx/raw/master/pin-icon-end.png',
        shadowUrl:    'http://github.com/mpetazzoni/leaflet-gpx/raw/master/pin-shadow.png',
      },
  }).on('loaded', function(e) {
  	var gpx = e.target;
  	map.fitBounds(e.target.getBounds());
}).addTo(map);
