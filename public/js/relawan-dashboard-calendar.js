// relawan-dashboard-calendar.js
// Kalender ringkas pada dashboard Pengaju: tanda titik pada tanggal yang punya
// pengajuan, dan modal daftar pengajuan saat tanggal diklik.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('dashboard-calendar');
        if (!calendarEl || typeof FullCalendar === 'undefined') return;

        let markedDates = [];
        let items = [];
        try {
            markedDates = JSON.parse(calendarEl.getAttribute('data-dates') || '[]');
        } catch (e) {
            markedDates = [];
        }
        try {
            items = JSON.parse(calendarEl.getAttribute('data-items') || '[]');
        } catch (e) {
            items = [];
        }
        const markedSet = new Set(markedDates);

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDateLocal(dateStr) {
            try {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            } catch (e) {
                return dateStr;
            }
        }

        function formatTimeRange(start, end) {
            try {
                const timeOpt = { hour: '2-digit', minute: '2-digit' };
                const s = new Date(start);
                if (!end) return s.toLocaleTimeString('id-ID', timeOpt);

                const e = new Date(end);
                const sameDay = s.getFullYear() === e.getFullYear() && s.getMonth() === e.getMonth() && s.getDate() === e.getDate();
                if (sameDay) {
                    return s.toLocaleTimeString('id-ID', timeOpt) + ' - ' + e.toLocaleTimeString('id-ID', timeOpt);
                }
                const dateOpt = { day: '2-digit', month: 'short', year: 'numeric' };
                return s.toLocaleDateString('id-ID', dateOpt) + ' ' + s.toLocaleTimeString('id-ID', timeOpt) +
                    ' → ' + e.toLocaleDateString('id-ID', dateOpt) + ' ' + e.toLocaleTimeString('id-ID', timeOpt);
            } catch (e) {
                return '';
            }
        }

        function showDateModal(dateStr) {
            const labelEl = document.getElementById('pengajuan-modal-date-label');
            const listEl = document.getElementById('pengajuan-modal-list');
            if (!labelEl || !listEl) return;
            labelEl.innerText = formatDateLocal(dateStr);

            const dayStart = new Date(dateStr + 'T00:00:00');
            const dayEnd = new Date(dateStr + 'T23:59:59');
            const dayItems = items.filter(function (it) {
                const start = new Date(it.waktu_mulai);
                const end = it.waktu_selesai ? new Date(it.waktu_selesai) : start;
                return start <= dayEnd && end >= dayStart;
            });

            if (!dayItems.length) {
                listEl.innerHTML = '<p class="text-muted mb-0">Tidak ada pengajuan pada tanggal ini.</p>';
            } else {
                listEl.innerHTML = dayItems
                    .map(function (it) {
                        const chip = function (icon, text) {
                            return '<span class="badge bg-light text-dark border fw-normal"><i class="bi ' + icon + ' me-1"></i>' + escapeHtml(text) + '</span>';
                        };
                        const meta = [];
                        if (it.divisi) meta.push(chip('bi-diagram-3', it.divisi));
                        if (it.lokasi) meta.push(chip('bi-geo-alt', it.lokasi));
                        meta.push(chip('bi-people', (it.kebutuhan ?? 0) + ' Relawan'));

                        return (
                            '<a href="' + escapeHtml(it.url) + '" class="d-block border rounded-3 p-3 mb-2 text-decoration-none text-reset week-pengajuan-item">' +
                            '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">' +
                            '<div class="fw-bold min-w-0">' + escapeHtml(it.judul) + '</div>' +
                            '<span class="badge ' + escapeHtml(it.status_class) + ' flex-shrink-0"><i class="bi ' + escapeHtml(it.status_icon) + ' me-1"></i>' + escapeHtml(it.status_label) + '</span>' +
                            '</div>' +
                            '<div class="text-muted small mt-2"><i class="bi bi-clock me-1"></i>' + formatTimeRange(it.waktu_mulai, it.waktu_selesai) + '</div>' +
                            '<div class="mt-2 d-flex flex-wrap gap-2">' + meta.join('') + '</div>' +
                            '</a>'
                        );
                    })
                    .join('');
            }

            const modalEl = document.getElementById('pengajuanDateModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        function insertToolbarNav(calendar) {
            const titleEl = calendarEl.querySelector('.fc-toolbar-title');
            if (!titleEl) return;
            const centerChunk = titleEl.parentElement;
            if (centerChunk.querySelector('.calendar-nav-left') && centerChunk.querySelector('.calendar-nav-right')) return;

            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'calendar-nav-left';
            prevBtn.setAttribute('aria-label', 'Bulan sebelumnya');
            prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
            prevBtn.addEventListener('click', function () { calendar.prev(); });

            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'calendar-nav-right';
            nextBtn.setAttribute('aria-label', 'Bulan berikutnya');
            nextBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
            nextBtn.addEventListener('click', function () { calendar.next(); });

            centerChunk.insertBefore(prevBtn, titleEl);
            centerChunk.insertBefore(nextBtn, titleEl.nextSibling);
        }

        function markCells() {
            calendarEl.querySelectorAll('.fc-daygrid-day').forEach(function (cell) {
                const date = cell.getAttribute('data-date');
                const existing = cell.querySelector('.pengajuan-marker');
                if (date && markedSet.has(date)) {
                    if (!existing) {
                        const marker = document.createElement('div');
                        marker.className = 'pengajuan-marker';
                        cell.style.position = 'relative';
                        cell.appendChild(marker);
                    }
                } else if (existing) {
                    existing.remove();
                }
            });
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: '', center: 'title', right: '' },
            navLinks: false,
            locale: 'id',
            firstDay: 0,
            height: 'auto',
            dayMaxEventRows: true,
            dateClick: function (info) { showDateModal(info.dateStr); },
            datesSet: function () {
                setTimeout(function () {
                    insertToolbarNav(calendar);
                    markCells();
                }, 20);
            },
        });
        calendar.render();

        insertToolbarNav(calendar);
        markCells();
    });
})();
