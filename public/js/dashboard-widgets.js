// dashboard-widgets.js
// Initializes FullCalendar markers and rooms-availability widget
(function () {
    const calendarDatesUrl = "/room/api/bookings/calendar-dates";
    const bookingsByDateUrl = "/room/api/bookings/by-date";

    // helper: return urls for a given prefix (rooms/assets)
    function urlsForPrefix(prefix) {
        if (prefix === "assets") {
            // prefer asset-specific endpoints if they exist, otherwise reuse room endpoints with type=asset
            return {
                calendarDates: "/asset/api/bookings/calendar-dates",
                bookingsByDate: "/asset/api/bookings/by-date",
            };
        }
        return {
            calendarDates: calendarDatesUrl,
            bookingsByDate: bookingsByDateUrl,
        };
    }

    document.addEventListener("DOMContentLoaded", function () {
        // detect which availability prefix is present on the page (assets or rooms)
        const availElement = document.querySelector('[id$="-availability"]');
        const activePrefix = availElement
            ? availElement.id.replace(/-availability$/, "")
            : "rooms";
        const activeUrls = urlsForPrefix(activePrefix);

        // calendar init
        const calendarEl = document.getElementById("dashboard-calendar");
        if (calendarEl && typeof FullCalendar !== "undefined") {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                headerToolbar: { left: "", center: "title", right: "" },
                navLinks: false,
                locale: "id",
                height: 300,
                selectable: true,
                dayMaxEventRows: true,
                dateClick: function (info) {
                    fetch(
                        activeUrls.bookingsByDate +
                            "?date=" +
                            encodeURIComponent(info.dateStr),
                    )
                        .then((r) => r.json())
                        .then((data) => showBookingsModal(info.dateStr, data))
                        .catch((err) => {
                            console.error(err);
                            showBookingsModal(info.dateStr, []);
                        });
                },
            });
            calendar.render();

            function insertToolbarNav() {
                const titleEl = calendarEl.querySelector(".fc-toolbar-title");
                if (!titleEl) return;
                const centerChunk = titleEl.parentElement;
                if (
                    centerChunk.querySelector(".calendar-nav-left") &&
                    centerChunk.querySelector(".calendar-nav-right")
                )
                    return;
                const prevBtn = document.createElement("button");
                prevBtn.type = "button";
                prevBtn.className = "fc-button calendar-nav-left";
                prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
                prevBtn.addEventListener("click", function () {
                    calendar.prev();
                    insertToolbarNav();
                });
                const nextBtn = document.createElement("button");
                nextBtn.type = "button";
                nextBtn.className = "fc-button calendar-nav-right";
                nextBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
                nextBtn.addEventListener("click", function () {
                    calendar.next();
                    insertToolbarNav();
                });
                centerChunk.insertBefore(prevBtn, titleEl);
                centerChunk.insertBefore(nextBtn, titleEl.nextSibling);
            }
            insertToolbarNav();
            calendar.setOption("datesSet", function () {
                setTimeout(insertToolbarNav, 20);
            });

            const bookingDateSet = new Set();
            function markCells() {
                const cells = calendarEl.querySelectorAll(".fc-daygrid-day");
                cells.forEach((cell) => {
                    const date = cell.getAttribute("data-date");
                    if (!date) return;
                    if (bookingDateSet.has(date)) {
                        if (!cell.querySelector(".booking-marker")) {
                            const marker = document.createElement("div");
                            marker.className = "booking-marker";
                            cell.style.position = "relative";
                            cell.appendChild(marker);
                        }
                    } else {
                        const existing = cell.querySelector(".booking-marker");
                        if (existing) existing.remove();
                    }
                });
            }
            function fetchAndMarkDates() {
                fetch(activeUrls.calendarDates)
                    .then((r) => r.json())
                    .then((dates) => {
                        bookingDateSet.clear();
                        (dates || []).forEach((d) => bookingDateSet.add(d));
                        setTimeout(markCells, 20);
                    })
                    .catch((err) =>
                        console.error("Unable to load calendar dates", err),
                    );
            }
            fetchAndMarkDates();
            calendar.setOption("datesSet", function () {
                setTimeout(markCells, 20);
            });
        }

        function formatDateLocal(dateStr) {
            try {
                const d = new Date(dateStr);
                return d.toLocaleDateString("id-ID", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                });
            } catch (e) {
                return dateStr;
            }
        }
        function formatTimeRange(start, end) {
            try {
                const s = new Date(start);
                const e = new Date(end);
                const opt = { hour: "2-digit", minute: "2-digit" };
                // if booking spans multiple calendar days, show full date + time range
                const sameDay =
                    s.getFullYear() === e.getFullYear() &&
                    s.getMonth() === e.getMonth() &&
                    s.getDate() === e.getDate();
                if (!sameDay) {
                    const dateOpt = {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                    };
                    return `${s.toLocaleDateString(
                        "id-ID",
                        dateOpt,
                    )} ${s.toLocaleTimeString(
                        "id-ID",
                        opt,
                    )} \u2192 ${e.toLocaleDateString(
                        "id-ID",
                        dateOpt,
                    )} ${e.toLocaleTimeString("id-ID", opt)}`;
                }
                return `${s.toLocaleTimeString(
                    "id-ID",
                    opt,
                )} - ${e.toLocaleTimeString("id-ID", opt)}`;
            } catch (e) {
                return `${start} - ${end}`;
            }
        }

        function showBookingsModal(dateStr, bookings) {
            const modalLabel = document.getElementById("modal-date-label");
            const modalList = document.getElementById("modal-bookings-list");
            if (!modalLabel || !modalList) return;
            modalLabel.innerText = formatDateLocal(dateStr);
            if (!Array.isArray(bookings) || bookings.length === 0) {
                modalList.innerHTML =
                    '<p class="text-muted">Tidak ada booking terverifikasi pada tanggal ini.</p>';
            } else {
                const html = bookings
                    .map((b) => {
                        // Handle room/asset name - could be nested object or string
                        let room = b.room;
                        if (typeof room === "object" && room !== null) {
                            room = room.name || room.id || "—";
                        }
                        room = escapeHtml(
                            room ||
                                b.room_name ||
                                b.asset_name ||
                                b.asset ||
                                "—",
                        );

                        // Handle user name - could be nested object or string
                        let user = b.user_name || b.pic_name || b.user;
                        if (typeof user === "object" && user !== null) {
                            user = user.name || user.id || "-";
                        }
                        user = escapeHtml(user || "-");

                        const title = escapeHtml(b.title || "-");
                        const time = escapeHtml(
                            formatTimeRange(b.start_at, b.end_at),
                        );
                        const division = escapeHtml(b.division || "");
                        const directorate = escapeHtml(b.directorate || "");

                        // Build status badge
                        let statusBadge = "";
                        if (b.is_overdue) {
                            statusBadge = `<span class="badge bg-danger mb-1">Terlambat</span>`;
                        } else if (b.status === "done") {
                            statusBadge = `<span class="badge bg-secondary mb-1">Selesai</span>`;
                        } else if (b.status === "in_use") {
                            statusBadge = `<span class="badge bg-success mb-1">Sedang Digunakan</span>`;
                        }

                        // Build user section - only show if user is not empty/dash
                        const userSection =
                            user && user !== "-"
                                ? `
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle text-muted" style="font-size:1.05rem"></i>
                                        <div class="small">${user}</div>
                                    </div>
                        `
                                : "";

                        return `
                        <div class="card mb-2 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">${room}</h6>
                                    <div class="text-muted small mb-2">${title}</div>
                                    ${userSection}
                                </div>
                                <div class="ms-3 text-end" style="min-width:150px;">
                                    ${statusBadge}
                                    ${
                                        division
                                            ? `<span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 mb-1" style="padding:.35rem .5rem;"><i class="bi bi-people" style="font-size:0.85rem"></i><span class="small text-truncate" style="max-width:7.5rem;display:inline-block;">${division}</span></span>`
                                            : ""
                                    }
                                    ${
                                        directorate
                                            ? `<span class="badge bg-light text-dark d-inline-flex align-items-center gap-1" style="padding:.35rem .5rem;"><i class="bi bi-diagram-3" style="font-size:0.85rem"></i><span class="small text-truncate" style="max-width:7.5rem;display:inline-block;">${directorate}</span></span>`
                                            : ""
                                    }
                                    <div class="text-muted small mt-3 text-end">${time}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    })
                    .join("");
                modalList.innerHTML = html;
            }
            const modalEl = document.getElementById("calendarDateModal");
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return "";
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Availability widget (works for rooms and assets). Locate any element that ends with -availability
        (function () {
            const availEls = Array.from(
                document.querySelectorAll('[id$="-availability"]'),
            );
            if (!availEls.length) return;

            availEls.forEach(function (roomsEl) {
                // derive prefix from id: e.g., rooms-availability -> rooms
                const id = roomsEl.id;
                const prefix = id.replace(/-availability$/, "");
                const prevBtn = document.getElementById(prefix + "-date-prev");
                const nextBtn = document.getElementById(prefix + "-date-next");
                const labelEl = document.getElementById(prefix + "-date-label");
                if (!roomsEl || !labelEl || !prevBtn || !nextBtn) return;

                const urls = urlsForPrefix(prefix);
                let rooms = [];
                try {
                    // prefer data-items (new generic), fallback to data-rooms for backwards compatibility
                    const raw =
                        roomsEl.getAttribute("data-items") ||
                        roomsEl.getAttribute("data-rooms") ||
                        "[]";
                    rooms = JSON.parse(raw || "[]") || [];
                } catch (e) {
                    rooms = [];
                }

                // Get all-time bookings if available from data attribute
                let allTimeBookings = [];
                try {
                    const rawAllTime =
                        roomsEl.getAttribute("data-alltime-bookings") || "[]";
                    allTimeBookings = JSON.parse(rawAllTime || "[]") || [];
                } catch (e) {
                    allTimeBookings = [];
                }

                let currentDate = new Date();
                function isoDate(d) {
                    // Use local date components to avoid timezone shifts caused by toISOString()
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, "0");
                    const dd = String(d.getDate()).padStart(2, "0");
                    return `${yyyy}-${mm}-${dd}`;
                }
                function formatLabel(d) {
                    try {
                        const months = [
                            "Jan",
                            "Feb",
                            "Mar",
                            "Apr",
                            "Mei",
                            "Jun",
                            "Jul",
                            "Agt",
                            "Sep",
                            "Okt",
                            "Nov",
                            "Des",
                        ];
                        const weekday = d.toLocaleDateString("id-ID", {
                            weekday: "long",
                        });
                        const day = d.getDate().toString().padStart(2, "0");
                        const month = months[d.getMonth()];
                        const year = d.getFullYear();
                        return `${weekday}, ${day} ${month} ${year}`;
                    } catch (e) {
                        return isoDate(d);
                    }
                }
                function renderLoading() {
                    roomsEl.innerHTML = '<div class="text-muted">Memuat…</div>';
                }
                function buildRoomBookingsMap(bookings) {
                    const map = new Map();
                    // also create a lookup map by booking id/index
                    const lookup = {};
                    (bookings || []).forEach((b, idx) => {
                        // prefer numeric room or asset id when available to match rooms/assets list
                        const roomKey = b.room_id ?? b.asset_id ?? null;
                        // prefer room name fields, fallback to asset name fields so this map works for both types
                        const roomName =
                            b.room_name ??
                            b.room ??
                            b.roomName ??
                            b.asset_name ??
                            b.asset ??
                            b.assetName ??
                            null;
                        const key =
                            roomKey != null
                                ? String(roomKey)
                                : roomName || "__unknown__";
                        const start = b.start_at ?? b.start ?? null;
                        const end = b.end_at ?? b.end ?? null;
                        if (!map.has(key))
                            map.set(key, {
                                name: roomName || String(key),
                                ranges: [],
                            });
                        // ensure booking has an id for lookup; fallback to index-based id
                        const bid = b.id ?? b.booking_id ?? `idx-${idx}`;
                        lookup[bid] = b;
                        map.get(key).ranges.push({
                            start,
                            end,
                            bookingId: bid,
                        });
                    });
                    // sort ranges for each room by start time (earliest first)
                    for (const entry of map.values()) {
                        entry.ranges.sort((a, b) => {
                            const sa = a.start
                                ? new Date(a.start).getTime()
                                : 0;
                            const sb = b.start
                                ? new Date(b.start).getTime()
                                : 0;
                            return sa - sb;
                        });
                    }
                    return { map, lookup };
                }
                function renderRoomsForDate(bookings, lookup) {
                    const { map } = buildRoomBookingsMap(bookings);
                    const rows = rooms.map((r) => {
                        const idKey = r.id != null ? String(r.id) : null;
                        const name = r.name ?? r["name"] ?? String(r);
                        const entry = (idKey && map.get(idKey)) ||
                            map.get(name) || { ranges: [] };
                        const { lookup: localLookup } =
                            buildRoomBookingsMap(bookings);
                        const ranges = (entry.ranges || []).map((rs) => {
                            const start = rs.start ? new Date(rs.start) : null;
                            const end = rs.end ? new Date(rs.end) : null;
                            if (start && end) {
                                // detect multi-day booking
                                const sameDay =
                                    start.getFullYear() === end.getFullYear() &&
                                    start.getMonth() === end.getMonth() &&
                                    start.getDate() === end.getDate();
                                let t = "";
                                if (!sameDay) {
                                    const dateOpt = {
                                        year: "numeric",
                                        month: "short",
                                        day: "numeric",
                                    };
                                    t = `${escapeHtml(
                                        start.toLocaleDateString(
                                            "id-ID",
                                            dateOpt,
                                        ),
                                    )} ${escapeHtml(
                                        start.toLocaleTimeString("id-ID", {
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        }),
                                    )} → ${escapeHtml(
                                        end.toLocaleDateString(
                                            "id-ID",
                                            dateOpt,
                                        ),
                                    )} ${escapeHtml(
                                        end.toLocaleTimeString("id-ID", {
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        }),
                                    )}`;
                                } else {
                                    t = `${escapeHtml(
                                        start.toLocaleTimeString("id-ID", {
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        }),
                                    )} - ${escapeHtml(
                                        end.toLocaleTimeString("id-ID", {
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        }),
                                    )}`;
                                }
                                // clickable, more prominent pill-style button with tooltip
                                const bookingObj =
                                    localLookup && localLookup[rs.bookingId]
                                        ? localLookup[rs.bookingId]
                                        : null;
                                const titleAttr = bookingObj
                                    ? escapeHtml(
                                          bookingObj.title ||
                                              bookingObj.purpose ||
                                              "",
                                      )
                                    : "";
                                return `<button type="button" class="btn btn-sm btn-light booking-time rounded-sm text-black fw-semibold shadow-sm me-1" data-booking-id="${escapeHtml(
                                    rs.bookingId,
                                )}" title="${titleAttr}">${t}</button>`;
                            }
                            return "-";
                        });
                        const badges = ranges.length
                            ? ranges.join("")
                            : '<span class="text-muted small">Belum ada booking</span>';
                        return `
                <div class="d-flex justify-content-between align-items-start py-1 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${escapeHtml(name)}</div>
                        <div class="small text-muted mt-1">${badges}</div>
                    </div>
                </div>
            `;
                    });
                    roomsEl.innerHTML = `<div class="list-unstyled">${rows.join(
                        "",
                    )}</div>`;

                    // attach delegated click handler to show booking modal using lookup
                    roomsEl.addEventListener("click", function (ev) {
                        const btn = ev.target.closest(".booking-time");
                        if (!btn) return;
                        const bid = btn.getAttribute("data-booking-id");
                        if (!bid) return;
                        const booking =
                            lookup && lookup[bid] ? lookup[bid] : null;
                        if (booking) {
                            // use booking start date as modal date
                            const dateStr = (
                                booking.start_at ||
                                booking.start ||
                                ""
                            ).slice(0, 10);
                            showBookingsModal(dateStr, [booking]);
                        }
                    });
                }
                function fetchAndRender(date) {
                    const iso = isoDate(date);
                    labelEl.innerText = formatLabel(date);
                    renderLoading();

                    // If we have all-time bookings from server, use them directly
                    if (allTimeBookings && allTimeBookings.length > 0) {
                        // Filter bookings for the selected date
                        const bookingsForDate = allTimeBookings.filter((b) => {
                            const start = b.start_at
                                ? new Date(b.start_at)
                                : null;
                            const end = b.end_at ? new Date(b.end_at) : null;
                            const selectedDate = new Date(iso);

                            if (!start || !end) return false;

                            // Check if booking overlaps with the selected date
                            const bookingStart = new Date(
                                start.getFullYear(),
                                start.getMonth(),
                                start.getDate(),
                            );
                            const bookingEnd = new Date(
                                end.getFullYear(),
                                end.getMonth(),
                                end.getDate(),
                            );
                            const selectedDateNorm = new Date(
                                selectedDate.getFullYear(),
                                selectedDate.getMonth(),
                                selectedDate.getDate(),
                            );

                            return (
                                bookingStart <= selectedDateNorm &&
                                bookingEnd >= selectedDateNorm
                            );
                        });

                        const { lookup } =
                            buildRoomBookingsMap(bookingsForDate);
                        renderRoomsForDate(bookingsForDate, lookup);
                        return;
                    }

                    // Fallback to API fetch if no all-time bookings available
                    const url =
                        urls.bookingsByDate +
                        "?date=" +
                        encodeURIComponent(iso);
                    // Try the prefix-specific endpoint first. If asset endpoint 404s or is not ok,
                    // fall back to the room endpoint and add type=asset when appropriate.
                    const roomFallbackUrl =
                        bookingsByDateUrl +
                        "?date=" +
                        encodeURIComponent(iso) +
                        (prefix === "assets" ? "&type=asset" : "");

                    fetch(url)
                        .then((r) => {
                            if (
                                (r.status === 404 || !r.ok) &&
                                prefix === "assets"
                            ) {
                                // try fallback room endpoint with type=asset
                                return fetch(roomFallbackUrl).then((rr) => {
                                    if (!rr.ok)
                                        throw new Error("Fallback failed");
                                    return rr.json();
                                });
                            }
                            if (!r.ok)
                                throw new Error("Failed to fetch bookings");
                            return r.json();
                        })
                        .then((data) => {
                            const bookings = Array.isArray(data)
                                ? data
                                : data.data || [];
                            const { lookup } = buildRoomBookingsMap(bookings);
                            renderRoomsForDate(bookings, lookup);
                        })
                        .catch((err) => {
                            console.error(
                                "Unable to load bookings for",
                                iso,
                                err,
                            );
                            roomsEl.innerHTML =
                                '<div class="text-muted">Gagal memuat data.</div>';
                        });
                }
                prevBtn.addEventListener("click", function () {
                    currentDate.setDate(currentDate.getDate() - 1);
                    fetchAndRender(currentDate);
                });
                nextBtn.addEventListener("click", function () {
                    currentDate.setDate(currentDate.getDate() + 1);
                    fetchAndRender(currentDate);
                });
                fetchAndRender(currentDate);
            });
        })();
    });
})();
