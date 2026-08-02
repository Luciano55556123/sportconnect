document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Confirmar acao?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[action*="/excluir"]').forEach((form) => {
        if (form.dataset.confirmBound) {
            return;
        }
        form.dataset.confirmBound = '1';
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Confirmar exclusao?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-confirm-submit]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm(button.dataset.confirmSubmit || 'Confirmar acao?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-share-current]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (navigator.share) {
                await navigator.share({ title: document.title, url: location.href });
                return;
            }
            await navigator.clipboard.writeText(location.href);
            button.classList.add('btn-success');
        });
    });

    document.querySelectorAll('[data-print-page]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    document.querySelectorAll('[data-games-section]').forEach((section) => {
        if (section.dataset.gamesReady === '1') {
            return;
        }
        section.dataset.gamesReady = '1';

        const phaseFilter = section.querySelector('[data-games-phase-filter]');
        const statusButtons = Array.from(section.querySelectorAll('[data-games-status-filter]'));
        const cards = Array.from(section.querySelectorAll('[data-game-card]'));
        const empty = section.querySelector('[data-games-filter-empty]');
        let activeStatus = 'all';

        const applyFilters = () => {
            const selectedPhase = phaseFilter ? phaseFilter.value : '';
            let visibleCount = 0;

            cards.forEach((card) => {
                const phaseMatches = selectedPhase === '' || card.dataset.phase === selectedPhase;
                const statusMatches = activeStatus === 'all' || card.dataset.status === activeStatus;
                const visible = phaseMatches && statusMatches;
                card.hidden = !visible;
                if (visible) {
                    visibleCount++;
                }
            });

            if (empty) {
                empty.classList.toggle('d-none', visibleCount > 0);
            }
        };

        statusButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeStatus = button.dataset.gamesStatusFilter || 'all';
                statusButtons.forEach((item) => {
                    const active = item === button;
                    item.classList.toggle('active', active);
                    item.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                applyFilters();
            });
        });

        phaseFilter?.addEventListener('change', applyFilters);
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
                if (button.name) {
                    return;
                }
                button.dataset.originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Enviando';
            });
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.querySelector(button.dataset.passwordToggle);
        if (!input) {
            return;
        }
        button.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            button.querySelector('i')?.classList.toggle('fa-eye-slash');
        });
    });

    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        const target = document.querySelector(input.dataset.preview);
        if (!target) {
            return;
        }
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !file.type.startsWith('image/')) {
                target.removeAttribute('src');
                target.hidden = true;
                return;
            }
            const reader = new FileReader();
            reader.addEventListener('load', () => {
                target.src = String(reader.result || '');
                target.hidden = false;
            });
            reader.readAsDataURL(file);
        });
    });

    const chart = document.querySelector('#organizerChart');
    if (chart && window.Chart) {
        new Chart(chart, {
            type: 'doughnut',
            data: {
                labels: ['Ativos', 'Encerrados'],
                datasets: [{
                    data: [Number(chart.dataset.active || 0), Number(chart.dataset.closed || 0)],
                    backgroundColor: ['#1267d8', '#ffca2c']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    const publicOverviewChart = document.querySelector('#publicOverviewChart');
    if (publicOverviewChart && window.Chart) {
        new Chart(publicOverviewChart, {
            type: 'doughnut',
            data: {
                labels: ['Realizados', 'A realizar'],
                datasets: [{
                    data: [
                        Number(publicOverviewChart.dataset.played || 0),
                        Number(publicOverviewChart.dataset.pending || 0)
                    ],
                    backgroundColor: ['#1267d8', '#ffca2c'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '68%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    document.querySelectorAll('[data-whatsapp-mask]').forEach((input) => {
        const applyMask = () => {
            const digits = input.value.replace(/\D/g, '').replace(/^55(?=\d{10,11}$)/, '').slice(0, 11);
            const ddd = digits.slice(0, 2);
            const first = digits.length > 10 ? digits.slice(2, 7) : digits.slice(2, 6);
            const second = digits.length > 10 ? digits.slice(7, 11) : digits.slice(6, 10);

            let value = ddd ? `(${ddd}` : '';
            if (digits.length >= 2) {
                value += ')';
            }
            if (first) {
                value += ` ${first}`;
            }
            if (second) {
                value += `-${second}`;
            }

            input.value = value;
        };

        applyMask();
        input.addEventListener('input', applyMask);
    });

    document.querySelectorAll('[data-competition-chart="goals"]').forEach((canvas) => {
        if (!window.Chart) {
            return;
        }
        const labels = (canvas.dataset.labels || '').split('|').filter(Boolean).slice(0, 8);
        const values = (canvas.dataset.values || '').split('|').filter(Boolean).map(Number).slice(0, 8);
        if (!labels.length || !values.some((value) => value > 0)) {
            return;
        }
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Gols',
                    data: values,
                    backgroundColor: '#1267d8',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });
});
