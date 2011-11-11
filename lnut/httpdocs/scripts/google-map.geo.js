/*!
 * geo-location-javascript v0.4.1 
 * http://code.google.com/p/geo-location-javascript/
 *
 * Copyright (c) 2009 Stan Wiechers
 * Licensed under the MIT licenses.
 *
 * Revision: $Rev: 66 $: 
 * Author: $Author: whoisstan $:
 * Date: $Date: 2010-01-10 08:27:48 -0500 (Sun, 10 Jan 2010) $:    
 */
	var bb_successCallback;
	var bb_errorCallback;

	function handleBlackBerryLocation() {
		if (bb_successCallback && bb_errorCallback) {
			if(0==blackberry.location.latitude && 0==blackberry.location.longitude) {
				// http://dev.w3.org/geo/api/spec-source.html#position_unavailable_error
				// POSITION_UNAVAILABLE (numeric value 2)
				bb_errorCallback({message:"Position unavailable", code:2});
			} else {
				var timestamp=null;
				// only available with 4.6 and later
				// http://na.blackberry.com/eng/deliverables/8861/blackberry_location_568404_11.jsp
				if (blackberry.location.timestamp) {
					timestamp=new Date(blackberry.location.timestamp);
				}
				bb_successCallback({timestamp:timestamp, coords: {latitude:blackberry.location.latitude,longitude:blackberry.location.longitude}});
			}
			// If you have passed the method a function, you can cancel the callback using blackberry.location.removeLocationUpdate(). 
			// If you have passed a string, the callback cannot be removed.
			// http://docs.blackberry.com/en/developers/deliverables/11849/blackberry_location_onLocationUpdate_568407_11.jsp
			if(4.6 <= parseFloat(navigator.appVersion)) {
				blackberry.location.removeLocationUpdate();
			}

			bb_successCallback = null;
			bb_errorCallback = null;
		}
	}

	var geo_position_js=function() {
		var pub = {};
		var provider=null;

		pub.getCurrentPosition = function(successCallback,errorCallback,options){
			provider.getCurrentPosition(successCallback, errorCallback,options);
		}

		pub.init = function() {
			try {
				if ("undefined" != typeof(geo_position_js_simulator)) {
					provider = geo_position_js_simulator;
				} else if ("undefined" != typeof(bondi) && "undefined" != typeof(bondi.geolocation)) {
					provider = bondi.geolocation;
				} else if ("undefined" != typeof(window.google) && "undefined" != typeof(window.google.gears)) {
					provider = google.gears.factory.create('beta.geolocation');
					pub.getCurrentPosition = function(successCallback, errorCallback, options){
						try{
							function _successCallback(p){
								if ("undefined" != typeof(p.latitude)) {
									successCallback({
										timestamp: p.timestamp,
										coords: {
											latitude: p.latitude,
											longitude: p.longitude
										}
									});
								} else {
									successCallback(p);
								}
							}
							provider.getCurrentPosition(_successCallback, errorCallback, options);
						} catch(e) {
							// this is thrown when the request is denied
							errorCallback({message:e,code:1});
						}
					}
				} else if ("undefined" != typeof(navigator.geolocation)) {
					provider = navigator.geolocation;
					pub.getCurrentPosition = function(successCallback, errorCallback, options) {
						function _successCallback(p){
							// for mozilla geode,it returns the coordinates slightly differently
							if ("undefined" != typeof(p.latitude)) {
								successCallback({
									timestamp: p.timestamp,
									coords: {
										latitude: p.latitude,
										longitude: p.longitude
									}
								});
							} else {
								successCallback(p);
							}
						}
						provider.getCurrentPosition(_successCallback, errorCallback, options);
					}
					pub.watchPosition = function(successCallback,errorCallback,options){
						try {
							provider.watchPosition(successCallback,errorCallback,options);

							pub.clearWatch = function(watchId){
								if("undefined" != typeof(provider.clearWatch)) {// Should always be true, but just in case
									provider.clearWatch(watchId);
								}
							}
						} catch(e) {
							// thrown when method not available
							errorCallback({message:e,code:1});
						}
					}
				} else if ("undefined" != typeof(Mojo) && "undefined" != typeof(Mojo.Service) && "Mojo.Service.Request" != typeof(Mojo.Service.Request)) {
					provider = true;
					pub.getCurrentPosition = function(successCallback, errorCallback, options){
						parameters = {};
						if (options) {
							// http://developer.palm.com/index.php?option=com_content&view=article&id=1673#GPS-getCurrentPosition
							if (options.enableHighAccuracy && options.enableHighAccuracy == true) {
								parameters.accuracy = 1;
							}
							if (options.maximumAge) {
								parameters.maximumAge = options.maximumAge;
							}
							if (options.responseTime) {
								if (5 > options.responseTime) {
									parameters.responseTime = 1;
								} else {
									if (20 > options.responseTime) {
										parameters.responseTime = 2;
									} else {
										parameters.timeout = 3;
									}
								}
							}
						}

						r = new Mojo.Service.Request(
							'palm://com.palm.location', {
								method: "getCurrentPosition",
								parameters: parameters,
								onSuccess: function(p){
									successCallback({
										timestamp: p.timestamp,
										coords: {
											latitude: p.latitude,
											longitude: p.longitude,
											heading: p.heading
										}
									});
								},
								onFailure: function(e) {
									if (1 == e.errorCode) {
										errorCallback({
											code: 3,
											message: "Timeout"
										});
									} else {
										if (2 == e.errorCode) {
											errorCallback({
												code: 2,
												message: "Position Unavailable"
											});
										}
										else {
											errorCallback({
												code: 0,
												message: "Unknown Error: webOS-code" + errorCode
											});
										}
									}
								}
							}
						);
					}
				} else if ("undefined" != typeof(device) && "undefined" != typeof(device.getServiceObject)) {
					provider = device.getServiceObject("Service.Location", "ILocation");

					// override default method implementation
					pub.getCurrentPosition = function(successCallback, errorCallback, options){
						function callback(transId, eventCode, result){
							if (4 == eventCode) {
								errorCallback({
									message: "Position unavailable",
									code: 2
								});
							} else {
								//no timestamp of location given?
								successCallback({
									timestamp: null,
									coords: {
										latitude: result.ReturnValue.Latitude,
										longitude: result.ReturnValue.Longitude,
										altitude: result.ReturnValue.Altitude,
										heading: result.ReturnValue.Heading
									}
								});
							}
						}
						// location criteria
						var criteria = new Object();
						criteria.LocationInformationClass = "BasicLocationInformation";

						// make the call
						provider.ILocation.GetLocation(criteria, callback);
					}
				} else if ("undefined" != typeof(window.blackberry) && blackberry.location.GPSSupported) {
					// set to autonomous mode
					blackberry.location.setAidMode(2);
					
					// override default method implementation
					pub.getCurrentPosition = function(successCallback, errorCallback, options){
						// passing over callbacks as parameter didn't work consistently 
						// in the onLocationUpdate method, thats why they have to be set
						// outside
						bb_successCallback = successCallback;
						bb_errorCallback = errorCallback;
						
						// http://docs.blackberry.com/en/developers/deliverables/11849/blackberry_location_onLocationUpdate_568407_11.jsp
						// On BlackBerry devices running versions of BlackBerry® Device Software  earlier than version 4.6,
						// this method must be passed as a string that is evaluated each time the location is refreshed. 
						// On BlackBerry devices running BlackBerry Device Software version 4.6 or later, you can pass a string, 
						// or use the method to register a callback function.
						if(4.6 <= parseFloat(navigator.appVersion)) {
							blackberry.location.onLocationUpdate(handleBlackBerryLocation());
						} else {
							blackberry.location.onLocationUpdate("handleBlackBerryLocation()");
						}
						blackberry.location.refreshLocation();
					}
					provider = blackberry.location;
				}
			} catch (e) {
				if ("undefined" != typeof(console)) {
					console.log(e);
				}
			}
			return  provider!=null;
		}

		return pub;
	}();

/**
 * Mapping Functions
 */
	var Lat=54, Lng=0, geolocated=false, Map, GeoCoder, MapListener=null;

	/**
	 * User's Location
	 */
	function locationMe () {
		GEvent.removeListener(MapListener);
		if(geolocated) {
			locationMePlot();
		} else {
			var yesno = confirm('Would you like to see if vouchers are available in your area?');
			if(yesno) {
				if(geo_position_js.init()) {
					geo_position_js.getCurrentPosition(
						locationMeSuccess,
						locationMeError,
						{enableHighAccuracy:true}
					);
				} else {
					// Functionality Not Available
					// Show a message to the user
					alert("Your location could not be found right now.\nPlease use the search form to find Kiosks near you.\nCode:Browser Unsupported");
				}
			} else {
				alert("To find kiosks near you, use the search form.");
			}
		}
	}
	function locationMeSuccess (p) {
		Lat = p.coords.latitude.toFixed(2);
		Lng = p.coords.longitude.toFixed(2);
		$.cookie('LatLng', Lat+','+Lng);
		geolocated = true;
		locationMePlot();
	}
	function locationMeError (p) {
		alert('Your location could not be found right now.\nPlease use the search form to find Kiosks near you.\nCode:'+((p.message.length > 0) ? p.message : 'No Location'));
	}
	function locationMePlot () {
		var Here = new GLatLng(Lat, Lng);
		var Me = new GMarker(Here);
		Map.addOverlay(Me);
		Map.setCenter(Here, 13);
		Map.panTo(Here);
	}

	/**
	 * Postcode Location
	 */
	function locationPostcodePlot (isKiosk, title, address, image, description, region) {
		var address_region = '';

		// kiosks have region information
		if(isKiosk) {
			address_region = address;
		} else {
			address_region = address+((region && null != region) ? '+'+region : '');
		}

		GeoCoder.getLatLng(
			address_region,
			function(point) {
				if (!point) {
					growlAlert(
						"No Search Results",
						"<p>Searched for Address '"+address_region+"', but could not find a match.</p><p>Please use the Search form to locate Kiosks.</p>",
						"",
						2000
					);
				} else {
					/**
					 * Centre the map to this location
					 */
					Map.clearOverlays();

					/**
					 * If the address is not a kiosk location
					 * show a user marker, otherwise show a kiosk marker
					 */
					if(isKiosk) {
						// show kiosk marker
						Map.setCenter(point, 5); // was 13

						var marker = new GMarker(point);
						Map.addOverlay(marker);

						// generate html content
						var content =	'<div class="info-bubble">'+
											'<h1>'+title+'</h1>'+
											'<div class="clearfix">'+
												'<div class="info-bubble-image">'+
													((image && image.length > 0) ? '<img src="'+image+'" alt="'+title+'" />' : '')+
												'</div>'+
												'<div class="info-bubble-address">'+
													description+
												'</div>'+
											'</div>'+
											'<div class="info-advertise"><a href="'+url+'advertise/">Advertise here</a></div>'+
										'</div>';
						marker.ssContent = content;
						marker.openInfoWindowHtml(content);
						$('.info-bubble-address a').click(function(){
							// track click?
						});

						// pan the map to the current position when the point is clicked
						GEvent.addListener(marker, "click", function(){
							Map.panTo(marker.getLatLng());
							marker.openInfoWindowHtml(marker.ssContent);
						});
						//GEvent.trigger(marker,'click');
					} else {
						// show user marker
						//var marker = new GMarker(point);
						//Map.addOverlay(marker);

						GeoCoder.getLocations(
							point,
							function (response) {
								// Retrieve the object
								place = response.Placemark[0];

								// Retrieve the latitude and longitude
								point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);

								var marker = new GMarker(point);
								// Center the map on this point
								Map.setCenter(point, 5); // was 13

								// Create a marker
								marker = new GMarker(point);

								// Add the marker to map
								Map.addOverlay(marker);

								// Add address information to marker
								marker.openInfoWindowHtml(place.address);
							}
						);

						// now find other kiosks in the area

						// reset the select box for kiosks so changing it again can re-select the kiosk
						$('#kiosk option:eq(0)').attr('selected','selected');
					}
				}
			}
		);
	}

	/**
	 * Map Handler should be called only when the map object is on the page
	 */
	function mapHandler () {
		/**
		 * Google Map
		 */
		if($('#map').length > 0) {
			/**
			 * Kiosk Plotter
			 */
			$('#kiosk').change(function(){
				// If there's a google map, geolocate the postcode and plot it on the map
				if($("#kiosk option:selected").val().length > 0) {
					var id = '#kiosk-'+$("#kiosk option:selected").val();
					locationPostcodePlot(
						true,
						$(id+'-marker').attr('title'),
						$(id+'-address').text(),
						$(id+'-description').attr('title'),
						$(id+'-description').html(),
						$("#region option:selected").val()
					);
				}
			});

			/**
			 * Kiosk Search
			 */
			$('#search').parent().submit(function(){
				return false;
			});
			$('#search').keyup(function(evt) {
				var code;
				if (!evt) {
					var evt = window.event;
				}
				code = (evt.keyCode ? evt.keyCode : (evt.which ? evt.which : code));

				var character = String.fromCharCode(code);

				if(code==9||code==13) {
					// tab || enter
					// submit the text for geocoding if the length > 0
					if($(this).val().length > 0) {
						var isKiosk		= false;
						var title		= $('#search').val();
						var address		= $('#search').val();
						var image		= null;
						var description	= null;
						var region		= $('#region option:selected').val();

						locationPostcodePlot(
							isKiosk,
							title,
							address,
							image,
							description,
							region
						);
					}
					return false;
				}
			});
			$('#submit').click(function(){
				if($('#search').val().length > 0) {
					var isKiosk		= false;
					var title		= $('#search').val();
					var address		= $('#search').val();
					var image		= null;
					var description	= null;
					var region		= $('#region option:selected').val();

					locationPostcodePlot(
						isKiosk,
						title,
						address,
						image,
						description,
						region
					);
				}
				return false;
			});

			if('function' == typeof(GBrowserIsCompatible)) {
				/**
				 * If cookies for the lat/lng exists, use the points in them
				 */
				if($.cookie('LatLng') && $.cookie('LatLng').length > 0) {
					Lat = $.cookie('LatLng').split(',')[0];
					Lng = $.cookie('LatLng').split(',')[1];
					geolocated = true;
				}

				/**
				 * Create the Map object on the #map element
				 */
				Map = new GMap2($('#map')[0]);

				/**
				 * Centre the map on the lat/lng at a default zoom of 5
				 */
				Map.setCenter(new GLatLng(Lat, Lng), 5);
				Map.setUIToDefault();
				Map.setMapType(G_NORMAL_MAP);

				/**
				 * Prepare the Geocoder for plotting postcodes with markers
				 */
				GeoCoder = new GClientGeocoder();

				/**
				 * Call the locator function 
				 */
				var loaded = false;
				MapListener = GEvent.addListener(Map, "tilesloaded", function() {
					if(false==loaded) {
						loaded=true;
						var kiosks = [];

						/**
						 * Fetch all the plottable kiosks
						 */
						$('#kiosk option').each(function(){
							if($(this).val().length > 0) {
								var id = '#kiosk-'+$(this).val();
								kiosks[kiosks.length] = {
									title:			$(id+'-marker').attr('title'),
									address:		$(id+'-address').text(),
									image:			$(id+'-description').attr('title'),
									description:	$(id+'-description').html()
								}
							}
						});

						/**
						 * Plot each kiosk on the map
						 */
						if(kiosks.length > 0) {
							for(var i=kiosks.length-1; i>=0; i--) {
								locationPostcodePlot(
									true,
									kiosks[i].title,
									kiosks[i].address,
									kiosks[i].image,
									kiosks[i].description,
									$("#region option:selected").val()
								);
							}
						} else {
							locationMe();
						}
					}
					return;
				});
			}
		}
	}