(function() {
    'use strict';
    function stop(e) {
        if (e && e.preventDefault) e.preventDefault();
        if (e && e.stopPropagation) e.stopPropagation();
        return false;
    }
    var k = function(e) {
        var key = (e.key || '').toLowerCase();
        var code = e.keyCode || e.which;
        if (key === 'f12' || code === 123) return stop(e);
        if (e.ctrlKey && (key === 's' || key === 'u' || key === 'p')) return stop(e);
        if (e.ctrlKey && e.shiftKey && (key === 'i' || key === 'j' || key === 'c' || code === 73 || code === 74 || code === 67)) return stop(e);
        if (e.metaKey && (key === 'u' || key === 's' || key === 'p')) return stop(e);
        if (e.ctrlKey && key === 'u') return stop(e);
    };
    document.addEventListener('keydown', k, true);
    document.addEventListener('keyup', k, true);
    document.addEventListener('contextmenu', stop, true);
    document.addEventListener('selectstart', stop, true);
    document.addEventListener('dragstart', stop, true);
    document.addEventListener('copy', stop, true);
    document.addEventListener('cut', stop, true);
    try {
        document.body.style.webkitUserSelect = 'none';
        document.body.style.userSelect = 'none';
        document.body.style.webkitTouchCallout = 'none';
    } catch (_) {}
    setInterval(function() {
        try {
            var w = window.outerWidth - window.innerWidth;
            var h = window.outerHeight - window.innerHeight;
            if (w > 140 || h > 140) { document.body.style.filter = 'blur(12px)'; document.body.style.pointerEvents = 'none'; }
        } catch (_) {}
    }, 800);
    })();
