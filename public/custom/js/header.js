document.addEventListener("DOMContentLoaded", function () {
    const updateBadge = async () => {
        try {
            const response = await fetch(
                "/item/low-stock-count?ts=" + Date.now()
            );

            if (!response.ok)
                throw new Error(`HTTP error! status: ${response.status}`);

            const data = await response.json();

            document.querySelectorAll(".notification-badge").forEach((badge) => {
                if (!data.enabled) {
                    badge.style.display = "none";
                    return;
                }

                badge.textContent = data.count;
                badge.style.display = "flex";
                badge.style.animation = "none";
                void badge.offsetHeight;
                badge.style.animation = null;
            });

            let lowStockItems = "";

            if (data.enabled) {
                if (data.count > 0) {
                    lowStockItems = data.items
                        .slice(0, 3)
                        .map(
                            (item) =>
                                `<li class="dropdown-item border-bottom">${item.name}</li>`
                        )
                        .join("");
                } else {
                    lowStockItems =
                        `<li class="dropdown-empty">
                            No items under required min Stock
                        </li>`;
                }
            } else {
                lowStockItems = "";
            }

            document.querySelector("#low-stock-items").innerHTML =
                lowStockItems;
        } catch (error) {
            console.error("Error:", error);
            document.querySelectorAll(".notification-badge").forEach((badge) => {
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
