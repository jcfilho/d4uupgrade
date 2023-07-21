var mapPoint = function(latti,lonti,intZoom)
{
	this.lat = latti;
	this.lon = lonti;
	this.zoom = intZoom;
	this.stockkhom = new google.maps.LatLng(this.lat,this.lon);
	this.mapOption = {
		zoom : 5,
		scrollwheel: false,
		scaleControl: false,
		center : this.stockkhom,
		zoomControlOptions: {
			position: google.maps.ControlPosition.TOP_LEFT
		}
	}
	this.map = new google.maps.Map(document.getElementById("booking-map-canvas"),this.mapOption);
	//add maker new google.maps.Marker
	this.marker = new google.maps.Marker({
		map : this.map,
		position : this.map.getCenter()
	});
}