function initItemAutocomplete(inputSelector, options = {}) {
    let itemSearchPage = 1;
    let itemSearchHasMore = true;
    let lastSearchTerm = "";
    let itemSearchAccumulatedItems = [];

    const input = inputSelector;

    input.autocomplete({
        minLength: 1,
        source: function(request, response) {
            if (lastSearchTerm !== request.term) {
                itemSearchPage = 1;
                itemSearchHasMore = true;
                itemSearchAccumulatedItems = [];
            }
            lastSearchTerm = request.term;

            $.ajax({
                url: baseURL + '/item/ajax/get-list',
                dataType: "json",
                data: {
                    search: request.term,
                    warehouse_id: options.warehouse_id || '',
                    party_id: options.party_id || '',
                    page: itemSearchPage,
                    request_from: options.request_from || '',
                },
                success: function(data) {
                    let items = data.items || data;
                    itemSearchHasMore = data.has_more !== undefined ? data.has_more : (items.length >= 20);

                    if (items.length === 1 && items[0].item_code === request.term) {
                        input.autocomplete("close");
                        input.autocomplete("option", "select").call(input[0], null, { item: items[0] });
                    } else {
                        if (itemSearchPage === 1) {
                            itemSearchAccumulatedItems = items.slice();
                        } else {
                            const existingIds = new Set(itemSearchAccumulatedItems.map(i => i.id));
                            items.forEach(i => {
                                if (i.id && !existingIds.has(i.id)) {
                                    itemSearchAccumulatedItems.push(i);
                                }
                            });
                        }

                        const displayItems = itemSearchAccumulatedItems.slice();
                        if (itemSearchHasMore) {
                            displayItems.push({ isLoadMore: true });
                        }

                        response(displayItems);
                    }
                }
            });
        },

        focus: function(event, ui) {
            if (ui.item.isLoadMore) return false;
            input.val(ui.item.name);
            if(options.module === 'sale'){
                if (ui.item.sale_price) {
                    window.searchedItemPrice = _parseFix(ui.item.sale_price);
                }
            }
            else if(options.module === 'purchase'){
                if (ui.item.purchase_price) {
                    window.searchedItemPrice = _parseFix(ui.item.purchase_price);
                }
            }else{
                console.warn("Module not recognized for price setting.");
            }

            return false;
        },

        select: function(event, ui) {
            if (ui.item.isLoadMore) {
                event.preventDefault();
                itemSearchPage++;
                input.autocomplete("search", lastSearchTerm);
                return false;
            }
            input.val(ui.item.name);
            if (typeof options.onSelect === 'function') {
                options.onSelect(ui.item);
            }
            return false;
        },

        open: function() {
            const header = $(`<li class='ui-autocomplete-category' style='padding:5px; border-bottom:1px solid #ddd; background:#f8f9fa;'>
                <div style='display: flex; font-weight: bold;'>
                    <span style='flex:3;'>Name</span>
                    <span style='flex:1;'>Brand</span>
                    <span style='flex:1;text-align:right;'>Sales Price</span>
                    <span style='flex:1;text-align:right;'>Purchase Price</span>
                    <span style='flex:1;text-align:right;'>Stock</span>
                </div>
            </li>`);
            input.autocomplete("widget").prepend(header);

            input.autocomplete("widget").off("scroll.autocomplete").on("scroll.autocomplete", function () {
                const $menu = $(this);
                const scrollTop = $menu.scrollTop();
                const scrollHeight = $menu.prop("scrollHeight");
                const clientHeight = $menu.innerHeight();

                if (itemSearchHasMore && scrollTop + clientHeight >= scrollHeight - 10) {
                    itemSearchHasMore = false;
                    itemSearchPage++;
                    input.autocomplete("search", lastSearchTerm);
                }
            });
        },




    }).autocomplete("instance")._renderItem = function(ul, item) {
        if (item.isLoadMore) {
            return $("<li>")
                .attr("style", "padding: 5px; text-align: center; color: #007bff; cursor: pointer;")
                .append("<div>Loading...</div>")
                .appendTo(ul);
        }
        return $("<li>")
            .attr("style", "padding:5px; border-bottom:1px solid #eee;")
            .append(`<div style='display: flex; align-items: center;'>
                <span style='flex:3;'>${item.name || 'N/A'}</span>
                <span style='flex:1;'>${item.brand_name || '--'}</span>
                <span style='flex:1; text-align:right;'>${_parseFix(item.sale_price)}</span>
                <span style='flex:1; text-align:right;'>${_parseFix(item.purchase_price)}</span>
                <span style='flex:1; text-align:right; color:${_parseQuantity(item.current_stock) > 0 ? '#000' : '#dc3545'};'>${_parseQuantity(item.current_stock)}</span>
            </div>`)
            .appendTo(ul);
    };
}
