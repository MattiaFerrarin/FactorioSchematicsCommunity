<script>
    const hamburgerBtn = document.getElementById("hamburger-btn");
    const sidebar = document.getElementById("sidebar");

    if (hamburgerBtn && sidebar) {
        hamburgerBtn.addEventListener("click", () => {
            const isOpen = sidebar.classList.toggle("is-open");
            hamburgerBtn.setAttribute("aria-expanded", String(isOpen));
        });
    }

    const userMenuBtn = document.getElementById("user-menu-btn");
    const userDropdown = document.getElementById("user-dropdown");

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            const isOpen = userDropdown.classList.toggle("is-open");
            userMenuBtn.setAttribute("aria-expanded", String(isOpen));
        });

        document.addEventListener("click", (e) => {
            if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userDropdown.classList.remove("is-open");
                userMenuBtn.setAttribute("aria-expanded", "false");
            }
        });
    }

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            if (sidebar && sidebar.classList.contains("is-open")) {
                sidebar.classList.remove("is-open");
                hamburgerBtn.setAttribute("aria-expanded", "false");
            }
            if (userDropdown && userDropdown.classList.contains("is-open")) {
                userDropdown.classList.remove("is-open");
                userMenuBtn.setAttribute("aria-expanded", "false");
            }
        }
    });
</script>