/**
 * Auto-send GPS every ~1s while staff is on duty (no manual button needed).
 */
(function () {
    if (!window.FYP_ON_DUTY) return;

    let watchId = null;
    let lastSent = 0;

    function send(pos) {
        const now = Date.now();
        if (now - lastSent < 900) return;
        lastSent = now;

        fetch('api/send_location.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                latitude: pos.coords.latitude,
                longitude: pos.coords.longitude,
                speed: pos.coords.speed != null ? pos.coords.speed * 3.6 : 0,
                auto: true
            })
        }).catch(function () {});
    }

    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            send,
            function () {},
            { enableHighAccuracy: true, maximumAge: 500, timeout: 10000 }
        );
    }

    window.addEventListener('beforeunload', function () {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
    });
})();
