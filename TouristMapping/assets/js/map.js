document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('map');
    const destList = document.getElementById('destList');
    const categoryFilter = document.getElementById('categoryFilter');
    const destSearch = document.getElementById('destSearch');
    const resetView = document.getElementById('resetView');
    const showAll = document.getElementById('showAll');
    const fullscreenBtn = document.getElementById('fullscreenBtn');

    if (!mapContainer) return;
    if (typeof L === 'undefined') { console.error('Leaflet not loaded'); return; }

    const map = L.map(mapContainer).setView([12.5, 122], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const places = Array.isArray(window.touristDestinations) ? window.touristDestinations : [];
    const markers = [];
    const markerMap = {};

    function getCategoryIcon(cat) {
        const c = (cat || '').toLowerCase();
        if (c.includes('natural')) return L.divIcon({className:'custom-marker', html:'<div style="background:#16a34a;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>', iconSize:[20,20]});
        if (c.includes('histor')) return L.divIcon({className:'custom-marker', html:'<div style="background:#b45309;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>', iconSize:[20,20]});
        if (c.includes('cultural')) return L.divIcon({className:'custom-marker', html:'<div style="background:#7c3aed;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>', iconSize:[20,20]});
        if (c.includes('beach') || c.includes('island')) return L.divIcon({className:'custom-marker', html:'<div style="background:#0ea5e9;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>', iconSize:[20,20]});
        if (c.includes('park')) return L.divIcon({className:'custom-marker', html:'<div style="background:#22c55e;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>', iconSize:[20,20]});
        return L.divIcon({className:'custom-marker', html:'<div style="background:#2563eb;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>', iconSize:[20,20]});
    }

    places.forEach(function(place, idx){
        const lat = Number(place.latitude);
        const lng = Number(place.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        const icon = getCategoryIcon(place.category_name);
        const marker = L.marker([lat,lng], {icon: icon}).addTo(map);
        const img = place.image ? place.image : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80';
        const desc = (place.description || '').replace(/<[^>]+>/g,'');
        const shortDesc = desc.length > 80 ? desc.substring(0,80) + '...' : desc;
        const popup = document.createElement('div');
        popup.innerHTML = '<img src="'+img+'" class="popup-img" alt="'+(place.name||'')+'"><div><strong>'+(place.name||'')+'</strong><br><small class="text-muted">'+(place.category_name||'')+' • '+(place.municipality||'')+', '+(place.province||'')+'</small><p class="small mt-1 mb-2">'+shortDesc+'</p><a class="btn btn-sm btn-primary" href="destination.php?slug='+(place.slug||'')+'">View Details</a></div>';
        marker.bindPopup(popup);
        marker._place = place;
        marker._idx = idx;
        markers.push(marker);
        markerMap[place.slug || idx] = marker;
    });

    function renderList(filterCat, searchTerm){
        if(!destList) return;
        destList.innerHTML = '';
        const term = (searchTerm||'').toLowerCase();
        let count = 0;
        places.forEach(function(place){
            const cat = place.category_name || '';
            if(filterCat && filterCat !== 'all' && cat !== filterCat) return;
            if(term && !( (place.name||'').toLowerCase().includes(term) || (place.municipality||'').toLowerCase().includes(term) || cat.toLowerCase().includes(term) )) return;
            count++;
            const item = document.createElement('div');
            item.className = 'dest-item';
            item.style.cursor = 'pointer';
            item.innerHTML = '<div class="fw-semibold">'+(place.name||'')+'</div><div class="small text-muted">'+cat+' • '+(place.municipality||'')+', '+(place.province||'')+'</div>';
            item.addEventListener('click', function(){
                const lat = Number(place.latitude); const lng = Number(place.longitude);
                if(Number.isFinite(lat)&&Number.isFinite(lng)){
                    map.setView([lat,lng], 14, {animate:true});
                    const key = place.slug || places.indexOf(place);
                    if(markerMap[key]) markerMap[key].openPopup();
                }
            });
            destList.appendChild(item);
        });
        const badge = document.querySelector('.map-card .badge');
        if(badge) badge.textContent = count + ' shown';
    }

    if(categoryFilter) categoryFilter.addEventListener('change', function(){ renderList(this.value, destSearch ? destSearch.value : ''); });
    if(destSearch) destSearch.addEventListener('input', function(){ renderList(categoryFilter ? categoryFilter.value : 'all', this.value); });
    renderList('all','');

    if(resetView){
        resetView.addEventListener('click', function(){
            const coords = markers.map(m => m.getLatLng());
            if(coords.length > 1) map.fitBounds(coords, {padding:[40,40], maxZoom:8});
            else if(coords.length === 1) map.setView(coords[0], 10);
            else map.setView([12.5,122], 6);
        });
    }
    if(showAll){
        showAll.addEventListener('click', function(){
            const coords = markers.map(m => m.getLatLng());
            if(coords.length > 1) map.fitBounds(coords, {padding:[40,40], maxZoom:8});
            else if(coords.length === 1) map.setView(coords[0], 10);
        });
    }
    if(fullscreenBtn){
        fullscreenBtn.addEventListener('click', function(){
            const el = document.querySelector('.map-card');
            if(!document.fullscreenElement){ el.requestFullscreen().catch(()=>{}); }
            else { document.exitFullscreen(); }
        });
    }

    setTimeout(function(){ map.invalidateSize(); }, 200);
});
