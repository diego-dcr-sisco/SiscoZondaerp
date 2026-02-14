// ===========================
// VARIABLES GLOBALES
// ===========================
let map;
let geocoder;
let markers = {};
let polygons = {}; // Polígonos por tipo de punto de control
let selectedDeviceId = null;
let devices = [];
let modifiedDevices = new Set();
let originalCoordinates = {};

const TOLERANCE = 0.00001; // Tolerancia para coordenadas duplicadas (~1 metro)

// ===========================
// INICIALIZACIÓN
// ===========================
function initMap(devicesData, customerAddress, customerCity, customerState) {
    devices = devicesData;
    geocoder = new google.maps.Geocoder();

    // Determinar centro del mapa
    getMapCenter(customerAddress, customerCity, customerState, function(center) {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 22,
            center: center,
            mapTypeId: 'satellite',
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            clickableIcons: false
        });

        // Event listener para colocar dispositivos
        map.addListener('click', function(event) {
            if (selectedDeviceId) {
                placeDeviceOnMap(selectedDeviceId, event.latLng);
            }
        });

        // Esperar a que el mapa esté completamente cargado
        google.maps.event.addListenerOnce(map, 'idle', function() {
            console.log('🗺️ Mapa completamente cargado');
            // Cargar dispositivos existentes
            loadExistingDevices();
            renderDevicesList();
            updateStatistics();
        });
    });
}

// ===========================
// FUNCIONES DE MAPA
// ===========================
function getMapCenter(address, city, state, callback) {
    // Buscar dispositivos con coordenadas existentes
    const geolocatedDevices = devices.filter(d => d.latitude && d.longitude);
    
    if (geolocatedDevices.length > 0) {
        // Calcular centro promedio
        const avgLat = geolocatedDevices.reduce((sum, d) => sum + parseFloat(d.latitude), 0) / geolocatedDevices.length;
        const avgLng = geolocatedDevices.reduce((sum, d) => sum + parseFloat(d.longitude), 0) / geolocatedDevices.length;
        callback({ lat: avgLat, lng: avgLng });
        return;
    }

    // Si no hay dispositivos geolocalizados, intentar geocodificar la dirección del cliente
    if (address) {
        const customerAddress = `${address}, ${city || ''}, ${state || ''}`;
        geocoder.geocode({ address: customerAddress }, function(results, status) {
            if (status === 'OK' && results[0]) {
                callback({
                    lat: results[0].geometry.location.lat(),
                    lng: results[0].geometry.location.lng()
                });
            } else {
                // Si falla la geocodificación, usar ubicación por defecto
                console.warn('No se pudo geocodificar la dirección del cliente:', status);
                callback({ lat: 19.4326, lng: -99.1332 });
            }
        });
    } else {
        // Por defecto: Ciudad de México
        callback({ lat: 19.4326, lng: -99.1332 });
    }
}

function loadExistingDevices() {
    console.log('📍 Cargando dispositivos existentes...');
    console.log('Total de dispositivos:', devices.length);
    
    let loadedCount = 0;
    devices.forEach(device => {
        if (device.latitude && device.longitude) {
            createMarker(device, parseFloat(device.latitude), parseFloat(device.longitude), false, false);
            originalCoordinates[device.id] = {
                lat: parseFloat(device.latitude),
                lng: parseFloat(device.longitude)
            };
            loadedCount++;
        }
    });
    
    console.log(`✅ ${loadedCount} dispositivos cargados con coordenadas`);
    
    // Actualizar polígonos una sola vez después de cargar todos los dispositivos
    updatePolygons();
}

function createMarker(device, lat, lng, isNew = false, shouldUpdatePolygons = true) {
    // Eliminar marcador existente si hay uno
    if (markers[device.id]) {
        markers[device.id].setMap(null);
    }

    const marker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        draggable: true,
        title: `${device.code} - ${device.control_point.name}`,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: device.control_point.color,
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            strokeWeight: 3
        },
        label: {
            text: device.nplan.toString(),
            color: '#FFFFFF',
            fontSize: '12px',
            fontWeight: 'bold'
        },
        zIndex: 200
    });

    // Info Window
    const infoWindow = new google.maps.InfoWindow({
        content: getInfoWindowContent(device, lat, lng)
    });

    marker.addListener('click', function() {
        infoWindow.open(map, marker);
    });

    // Event listener para drag
    marker.addListener('dragend', function(event) {
        const newLat = event.latLng.lat();
        const newLng = event.latLng.lng();

        // Validar coordenadas duplicadas
        if (isDuplicateLocation(newLat, newLng, device.id)) {
            alert('Ya existe un dispositivo en esta ubicación. Por favor, elige otra posición.');
            // Restaurar posición original
            if (originalCoordinates[device.id]) {
                marker.setPosition(originalCoordinates[device.id]);
            } else {
                marker.setMap(null);
                delete markers[device.id];
            }
            return;
        }

        // Actualizar dispositivo
        device.latitude = newLat;
        device.longitude = newLng;
        modifiedDevices.add(device.id);

        // Actualizar info window
        infoWindow.setContent(getInfoWindowContent(device, newLat, newLng));

        updateStatistics();
        renderDevicesList();
        updatePolygons();
    });

    markers[device.id] = marker;

    if (isNew) {
        modifiedDevices.add(device.id);
        map.panTo(marker.getPosition());
    }

    if (shouldUpdatePolygons) {
        updatePolygons();
    }
}

function getInfoWindowContent(device, lat, lng) {
    return `
        <div style="min-width: 200px;">
            <h6 style="color: ${device.control_point.color}; margin-bottom: 10px;">
                <strong>${device.code}</strong>
            </h6>
            <div class="small">
                <strong>Número:</strong> ${device.nplan}<br>
                <strong>Tipo:</strong> ${device.control_point.name}<br>
                <strong>Zona:</strong> ${device.application_area.name}<br>
                <hr class="my-2">
                <strong>Lat:</strong> ${lat.toFixed(6)}<br>
                <strong>Lng:</strong> ${lng.toFixed(6)}
            </div>
            <button class="btn btn-sm btn-danger mt-2 w-100" onclick="removeDeviceMarker(${device.id})">
                <i class="bi bi-trash"></i> Eliminar marcador
            </button>
        </div>
    `;
}

function placeDeviceOnMap(deviceId, latLng) {
    const device = devices.find(d => d.id === deviceId);
    if (!device) return;

    const lat = latLng.lat();
    const lng = latLng.lng();

    // Validar rango de coordenadas
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        alert('Coordenadas fuera de rango válido.');
        return;
    }

    // Validar coordenadas duplicadas
    if (isDuplicateLocation(lat, lng, deviceId)) {
        alert('Ya existe un dispositivo en esta ubicación. Por favor, elige otra posición.');
        return;
    }

    // Si ya tiene coordenadas, confirmar sobreescritura
    if (device.latitude && device.longitude) {
        if (!confirm(`El dispositivo ${device.code} ya tiene coordenadas.\n¿Deseas sobreescribirlas?`)) {
            return;
        }
    }

    // Actualizar dispositivo
    device.latitude = lat;
    device.longitude = lng;

    // Crear marcador
    createMarker(device, lat, lng, true);

    // Deseleccionar dispositivo
    selectedDeviceId = null;
    document.querySelectorAll('.device-item').forEach(item => {
        item.classList.remove('selected');
    });

    updateStatistics();
    renderDevicesList();
}

function removeDeviceMarker(deviceId) {
    if (!confirm('¿Deseas eliminar este marcador?')) return;

    const device = devices.find(d => d.id === deviceId);
    if (device) {
        device.latitude = null;
        device.longitude = null;
    }

    if (markers[deviceId]) {
        markers[deviceId].setMap(null);
        delete markers[deviceId];
    }

    modifiedDevices.add(deviceId);
    updateStatistics();
    renderDevicesList();
    updatePolygons();
}

function isDuplicateLocation(lat, lng, currentDeviceId) {
    return devices.some(d => {
        if (d.id === currentDeviceId) return false;
        if (!d.latitude || !d.longitude) return false;

        const latDiff = Math.abs(parseFloat(d.latitude) - lat);
        const lngDiff = Math.abs(parseFloat(d.longitude) - lng);

        return latDiff < TOLERANCE && lngDiff < TOLERANCE;
    });
}

// ===========================
// FUNCIONES DE POLÍGONOS
// ===========================
function calculateCentroid(points) {
    const sum = points.reduce((acc, point) => {
        return {
            lat: acc.lat + point.lat,
            lng: acc.lng + point.lng
        };
    }, { lat: 0, lng: 0 });

    return {
        lat: sum.lat / points.length,
        lng: sum.lng / points.length
    };
}

function sortPointsByAngle(points) {
    if (points.length < 3) return points;

    const centroid = calculateCentroid(points);

    return points.sort((a, b) => {
        const angleA = Math.atan2(a.lat - centroid.lat, a.lng - centroid.lng);
        const angleB = Math.atan2(b.lat - centroid.lat, b.lng - centroid.lng);
        return angleA - angleB;
    });
}

function updatePolygons() {
    console.log('🔷 Actualizando polígonos...');
    
    if (!map) {
        console.error('❌ Error: El mapa no está inicializado');
        return;
    }
    
    // Limpiar polígonos existentes
    Object.values(polygons).forEach(polygon => {
        polygon.setMap(null);
    });
    polygons = {};

    // Agrupar dispositivos por tipo de punto de control
    const devicesByType = {};

    devices.forEach(device => {
        if (device.latitude && device.longitude) {
            const typeId = device.type_control_point_id;
            
            if (!devicesByType[typeId]) {
                devicesByType[typeId] = {
                    devices: [],
                    color: device.control_point.color,
                    name: device.control_point.name
                };
            }

            devicesByType[typeId].devices.push({
                lat: parseFloat(device.latitude),
                lng: parseFloat(device.longitude)
            });
        }
    });

    console.log('📊 Dispositivos agrupados por tipo:', devicesByType);

    // Crear polígonos para cada tipo (si tiene al menos 3 puntos)
    Object.entries(devicesByType).forEach(([typeId, data]) => {
        console.log(`🔍 Tipo ${typeId} (${data.name}): ${data.devices.length} dispositivos`);
        
        if (data.devices.length >= 3) {
            const sortedPoints = sortPointsByAngle(data.devices);
            
            // Imprimir puntos con inicio y fin marcados
            console.log(`📐 Polígono para "${data.name}" (${data.devices.length} puntos):`);
            console.log('═══════════════════════════════════════');
            sortedPoints.forEach((point, index) => {
                if (index === 0) {
                    console.log(`🟢 INICIO [${index}]:`, `Lat: ${point.lat.toFixed(6)}, Lng: ${point.lng.toFixed(6)}`);
                } else if (index === sortedPoints.length - 1) {
                    console.log(`🔴 FIN [${index}]:`, `Lat: ${point.lat.toFixed(6)}, Lng: ${point.lng.toFixed(6)}`);
                } else {
                    console.log(`⚪ Punto [${index}]:`, `Lat: ${point.lat.toFixed(6)}, Lng: ${point.lng.toFixed(6)}`);
                }
            });
            console.log(`🔄 El polígono se cierra automáticamente conectando FIN con INICIO`);
            console.log('═══════════════════════════════════════');

            const polygon = new google.maps.Polygon({
                paths: sortedPoints,
                strokeColor: data.color,
                strokeOpacity: 0.8,
                strokeWeight: 3,
                fillColor: data.color,
                fillOpacity: 0.2,
                map: map,
                zIndex: 50,
                clickable: true,
                editable: false,
                draggable: false
            });

            console.log(`✅ Polígono creado y agregado al mapa para tipo ${typeId} (${data.name}) con ${data.devices.length} puntos`);
            console.log(`🎨 Color del polígono:`, data.color);

            // Info window para el polígono
            const infoWindow = new google.maps.InfoWindow();

            polygon.addListener('click', function(event) {
                const contentString = `
                    <div style="padding: 10px;">
                        <h6 style="color: ${data.color}; margin-bottom: 5px;">
                            <strong>${data.name}</strong>
                        </h6>
                        <p class="mb-0 small">
                            <i class="bi bi-geo-alt"></i> ${data.devices.length} dispositivo(s)<br>
                            <i class="bi bi-pentagon"></i> Perímetro del área
                        </p>
                    </div>
                `;

                infoWindow.setContent(contentString);
                infoWindow.setPosition(event.latLng);
                infoWindow.open(map);
            });

            polygons[typeId] = polygon;
        } else {
            console.log(`⚠️ Tipo ${typeId} (${data.name}): Solo ${data.devices.length} punto(s), se necesitan al menos 3 para crear polígono`);
        }
    });
    
    console.log(`🏁 Total de polígonos creados: ${Object.keys(polygons).length}`);
    console.log('📍 Polígonos en el mapa:', polygons);
}

// ===========================
// FUNCIONES DE UI
// ===========================
function renderDevicesList() {
    const container = document.getElementById('devices-list');
    const filterControlPoint = document.getElementById('filter-control-point').value;
    const filterArea = document.getElementById('filter-area').value;
    const filterStatus = document.querySelector('input[name="filter-status"]:checked').value;

    let filteredDevices = devices.filter(device => {
        if (filterControlPoint && device.type_control_point_id != filterControlPoint) return false;
        if (filterArea && device.application_area.id != filterArea) return false;
        if (filterStatus === 'geolocated' && (!device.latitude || !device.longitude)) return false;
        if (filterStatus === 'pending' && device.latitude && device.longitude) return false;
        return true;
    });

    container.innerHTML = filteredDevices.map(device => {
        const hasCoords = device.latitude && device.longitude;
        const isModified = modifiedDevices.has(device.id);
        const isSelected = selectedDeviceId === device.id;

        return `
            <div class="device-item p-3 border-bottom ${hasCoords ? 'geolocated' : ''} ${isSelected ? 'selected' : ''}" 
                 data-device-id="${device.id}" 
                 onclick="selectDevice(${device.id})">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <span class="color-indicator me-2" style="background-color: ${device.control_point.color};"></span>
                            <strong>${device.code}</strong>
                            ${isModified ? '<span class="badge bg-warning ms-2">Modificado</span>' : ''}
                        </div>
                        <small class="text-muted d-block">
                            <i class="bi bi-pin-map"></i> ${device.control_point.name}
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-geo"></i> ${device.application_area.name}
                        </small>
                    </div>
                    <div>
                        ${hasCoords ? 
                            '<span class="badge bg-success"><i class="bi bi-check-circle"></i></span>' : 
                            '<span class="badge bg-danger"><i class="bi bi-exclamation-circle"></i></span>'
                        }
                    </div>
                </div>
                ${hasCoords ? `
                    <div class="mt-2 small text-muted">
                        <i class="bi bi-globe"></i> ${parseFloat(device.latitude).toFixed(6)}, ${parseFloat(device.longitude).toFixed(6)}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function selectDevice(deviceId) {
    const device = devices.find(d => d.id === deviceId);
    if (!device) return;

    // Toggle selection
    if (selectedDeviceId === deviceId) {
        selectedDeviceId = null;
    } else {
        selectedDeviceId = deviceId;

        // Si el dispositivo ya tiene marcador, centrar en él
        if (markers[deviceId]) {
            map.panTo(markers[deviceId].getPosition());
            map.setZoom(19);
        }
    }

    renderDevicesList();
}

function updateStatistics() {
    const geolocated = devices.filter(d => d.latitude && d.longitude).length;
    const pending = devices.length - geolocated;
    const progress = (geolocated / devices.length * 100).toFixed(0);

    document.getElementById('geolocated-count').textContent = geolocated;
    document.getElementById('pending-count').textContent = pending;
    document.getElementById('modified-count').textContent = modifiedDevices.size;
    document.getElementById('stats-badge').textContent = `${geolocated} de ${devices.length} geolocalizados`;
    
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = progress + '%';
    progressBar.textContent = progress + '%';

    // Habilitar/deshabilitar botón de guardar
    document.getElementById('save-btn').disabled = modifiedDevices.size === 0;
}

// ===========================
// FUNCIONES DE BÚSQUEDA
// ===========================
function searchAddress() {
    const address = document.getElementById('address-search').value;
    if (!address) return;

    geocoder.geocode({ address: address }, function(results, status) {
        if (status === 'OK') {
            map.setCenter(results[0].geometry.location);
            map.setZoom(18);
        } else {
            alert('No se pudo encontrar la dirección: ' + status);
        }
    });
}

// ===========================
// FUNCIONES DE GUARDADO
// ===========================
function saveCoordinates(updateUrl, csrfToken) {
    if (modifiedDevices.size === 0) {
        alert('No hay cambios para guardar.');
        return;
    }

    // Incluir todos los dispositivos modificados (incluyendo los que tienen coordenadas null)
    const devicesToUpdate = Array.from(modifiedDevices).map(id => {
        const device = devices.find(d => d.id === id);
        return {
            id: device.id,
            latitude: device.latitude,
            longitude: device.longitude
        };
    });

    const validCount = devicesToUpdate.filter(d => d.latitude !== null && d.longitude !== null).length;
    const removedCount = devicesToUpdate.length - validCount;
    
    const confirmMessage = removedCount > 0 
        ? `¿Guardar cambios?\n• ${validCount} dispositivo(s) con coordenadas\n• ${removedCount} dispositivo(s) sin coordenadas (se eliminarán)`
        : `¿Guardar las coordenadas de ${validCount} dispositivo(s)?`;

    if (!confirm(confirmMessage)) {
        return;
    }

    // Mostrar loading
    const loadingOverlay = document.getElementById('loading-overlay');
    loadingOverlay.classList.add('active');

    // Timeout de 30 segundos
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);

    fetch(updateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            devices: devicesToUpdate
        }),
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        loadingOverlay.classList.remove('active');
        
        if (data && data.success) {
            alert(`✓ ${data.message || 'Guardado exitoso'}\n${data.updated_count || devicesToUpdate.length} dispositivo(s) actualizado(s)`);

            // Actualizar coordenadas originales para TODOS los dispositivos (incluyendo null)
            devicesToUpdate.forEach(d => {
                if (d.latitude !== null && d.longitude !== null) {
                    originalCoordinates[d.id] = {
                        lat: d.latitude,
                        lng: d.longitude
                    };
                } else {
                    // Si se eliminaron las coordenadas, eliminar del registro original
                    delete originalCoordinates[d.id];
                }
            });
            
            modifiedDevices.clear();
            updateStatistics();
            renderDevicesList();
            updatePolygons();
        } else {
            alert('Error: ' + (data?.message || 'Respuesta inválida del servidor'));
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        loadingOverlay.classList.remove('active');
        
        if (error.name === 'AbortError') {
            alert('La petición tardó demasiado tiempo. Por favor, verifica tu conexión e inténtalo de nuevo.');
        } else {
            console.error('Error:', error);
            alert('Error al guardar las coordenadas. Por favor, inténtalo de nuevo.\n' + error.message);
        }
    });
}

function resetChanges() {
    if (modifiedDevices.size === 0) {
        alert('No hay cambios para restablecer.');
        return;
    }

    if (!confirm('¿Deseas restablecer todos los cambios no guardados?')) {
        return;
    }

    modifiedDevices.forEach(id => {
        const device = devices.find(d => d.id === id);
        if (originalCoordinates[id]) {
            device.latitude = originalCoordinates[id].lat;
            device.longitude = originalCoordinates[id].lng;
            
            if (markers[id]) {
                markers[id].setPosition(originalCoordinates[id]);
            }
        } else {
            device.latitude = null;
            device.longitude = null;
            
            if (markers[id]) {
                markers[id].setMap(null);
                delete markers[id];
            }
        }
    });

    modifiedDevices.clear();
    selectedDeviceId = null;
    updateStatistics();
    renderDevicesList();
    updatePolygons();
}

// ===========================
// CONTROLES DE MAPA
// ===========================
function changeMapType(type) {
    map.setMapTypeId(type);
    document.querySelectorAll('.map-controls .btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

// ===========================
// EVENT LISTENERS
// ===========================
function setupEventListeners(updateUrl, csrfToken) {
    document.getElementById('search-btn').addEventListener('click', searchAddress);
    document.getElementById('address-search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchAddress();
    });

    document.getElementById('filter-control-point').addEventListener('change', renderDevicesList);
    document.getElementById('filter-area').addEventListener('change', renderDevicesList);
    document.querySelectorAll('input[name="filter-status"]').forEach(radio => {
        radio.addEventListener('change', renderDevicesList);
    });

    document.getElementById('clear-selection').addEventListener('click', function() {
        selectedDeviceId = null;
        renderDevicesList();
    });

    document.getElementById('save-btn').addEventListener('click', function() {
        saveCoordinates(updateUrl, csrfToken);
    });
    
    document.getElementById('reset-btn').addEventListener('click', resetChanges);
}
