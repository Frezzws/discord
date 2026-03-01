(function() {
    'use strict';
    var k = function(e) {
        var key = e.key || e.keyCode;
        if (key === 'F12' || key === 123) { e.preventDefault(); return false; }
        if (e.ctrlKey && (key === 's' || key === 'S')) { e.preventDefault(); return false; }
        if (e.ctrlKey && e.shiftKey && (key === 'I' || key === 'i' || key === 73)) { e.preventDefault(); return false; }
        if (e.ctrlKey && e.shiftKey && (key === 'J' || key === 'j' || key === 74)) { e.preventDefault(); return false; }
        if (e.ctrlKey && e.shiftKey && (key === 'C' || key === 'c' || key === 67)) { e.preventDefault(); return false; }
        if (e.ctrlKey && (key === 'u' || key === 'U' || key === 85)) { e.preventDefault(); return false; }
        if (e.ctrlKey && (key === 'p' || key === 'P' || key === 80)) { e.preventDefault(); return false; }
    };
    document.addEventListener('keydown', k, true);
    document.addEventListener('keyup', k, true);
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; }, true);
    document.addEventListener('selectstart', function(e) { e.preventDefault(); return false; }, true);
    document.addEventListener('dragstart', function(e) { e.preventDefault(); return false; }, true);
    document.addEventListener('copy', function(e) { e.preventDefault(); return false; }, true);
    document.addEventListener('cut', function(e) { e.preventDefault(); return false; }, true);
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
