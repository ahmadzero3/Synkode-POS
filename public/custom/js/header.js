document.addEventListener("DOMContentLoaded", function () {
    const updateBadge = async () => {
        try {
            const response = await fetch(
                "/item/low-stock-count?ts=" + Date.now()
            );

            if (!response.ok)
                throw new Error(`HTTP error! status: ${response.status}`);

            const data = await response.json();

            const badges = document.querySelectorAll(".notification-badge");
            const listBox = document.querySelector("#low-stock-items");

            // If elements do not exist → exit gracefully (no errors)
            if (badges.length === 0 || !listBox) return;

            // If feature disabled → hide badge + clear list + stop here
            if (!data.enabled) {
                badges.forEach((badge) => {
                    badge.style.display = "none";
                });

                listBox.innerHTML = "";
                return;
            }

            // --- Feature ENABLED logic ---
            badges.forEach((badge) => {
                badge.textContent = data.count;
                badge.style.display = "flex";
                badge.style.animation = "none";
                void badge.offsetHeight;
                badge.style.animation = null;
            });

            let lowStockItems = "";

            if (data.count > 0) {
                lowStockItems = data.items
                    .slice(0, 3)
                    .map(
                        (item) =>
                            `<li class="dropdown-item border-bottom">${item.name}</li>`
                    )
                    .join("");
            } else {
                lowStockItems = `
                    <li class="dropdown-empty">
                        No items under required min Stock
                    </li>`;
            }

            listBox.innerHTML = lowStockItems;
        } catch (error) {
            console.error("Error:", error);

            const badges = document.querySelectorAll(".notification-badge");
            const listBox = document.querySelector("#low-stock-items");

            // If HTML elements are missing → exit safely
            if (badges.length === 0 || !listBox) return;

            // When disabled → do NOT show "!" badge or errors
            // Check backend settings response cache
            try {
                const settingsCheck = await fetch("/item/low-stock-count");
                const s = await settingsCheck.json();
                if (!s.enabled) {
                    badges.forEach((badge) => (badge.style.display = "none"));
                    listBox.innerHTML = "";
                    return;
                }
            } catch (_) {
                // If even settings cannot be read, assume disabled → hide everything
                badges.forEach((badge) => (badge.style.display = "none"));
                if (listBox) listBox.innerHTML = "";
                return;
            }

            // Only show error badge if feature ENABLED
            badges.forEach((badge) => {
                badge.textContent = "!";
                badge.style.display = "flex";
            });
        }
    };

    updateBadge();
    setInterval(updateBadge, 15000);
    document.addEventListener("visibilitychange", updateBadge);
    window.addEventListener("focus", updateBadge);
});
