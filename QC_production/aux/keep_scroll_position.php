<script>
    // Save scroll position before unload
    window.addEventListener("beforeunload", function() {
        localStorage.setItem("scrollY", window.scrollY);
    });

    // Restore scroll position after load (skip if URL has an anchor)
    window.addEventListener("load", function() {
        if (!window.location.hash) {
            const y = localStorage.getItem("scrollY");
            if (y !== null) {
                window.scrollTo(0, parseInt(y, 10));
            }
        }
    });
</script>