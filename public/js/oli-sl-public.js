(function () {
	'use strict';

	var settings = window.oliSLSettings || {};
	var googleMapsReady = false;
	var pendingInits = [];

	window.oliSLGoogleMapsReady = function () {
		googleMapsReady = true;
		pendingInits.forEach(function (fn) {
			fn();
		});
		pendingInits = [];
	};

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function escapeHtml(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function clusterIconUrl(color, textColor) {
		var svg =
			'<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><circle cx="20" cy="20" r="18" fill="' +
			escapeHtml(color) +
			'" /><text x="20" y="25" text-anchor="middle" fill="' +
			escapeHtml(textColor) +
			'" font-size="14" font-family="sans-serif">CLUSTER</text></svg>';
		return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
	}

	function directionsUrl(lat, lng) {
		return 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat + ',' + lng);
	}

	function linkTarget() {
		return settings.openLinksNewWindow ? ' target="_blank" rel="noopener noreferrer"' : '';
	}

	function applyTemplate(tpl, store, labels) {
		var unit = store.unit === 'mi' ? labels.mi : labels.km;
		var status = store.hours_status === 'open' ? labels.open : store.hours_status === 'closed' ? labels.closed : '';
		var map = {
			title: store.title || '',
			address: store.address || '',
			city: store.city || '',
			phone: store.phone || '',
			hours: store.hours_display || store.hours || '',
			distance: store.distance ? store.distance + ' ' + unit : '',
			url: store.url || '',
			permalink: store.permalink || '',
			status: status
		};
		return tpl.replace(/\{\{(\w+)\}\}/g, function (_, key) {
			return escapeHtml(map[key] || '');
		});
	}

	function storeHtml(store, labels) {
		if (settings.resultsTemplate) {
			return '<article class="oli-sl-result' + (store.featured ? ' is-featured' : '') + '" data-id="' + store.id + '">' + applyTemplate(settings.resultsTemplate, store, labels) + '</article>';
		}
		var unit = store.unit === 'mi' ? labels.mi : labels.km;
		var parts = [store.address, store.city, store.state, store.zip];
		if (!settings.hideCountry) {
			parts.push(store.country);
		}
		var html = '<article class="oli-sl-result' + (store.featured ? ' is-featured' : '') + '" data-id="' + store.id + '">';
		var title = escapeHtml(store.title);
		if (settings.clickableStoreName && (store.url || (settings.enableSingle && store.permalink))) {
			title = '<a href="' + escapeHtml(store.url || store.permalink) + '"' + linkTarget() + '>' + title + '</a>';
		}
		html += '<h3>' + title + '</h3>';
		if (store.hours_status && settings.showHourStatus) {
			html += '<div class="oli-sl-status-' + escapeHtml(store.hours_status) + '">' + escapeHtml(store.hours_status === 'open' ? labels.open : labels.closed) + '</div>';
		}
		if (store.distance && !settings.hideDistance) {
			html += '<div class="oli-sl-result-distance">' + escapeHtml(store.distance) + ' ' + escapeHtml(unit) + '</div>';
		}
		html += '<div>' + escapeHtml(parts.filter(Boolean).join(', ')) + '</div>';
		if (settings.showCategories && store.categories && store.categories.length) {
			html += '<div>';
			store.categories.forEach(function (name) {
				var color = store.category_colors && store.category_colors[name];
				html += (color ? '<span class="oli-sl-cat-dot" style="background:' + escapeHtml(color) + '"></span>' : '') + escapeHtml(name) + ' ';
			});
			html += '</div>';
		}
		if (store.phone) {
			var phone = settings.clickablePhone || settings.clickableContact ? '<a href="tel:' + escapeHtml(store.phone) + '">' + escapeHtml(store.phone) + '</a>' : escapeHtml(store.phone);
			html += '<div>' + escapeHtml(labels.phone) + ': ' + phone + '</div>';
		}
		if (store.hours_display || store.hours) {
			html += '<div>' + escapeHtml(labels.hours) + ': ' + escapeHtml(store.hours_display || store.hours) + '</div>';
		}
		if (store.url) {
			html += '<div><a href="' + escapeHtml(store.url) + '"' + linkTarget() + '>' + escapeHtml(labels.website) + '</a></div>';
		}
		if (settings.directions) {
			html += '<div><a class="oli-sl-directions" href="' + directionsUrl(store.latitude, store.longitude) + '"' + linkTarget() + '>' + escapeHtml(labels.directions) + '</a></div>';
		}
		if (settings.enableSingle && store.permalink) {
			html += '<div><a href="' + escapeHtml(store.permalink) + '">' + escapeHtml(labels.moreDetails) + '</a></div>';
		}
		html += '</article>';
		return html;
	}

	function infoHtml(store, labels) {
		if (settings.infowindowTemplate) {
			return '<div class="oli-sl-infowindow">' + applyTemplate(settings.infowindowTemplate, store, labels) + '</div>';
		}
		var parts = [store.address, store.city, store.state, store.zip];
		if (!settings.hideCountry) {
			parts.push(store.country);
		}
		var html = '<div class="oli-sl-infowindow">';
		if (settings.infowindowThumbnail && store.thumbnail) {
			html += '<img src="' + escapeHtml(store.thumbnail) + '" alt="" />';
		}
		html += '<h3>' + escapeHtml(store.title) + '</h3><p>' + escapeHtml(parts.filter(Boolean).join(', ')) + '</p>';
		if (store.phone) {
			html += '<p>' + escapeHtml(store.phone) + '</p>';
		}
		if (settings.directions) {
			html += '<p><a href="' + directionsUrl(store.latitude, store.longitude) + '"' + linkTarget() + '>' + escapeHtml(labels.directions) + '</a></p>';
		}
		if (settings.showStreetView) {
			html += '<p><a href="https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' + encodeURIComponent(store.latitude + ',' + store.longitude) + '"' + linkTarget() + '>' + escapeHtml(labels.streetView) + '</a></p>';
		}
		html += '</div>';
		return html;
	}

	function request(action, data) {
		var body = new URLSearchParams();
		body.set('action', action);
		body.set('nonce', settings.nonce || '');
		Object.keys(data).forEach(function (key) {
			if (Array.isArray(data[key])) {
				data[key].forEach(function (val) {
					body.append(key + '[]', val);
				});
			} else if (data[key] !== undefined && data[key] !== null && data[key] !== '') {
				body.set(key, data[key]);
			}
		});
		return fetch(settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (res) {
			return res.json();
		});
	}

	function getTileLayer() {
		var providers = settings.tileProviders || {};
		var key = settings.leafletTiles || 'osm'; // stored OpenStreetMap tile key
		if (key === 'custom' && settings.leafletCustomUrl) {
			return { url: settings.leafletCustomUrl, attribution: '' };
		}
		return providers[key] || providers.osm || { url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', attribution: '&copy; OpenStreetMap' };
	}

	function Locator(el) {
		this.el = el;
		this.form = el.querySelector('.oli-sl-search');
		this.mapEl = el.querySelector('.oli-sl-map');
		this.resultsEl = el.querySelector('.oli-sl-results');
		this.addressInput = el.querySelector('.oli-sl-address-input');
		this.autoList = el.querySelector('.oli-sl-autocomplete');
		this.markers = [];
		this.cluster = null;
		this.circle = null;
		this.origin = { lat: settings.startLat, lng: settings.startLng };
		if (el.getAttribute('data-start-lat')) {
			this.origin.lat = parseFloat(el.getAttribute('data-start-lat'));
		}
		if (el.getAttribute('data-start-lng')) {
			this.origin.lng = parseFloat(el.getAttribute('data-start-lng'));
		}
		if (el.getAttribute('data-zoom')) {
			settings.startZoom = parseInt(el.getAttribute('data-zoom'), 10);
		}
		if (el.getAttribute('data-directions') === 'false') {
			settings.directions = false;
		}
		this.searchMethod = el.getAttribute('data-search-method') || settings.searchMethod || 'geocode';
		this.consented = !settings.gdprConsent;
		if (this.consented) {
			this.initMap();
		}
		this.bind();
		if (settings.queryAddress && this.addressInput) {
			this.addressInput.value = settings.queryAddress;
		}
		if (settings.queryName && this.addressInput && this.searchMethod === 'name') {
			this.addressInput.value = settings.queryName;
		}
		if (this.consented && settings.searchOnLoad && !settings.hideMapUntilSearch) {
			this.search({});
		} else if (this.addressInput && this.addressInput.value) {
			this.searchFromForm();
		}
		if (this.consented) {
			this.initSingleMaps();
		}
		if (settings.mouseFocus && this.addressInput && window.matchMedia && !window.matchMedia('(max-width: 782px)').matches) {
			this.addressInput.focus();
		}
	}

	Locator.prototype.bind = function () {
		var self = this;
		if (this.form) {
			this.form.addEventListener('submit', function (e) {
				e.preventDefault();
				self.searchFromForm();
			});
			this.form.addEventListener('reset', function () {
				setTimeout(function () {
					self.origin = { lat: settings.startLat, lng: settings.startLng };
					self.search({});
				}, 0);
			});
		}
		var locate = this.el.querySelector('.oli-sl-locate-btn');
		if (locate && navigator.geolocation) {
			locate.addEventListener('click', function () {
				navigator.geolocation.getCurrentPosition(
					function (pos) {
						self.origin = { lat: pos.coords.latitude, lng: pos.coords.longitude };
						if (self.addressInput) {
							self.addressInput.value = '';
						}
						var data = self.formSearchData();
						data.lat = self.origin.lat;
						data.lng = self.origin.lng;
						data.address = '';
						delete data.name;
						self.search(data);
					},
					function () {
						self.setStatus(settings.labels.geoDenied);
					}
				);
			});
		}
		var consentBtn = this.el.querySelector('.oli-sl-consent-btn');
		if (consentBtn) {
			consentBtn.addEventListener('click', function () {
				self.consented = true;
				var banner = self.el.querySelector('.oli-sl-consent');
				if (banner) {
					banner.hidden = true;
				}
				self.initMap();
				self.initSingleMaps();
				if (settings.searchOnLoad) {
					self.search({});
				}
			});
		}
		if (settings.autocomplete && this.addressInput) {
			var timer = null;
			this.addressInput.addEventListener('input', function () {
				clearTimeout(timer);
				var q = self.addressInput.value;
				if (q.length < 3) {
					self.hideAutocomplete();
					return;
				}
				timer = setTimeout(function () {
					request('oli_sl_autocomplete', { query: q }).then(function (json) {
						if (json && json.success) {
							self.showAutocomplete(json.data || []);
						}
					});
				}, 300);
			});
		}
	};

	Locator.prototype.formSearchData = function () {
		var radiusEl = this.el.querySelector('.oli-sl-radius');
		var maxEl = this.el.querySelector('.oli-sl-max-results');
		var data = {
			address: this.addressInput ? this.addressInput.value : '',
			radius: radiusEl && radiusEl.value ? radiusEl.value : String(settings.searchRadius || ''),
			max_results: maxEl && maxEl.value ? maxEl.value : String(settings.maxResults || '')
		};
		var catSelect = this.el.querySelector('.oli-sl-category');
		var catBoxes = this.el.querySelectorAll('input[name="oli_sl_category[]"]:checked');
		if (catSelect && catSelect.value) {
			data.category = catSelect.value;
		} else if (catBoxes.length) {
			data.category = Array.prototype.map.call(catBoxes, function (box) {
				return box.value;
			});
		}
		if (this.searchMethod === 'name') {
			data.name = data.address;
		}
		return data;
	};

	Locator.prototype.searchFromForm = function () {
		var data = this.formSearchData();
		if (this.origin && !data.address && this.searchMethod !== 'name') {
			data.lat = this.origin.lat;
			data.lng = this.origin.lng;
		}
		this.search(data);
	};

	Locator.prototype.search = function (data) {
		var self = this;
		this.el.classList.add('oli-sl-has-searched');
		this.setStatus(settings.labels.loading);
		if (!data.radius) {
			var radiusEl = this.el.querySelector('.oli-sl-radius');
			data.radius = radiusEl && radiusEl.value ? radiusEl.value : String(settings.searchRadius || '');
		}
		if (this.searchMethod !== 'name' && !data.lat && !data.lng && !data.address && !data.name) {
			data.lat = this.origin.lat || settings.startLat;
			data.lng = this.origin.lng || settings.startLng;
		}
		request('oli_sl_search_stores', data).then(function (json) {
			if (!json || !json.success) {
				self.setStatus((json && json.data && json.data.message) || settings.labels.noResults);
				return;
			}
			self.origin = { lat: json.data.center_lat || settings.startLat, lng: json.data.center_lng || settings.startLng };
			self.renderResults(json.data.stores || [], data.radius);
		});
	};

	Locator.prototype.setStatus = function (text) {
		if (this.resultsEl) {
			this.resultsEl.innerHTML = '<div class="oli-sl-status">' + escapeHtml(text) + '</div>';
		}
	};

	Locator.prototype.renderResults = function (stores, radius) {
		var self = this;
		var labels = settings.labels || {};
		if (!stores.length) {
			var empty = labels.noResults;
			if (labels.adjustSearch) {
				empty += ' ' + labels.adjustSearch;
			}
			this.setStatus(empty);
			this.setMarkers([]);
			return;
		}
		if (settings.showNumberResults) {
			var countEl = this.el.querySelector('.oli-sl-count');
			if (countEl) {
				var tpl = stores.length === 1 ? labels.numberResult : labels.numberResults;
				countEl.hidden = false;
				countEl.textContent = (tpl || '{number}').replace('{number}', String(stores.length));
			}
		}
		this.resultsEl.innerHTML = stores.map(function (store) {
			return storeHtml(store, labels);
		}).join('');
		this.resultsEl.querySelectorAll('.oli-sl-result').forEach(function (item, index) {
			item.addEventListener('click', function () {
				self.focusStore(stores[index]);
			});
			item.addEventListener('mouseenter', function () {
				if (settings.markerEffect === 'ignore') {
					return;
				}
				if (settings.markerEffect === 'info_window') {
					self.focusStore(stores[index]);
					return;
				}
				self.bounce(stores[index]);
			});
		});
		this.setMarkers(stores, radius);
	};

	Locator.prototype.showAutocomplete = function (items) {
		var self = this;
		if (!this.autoList) {
			return;
		}
		if (!items.length) {
			this.hideAutocomplete();
			return;
		}
		this.autoList.hidden = false;
		this.autoList.innerHTML = items
			.map(function (item) {
				return '<li data-lat="' + escapeHtml(item.lat || '') + '" data-lng="' + escapeHtml(item.lng || '') + '">' + escapeHtml(item.label || item.value) + '</li>';
			})
			.join('');
		this.autoList.querySelectorAll('li').forEach(function (li) {
			li.addEventListener('click', function () {
				self.addressInput.value = li.textContent;
				if (li.getAttribute('data-lat')) {
					self.origin = { lat: parseFloat(li.getAttribute('data-lat')), lng: parseFloat(li.getAttribute('data-lng')) };
				}
				self.hideAutocomplete();
				if (settings.autosubmit) {
					self.searchFromForm();
				}
			});
		});
	};

	Locator.prototype.hideAutocomplete = function () {
		if (this.autoList) {
			this.autoList.hidden = true;
			this.autoList.innerHTML = '';
		}
	};

	Locator.prototype.initMap = function () {
		if (!this.mapEl) {
			return;
		}
		if (settings.provider === 'google') {
			var self = this;
			var start = function () {
				self.initGoogle();
			};
			if (googleMapsReady || (window.google && window.google.maps)) {
				start();
			} else {
				pendingInits.push(start);
			}
		} else if (window.L) {
			this.initLeaflet();
		}
	};

	Locator.prototype.initLeaflet = function () {
		var tiles = getTileLayer();
		this.map = window.L.map(this.mapEl, {
			scrollWheelZoom: !!settings.scrollWheel,
			zoomControl: !!settings.mapControls
		}).setView([settings.startLat, settings.startLng], settings.startZoom);
		window.L.tileLayer(tiles.url, { attribution: tiles.attribution }).addTo(this.map);
		if (settings.clustering && window.L.markerClusterGroup) {
			this.cluster = window.L.markerClusterGroup({
				maxClusterRadius: settings.clusterSize || 80,
				disableClusteringAtZoom: settings.clusterMaxZoom || 16,
				iconCreateFunction: function (cluster) {
					var color = settings.clusterColor || '#0073aa';
					var text = settings.clusterTextColor || '#fff';
					return window.L.divIcon({
						html: '<div style="background:' + color + ';color:' + text + ';border-radius:50%;width:36px;height:36px;line-height:36px;text-align:center;">' + cluster.getChildCount() + '</div>',
						className: 'oli-sl-cluster',
						iconSize: [36, 36]
					});
				}
			});
			this.map.addLayer(this.cluster);
		}
	};

	Locator.prototype.initGoogle = function () {
		var styles = [];
		try {
			if (settings.mapStylePreset === 'custom' && settings.mapStyleJson) {
				styles = JSON.parse(settings.mapStyleJson);
			} else if (settings.googleStyles && settings.googleStyles[settings.mapStylePreset]) {
				styles = settings.googleStyles[settings.mapStylePreset];
			}
		} catch (e) {
			styles = [];
		}
		this.map = new google.maps.Map(this.mapEl, {
			center: { lat: settings.startLat, lng: settings.startLng },
			zoom: settings.startZoom,
			mapTypeId: settings.mapType || 'roadmap',
			scrollwheel: !!settings.scrollWheel,
			disableDefaultUI: !settings.mapControls,
			streetViewControl: !!settings.streetView,
			styles: styles
		});
		this.info = new google.maps.InfoWindow();
		if (settings.clustering && window.MarkerClusterer) {
			this.cluster = new window.MarkerClusterer(this.map, [], {
				imagePath: '',
				styles: [
					{
						url: clusterIconUrl(settings.clusterColor || '#0073aa', settings.clusterTextColor || '#ffffff'),
						height: 40,
						width: 40,
						textColor: settings.clusterTextColor || '#ffffff',
						textSize: 12
					}
				]
			});
		}
	};

	Locator.prototype.clearMarkers = function () {
		if (this.cluster && this.cluster.clearMarkers) {
			this.cluster.clearMarkers();
		}
		if (this.cluster && this.cluster.clearLayers) {
			this.cluster.clearLayers();
		}
		this.markers.forEach(function (marker) {
			if (marker.setMap) {
				marker.setMap(null);
			}
			if (marker.remove) {
				marker.remove();
			}
		});
		this.markers = [];
		if (this.circle) {
			if (this.circle.setMap) {
				this.circle.setMap(null);
			}
			if (this.circle.remove) {
				this.circle.remove();
			}
			this.circle = null;
		}
	};

	Locator.prototype.setMarkers = function (stores, radius) {
		this.clearMarkers();
		if (settings.provider === 'google' && this.map && window.google) {
			this.setGoogleMarkers(stores, radius);
		} else if (this.map && window.L) {
			this.setLeafletMarkers(stores, radius);
		}
	};

	Locator.prototype.setLeafletMarkers = function (stores, radius) {
		var self = this;
		var bounds = window.L.latLngBounds([]);
		if (!settings.hideStartMarker && this.origin) {
			var start = window.L.marker([this.origin.lat, this.origin.lng], { zIndexOffset: settings.startMarkerOnTop ? 1000 : 0, title: (settings.labels && settings.labels.startLocation) || '' });
			if (!(settings.clustering && settings.clusterExcludeStart === false)) {
				start.addTo(this.map);
			} else if (this.cluster) {
				this.cluster.addLayer(start);
			} else {
				start.addTo(this.map);
			}
			this.markers.push(start);
		}
		stores.forEach(function (store) {
			var latlng = [parseFloat(store.latitude), parseFloat(store.longitude)];
			var opts = {};
			if (store.marker_icon) {
				opts.icon = window.L.icon({ iconUrl: store.marker_icon, iconSize: [25, 41], iconAnchor: [12, 41] });
			} else if (settings.defaultMarker) {
				opts.icon = window.L.icon({ iconUrl: settings.defaultMarker, iconSize: [25, 41], iconAnchor: [12, 41] });
			}
			var marker = window.L.marker(latlng, opts);
			marker.bindPopup(infoHtml(store, settings.labels || {}));
			if (self.cluster) {
				self.cluster.addLayer(marker);
			} else {
				marker.addTo(self.map);
			}
			self.markers.push(marker);
			store._marker = marker;
			bounds.extend(latlng);
		});
		if (settings.fitBounds && stores.length) {
			this.map.fitBounds(bounds.pad(0.15));
		}
		if (settings.drawRadius && radius && this.origin) {
			var meters = (settings.unit === 'mi' ? radius * 1609.34 : radius * 1000);
			this.circle = window.L.circle([this.origin.lat, this.origin.lng], { radius: meters, color: settings.clusterColor || '#0073aa', fillOpacity: 0.05 });
			this.circle.addTo(this.map);
		}
	};

	Locator.prototype.setGoogleMarkers = function (stores, radius) {
		var self = this;
		var bounds = new google.maps.LatLngBounds();
		stores.forEach(function (store) {
			var pos = { lat: parseFloat(store.latitude), lng: parseFloat(store.longitude) };
			var marker = new google.maps.Marker({
				position: pos,
				map: self.cluster ? null : self.map,
				title: store.title,
				icon: store.marker_icon || settings.defaultMarker || undefined
			});
			marker.addListener('click', function () {
				self.info.setContent(infoHtml(store, settings.labels || {}));
				self.info.open(self.map, marker);
				if (settings.inMapDirections && self.origin) {
					self.drawGoogleDirections(self.origin, pos);
				}
			});
			self.markers.push(marker);
			store._marker = marker;
			bounds.extend(pos);
		});
		if (this.cluster && this.cluster.addMarkers) {
			this.cluster.addMarkers(this.markers);
		}
		if (settings.fitBounds && stores.length) {
			this.map.fitBounds(bounds);
		}
		if (settings.drawRadius && radius && this.origin) {
			var meters = settings.unit === 'mi' ? radius * 1609.34 : radius * 1000;
			this.circle = new google.maps.Circle({
				map: this.map,
				center: this.origin,
				radius: meters,
				strokeColor: settings.clusterColor || '#0073aa',
				fillOpacity: 0.05
			});
		}
	};

	Locator.prototype.drawGoogleDirections = function (origin, dest) {
		if (!window.google || !google.maps.DirectionsService) {
			return;
		}
		var self = this;
		if (!this.directions) {
			this.directions = new google.maps.DirectionsRenderer({ map: this.map });
			this.service = new google.maps.DirectionsService();
		}
		this.service.route(
			{
				origin: origin,
				destination: dest,
				travelMode: google.maps.TravelMode.DRIVING
			},
			function (result, status) {
				if (status === 'OK') {
					self.directions.setDirections(result);
				}
			}
		);
	};

	Locator.prototype.focusStore = function (store) {
		if (!store || !this.map) {
			return;
		}
		var lat = parseFloat(store.latitude);
		var lng = parseFloat(store.longitude);
		if (this.map.setView) {
			this.map.setView([lat, lng], Math.max(this.map.getZoom(), 14));
			if (store._marker && store._marker.openPopup) {
				store._marker.openPopup();
			}
		} else if (this.map.setCenter) {
			this.map.setCenter({ lat: lat, lng: lng });
			this.map.setZoom(Math.max(this.map.getZoom(), 14));
			if (this.info && store._marker) {
				this.info.setContent(infoHtml(store, settings.labels || {}));
				this.info.open(this.map, store._marker);
			}
		}
	};

	Locator.prototype.bounce = function (store) {
		if (!store || !store._marker) {
			return;
		}
		if (store._marker.setAnimation && window.google) {
			store._marker.setAnimation(google.maps.Animation.BOUNCE);
			setTimeout(function () {
				store._marker.setAnimation(null);
			}, 700);
		}
	};

	Locator.prototype.initSingleMaps = function () {
		document.querySelectorAll('.oli-sl-single-map').forEach(function (el) {
			var lat = parseFloat(el.getAttribute('data-lat'));
			var lng = parseFloat(el.getAttribute('data-lng'));
			if (!lat || !lng) {
				return;
			}
			if (settings.provider === 'google' && window.google && google.maps) {
				var map = new google.maps.Map(el, { center: { lat: lat, lng: lng }, zoom: 15 });
				new google.maps.Marker({ position: { lat: lat, lng: lng }, map: map });
			} else if (window.L) {
				var lmap = window.L.map(el).setView([lat, lng], 15);
				var tiles = getTileLayer();
				window.L.tileLayer(tiles.url, { attribution: tiles.attribution }).addTo(lmap);
				window.L.marker([lat, lng]).addTo(lmap);
			}
		});
	};

	ready(function () {
		document.querySelectorAll('.oli-sl-wrap').forEach(function (el) {
			new Locator(el);
		});
		if (!document.querySelector('.oli-sl-wrap')) {
			var dummy = { initSingleMaps: Locator.prototype.initSingleMaps, mapEl: null };
			Locator.prototype.initSingleMaps.call(dummy);
		}
	});
})();
