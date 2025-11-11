$(function () {
    "use strict";

    // Silently handle external undefined references (like s in common.js)
    window.onerror = function (msg, src, line, col, err) {
        if (msg && msg.toString().includes("s is not defined")) return true;
        return false;
    };

    let originalButtonText;

    // =========================
    // CUSTOM FLATPICKR / PICKERS
    // =========================
    function initSessionDatePickers() {
        // YEAR PICKER
        $(".session-year-picker").each(function () {
            const inputEl = this;
            $(inputEl).prop("readonly", true);

            const raw = (inputEl.value || "").trim();
            if (raw && !/^\d{4}$/.test(raw)) {
                const m = raw.match(/^(\d{4})/);
                inputEl.value = m ? m[1] : "";
            }

            flatpickr(inputEl, {
                enableTime: false,
                dateFormat: "Y",
                clickOpens: true,
                static: true,
                defaultDate: null,
                allowInput: false,
                onOpen: function () {
                    renderYearPicker(this, inputEl);
                },
                onReady: function () {
                    const v = (inputEl.value || "").trim();
                    if (v && !/^\d{4}$/.test(v)) {
                        const m = v.match(/^(\d{4})/);
                        inputEl.value = m ? m[1] : "";
                    }
                    renderYearPicker(this, inputEl);
                },
            });
        });

        // MONTH PICKER
        $(".session-month-picker").each(function () {
            const $input = $(this);
            const $monthDisplay = $input.siblings(".month-display");
            const monthNames = [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December",
            ];

            $input.on("click", function (e) {
                e.preventDefault();
                showMonthPicker($input, $monthDisplay, monthNames);
            });
            $input.prop("readonly", true);
        });

        // DATETIME PICKER
        $(".session-datetime-picker").flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            defaultDate: new Date(),
            minuteIncrement: 1,
            position: "auto",
        });
    }

    function renderYearPicker(instance, inputEl) {
        const container = instance.calendarContainer;
        if (!container) return;

        const now = new Date();
        const currentYear = now.getFullYear();
        const windowSize = 11;
        let startYear = currentYear - 5;

        const val = parseInt(inputEl.value);
        if (!isNaN(val)) startYear = val - 5;

        instance.__windowStart = startYear;
        buildYearPickerUI(instance, inputEl);
    }

    function buildYearPickerUI(instance, inputEl) {
        const container = instance.calendarContainer;
        const inputRectWidth = inputEl.offsetWidth;
        const windowSize = 11;
        let start = instance.__windowStart || new Date().getFullYear() - 5;
        let end = start + (windowSize - 1);

        const header = `
            <div class="flatpickr-year-header">
                <button type="button" class="yp-prev btn btn-sm btn-light">&laquo; Prev</button>
                <span class="flatpickr-decade-range">${start}-${end}</span>
                <button type="button" class="yp-next btn btn-sm btn-light">Next &raquo;</button>
            </div>
        `;

        const years = [];
        for (let y = start; y <= end; y++) years.push(y);

        let grid = "";
        for (let i = 0; i < years.length; i += 4) {
            const row = years.slice(i, i + 4);
            grid += '<div class="flatpickr-year-row">';
            row.forEach((y) => {
                grid += `<button type="button" class="flatpickr-year-item" data-year="${y}">${y}</button>`;
            });
            while (row.length < 4) grid += '<div class="flatpickr-year-empty"></div>';
            grid += "</div>";
        }

        const html = `
            <div class="flatpickr-year-picker">
                ${header}
                <div class="flatpickr-year-grid">${grid}</div>
            </div>
        `;
        container.innerHTML = html;
        container.style.width = inputRectWidth + "px";
        addYearPickerStyles();

        container.querySelectorAll(".flatpickr-year-item").forEach((btn) => {
            btn.addEventListener("click", function () {
                const y = parseInt(this.getAttribute("data-year"));
                instance.setDate(new Date(y, 0, 1), true);
                inputEl.value = String(y);
                instance.close();
            });
        });

        const prev = container.querySelector(".yp-prev");
        const next = container.querySelector(".yp-next");
        prev.addEventListener("click", function () {
            instance.__windowStart =
                (instance.__windowStart || new Date().getFullYear() - 5) - 11;
            buildYearPickerUI(instance, inputEl);
        });
        next.addEventListener("click", function () {
            instance.__windowStart =
                (instance.__windowStart || new Date().getFullYear() - 5) + 11;
            buildYearPickerUI(instance, inputEl);
        });
    }

    function showMonthPicker($input, $monthDisplay, monthNames) {
        $(".custom-month-picker").remove();
        const rect = $input[0].getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        const monthPickerHTML = `
            <div class="custom-month-picker" style="
                position: absolute;
                top: ${rect.bottom + scrollTop + 2}px;
                left: ${rect.left + scrollLeft}px;
                width: ${rect.width}px;
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 9999;
                padding: 10px;
            ">
                <div class="custom-month-header" style="
                    text-align: center;
                    margin-bottom: 10px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #eee;
                    font-weight: bold;
                    color: #333;
                ">Select Month</div>
                <div class="custom-month-grid" style="
                    display: grid;
                    grid-template-columns: 1fr 1fr 1fr;
                    gap: 5px;
                ">
                    ${monthNames
                        .map(
                            (m, i) => `
                        <button type="button" class="custom-month-item" data-month="${i + 1}" style="
                            padding: 8px 5px;
                            background: #f8f9fa;
                            border: 1px solid #dee2e6;
                            border-radius: 3px;
                            cursor: pointer;
                            text-align: center;
                            font-size: 12px;
                            transition: all 0.2s;">
                            ${m}
                        </button>`
                        )
                        .join("")}
                </div>
            </div>
        `;
        $("body").append(monthPickerHTML);

        $(".custom-month-item").on("click", function () {
            const mm = parseInt($(this).data("month"), 10);
            $input.val(mm);
            $monthDisplay.text(monthNames[mm - 1]);
            $input.trigger("change");
            $(".custom-month-picker").remove();
        });

        $(document).on("click.monthPicker", function (e) {
            if (!$(e.target).closest(".custom-month-picker, .session-month-picker").length) {
                $(".custom-month-picker").remove();
                $(document).off("click.monthPicker");
            }
        });

        $(document).on("keydown.monthPicker", function (e) {
            if (e.key === "Escape") {
                $(".custom-month-picker").remove();
                $(document).off("keydown.monthPicker");
            }
        });
    }

    function addYearPickerStyles() {
        if (!$("#session-year-picker-styles").length) {
            const styles = `
                <style id="session-year-picker-styles">
                    .flatpickr-year-picker {
                        padding: 10px;
                        background: white;
                        border-radius: 4px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                        width: 100% !important;
                    }
                    .flatpickr-calendar { width: auto !important; }
                    .flatpickr-calendar.open { max-height: 350px; overflow: hidden; }
                </style>
            `;
            $("head").append(styles);
        }
    }

    // =========================
    // TOGGLE / VALIDATION / AJAX
    // =========================
    function initSessionFormToggle() {
        function toggleFields() {
            const sessionType = $("input[name='session_type']:checked").val();
            $(".manual-fields, .yearly-fields, .monthly-fields, .weekly-fields, .daily-fields").hide();
            $(
                "#start_at, #end_at, #duration_minutes, #start_year, #end_year, #start_month, #end_month, #start_day, #end_day, #start_hour, #end_hour, #monthly_start_time, #monthly_end_time, #weekly_start_time, #weekly_end_time"
            ).prop("required", false);

            if (sessionType === "manual") {
                $(".manual-fields").show();
                $("#start_at, #end_at, #duration_minutes").prop("required", true);
            } else if (sessionType === "yearly") {
                $(".yearly-fields").show();
                $("#start_year, #end_year").prop("required", true);
            } else if (sessionType === "monthly") {
                $(".monthly-fields").show();
                $("#start_month, #end_month, #monthly_start_time, #monthly_end_time").prop("required", true);
            } else if (sessionType === "weekly") {
                $(".weekly-fields").show();
                $("#start_day, #end_day, #weekly_start_time, #weekly_end_time").prop("required", true);

                // ✅ identical to user select, no visual gap
                $("#start_day, #end_day").select2({
                    theme: "bootstrap-5",
                    allowClear: true,
                    placeholder: "Select Day",
                    width: "100%",
                    dropdownParent: $("body"),
                    data: [
                        { id: "1", text: "Monday" },
                        { id: "2", text: "Tuesday" },
                        { id: "3", text: "Wednesday" },
                        { id: "4", text: "Thursday" },
                        { id: "5", text: "Friday" },
                        { id: "6", text: "Saturday" },
                        { id: "7", text: "Sunday" },
                    ],
                });
            } else if (sessionType === "daily") {
                $(".daily-fields").show();
                $("#start_hour, #end_hour").prop("required", true);

                // ✅ identical to user select, no visual gap
                $("#start_hour, #end_hour").select2({
                    theme: "bootstrap-5",
                    allowClear: true,
                    placeholder: "Select Hour",
                    width: "100%",
                    dropdownParent: $("body"),
                    data: Array.from({length: 24}, (_, i) => ({
                        id: i.toString(),
                        text: `${i.toString().padStart(2, '0')}:00`
                    })),
                });
            }

            // Clear fields when switching types
            if (sessionType !== "manual") $("#start_at, #end_at, #duration_minutes").val("");
            if (sessionType !== "yearly") $("#start_year, #end_year").val("");
            if (sessionType !== "monthly") {
                $("#start_month, #end_month").val("");
                $("#start_month").siblings(".month-display").text("");
                $("#end_month").siblings(".month-display").text("");
                $("#monthly_start_time, #monthly_end_time").val("");
            }
            if (sessionType !== "weekly") {
                $("#start_day, #end_day").val("");
                $("#weekly_start_time, #weekly_end_time").val("");
            }
            if (sessionType !== "daily") {
                $("#start_hour, #end_hour").val("");
            }
        }

        $(".session-type").change(toggleFields);
        toggleFields();
    }

    // Time validation functions
    function validateTimeFields() {
        const sessionType = $("input[name='session_type']:checked").val();
        let isValid = true;

        if (sessionType === "monthly") {
            const startTime = $("#monthly_start_time").val();
            const endTime = $("#monthly_end_time").val();
            if (startTime && endTime && startTime >= endTime) {
                iziToast.error({
                    title: "Error",
                    layout: 2,
                    position: "topRight",
                    message: "Monthly end time must be after start time.",
                });
                isValid = false;
            }
        }

        if (sessionType === "weekly") {
            const startTime = $("#weekly_start_time").val();
            const endTime = $("#weekly_end_time").val();
            if (startTime && endTime && startTime >= endTime) {
                iziToast.error({
                    title: "Error",
                    layout: 2,
                    position: "topRight",
                    message: "Weekly end time must be after start time.",
                });
                isValid = false;
            }
        }

        return isValid;
    }

    $("#sessionForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const user = $.trim($("#user_id").val());
        const sessionType = $("input[name='session_type']:checked").val();
        const startDay = $.trim($("#start_day").val());
        const endDay = $.trim($("#end_day").val());
        const startHour = $.trim($("#start_hour").val());
        const endHour = $.trim($("#end_hour").val());
        const monthlyStartTime = $.trim($("#monthly_start_time").val());
        const monthlyEndTime = $.trim($("#monthly_end_time").val());
        const weeklyStartTime = $.trim($("#weekly_start_time").val());
        const weeklyEndTime = $.trim($("#weekly_end_time").val());
        const missing = [];

        if (!user) missing.push("User");
        if (!sessionType) missing.push("Session Type");
        
        if (sessionType === "weekly") {
            if (!startDay) missing.push("Start Day");
            if (!endDay) missing.push("End Day");
            if (!weeklyStartTime) missing.push("Weekly Start Time");
            if (!weeklyEndTime) missing.push("Weekly End Time");
        }
        
        if (sessionType === "daily") {
            if (!startHour) missing.push("Start Hour");
            if (!endHour) missing.push("End Hour");
        }
        
        if (sessionType === "monthly") {
            if (!monthlyStartTime) missing.push("Monthly Start Time");
            if (!monthlyEndTime) missing.push("Monthly End Time");
        }

        if (missing.length > 0) {
            iziToast.error({
                title: "Error",
                layout: 2,
                position: "topRight",
                message: missing.join(", ") + " field(s) are required.",
            });
            return false;
        }

        // Validate time fields
        if (!validateTimeFields()) {
            return false;
        }

        const formArray = {
            formId: form.attr("id"),
            csrf: form.find("input[name='_token']").val(),
            url: form.attr("action"),
            formObject: form,
        };
        ajaxRequest(formArray);
    });

    function disableSubmitButton(form) {
        originalButtonText = form.find("button[type='submit']").text();
        form
            .find("button[type='submit']")
            .prop("disabled", true)
            .html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...'
            );
    }

    function enableSubmitButton(form) {
        form.find("button[type='submit']").prop("disabled", false).html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
    }

    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
    }

    function afterSuccessOfAjaxRequest(formObject) {
        formAdjustIfSaveOperation(formObject);
        pageRedirect(formObject);
    }

    function pageRedirect(formObject) {
        setTimeout(() => {
            location.href = baseURL + "/session/list";
        }, 1000);
    }

    function ajaxRequest(formArray) {
        const formData = new FormData(document.getElementById(formArray.formId));
        $.ajax({
            type: "POST",
            url: formArray.url,
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            headers: { "X-CSRF-TOKEN": formArray.csrf },
            beforeSend: function () {
                beforeCallAjaxRequest(formArray.formObject);
            },
            success: function (data) {
                iziToast.success({ title: "Success", layout: 2, message: data.message });
                afterSuccessOfAjaxRequest(formArray.formObject);
            },
            error: function (response) {
                if (response.status === 422 && response.responseJSON && response.responseJSON.errors) {
                    const errors = response.responseJSON.errors;
                    let msg = "";
                    Object.keys(errors).forEach((key) => {
                        msg += errors[key][0] + "<br>";
                    });
                    iziToast.error({ title: "Error", layout: 2, message: msg });
                } else {
                    iziToast.error({
                        title: "Error",
                        layout: 2,
                        message:
                            response.responseJSON?.message || "An error occurred.",
                    });
                }
            },
            complete: function () {
                afterCallAjaxResponse(formArray.formObject);
            },
        });
    }

    function formAdjustIfSaveOperation(formObject) {
        if ((formObject.find("input[name='_method']").val() || "POST").toUpperCase() === "POST") {
            formObject[0].reset();
        }
    }

    // Time field change handlers
    $("#monthly_start_time, #monthly_end_time").on("change", function() {
        validateTimeFields();
    });

    $("#weekly_start_time, #weekly_end_time").on("change", function() {
        validateTimeFields();
    });

    $(document).ready(function () {
        initSessionFormToggle();
        if (typeof initSelect2SessionUsers === "function") {
            initSelect2SessionUsers();
        }
        initSessionDatePickers();
    });
});