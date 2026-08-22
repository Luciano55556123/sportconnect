const runSafely = (name, callback) => {
    try {
        callback();
    } catch (error) {
        console.error(`Ponto Competitivo: falha ao iniciar ${name}`, error);
    }
};

const initOrganizerChart = () => {
    const chart = document.querySelector('#organizerChart');
    if (!chart || !window.Chart) {
        return;
    }

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
};

const initPaymentToggle = () => {
    const paymentToggle = document.querySelector('.js-payment-toggle');
    if (!paymentToggle) {
        return;
    }

    const pixAreas = document.querySelectorAll('.js-pix-area');
    const paymentFields = document.querySelectorAll('.js-payment-field');
    const syncPaymentFields = () => {
        const paid = paymentToggle.checked;
        pixAreas.forEach((area) => area.classList.toggle('d-none', !paid));
        paymentFields.forEach((field) => {
            const isFee = field.name === 'registration_fee';
            field.disabled = !paid && !isFee;
            if (!paid && isFee) {
                field.value = '0';
            }
        });
    };
    paymentToggle.addEventListener('change', syncPaymentFields);
    syncPaymentFields();
};

const initPixCopy = () => {
    document.querySelectorAll('.js-copy-pix').forEach((button) => {
        button.addEventListener('click', async () => {
            const originalText = button.dataset.originalText || button.textContent;
            button.dataset.originalText = originalText;
            const code = (button.getAttribute('data-pix-code') || '').trim();
            if (!code) {
                button.textContent = 'Nao foi possivel gerar o codigo PIX.';
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-outline-danger');
                return;
            }

            const showFeedback = (message, success) => {
                button.textContent = message;
                button.classList.toggle('btn-outline-primary', success);
                button.classList.toggle('btn-outline-danger', !success);
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.add('btn-outline-primary');
                    button.classList.remove('btn-outline-danger');
                }, 2500);
            };

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(code);
                } else {
                    const input = document.createElement('textarea');
                    input.value = code;
                    input.setAttribute('readonly', '');
                    input.style.position = 'fixed';
                    input.style.left = '-9999px';
                    document.body.appendChild(input);
                    input.select();
                    const copied = document.execCommand('copy');
                    input.remove();
                    if (!copied) {
                        throw new Error('Fallback copy failed');
                    }
                }
                showFeedback('PIX copiado!', true);
            } catch (error) {
                showFeedback('Nao foi possivel copiar.', false);
            }
        });
    });
};

const initCompetitionTabs = () => {
    const hashAliases = {
        'visao-geral': 'overview',
        'equipes': 'teams',
        'partidas': 'matches',
        'classificacao': 'standings',
        'rodadas': 'bracket',
        'chaveamento': 'bracket',
        'estatisticas': 'stats',
        'eventos': 'events',
        'gols': 'goals',
        'historico': 'history'
    };

    const activateBoardTab = (board, target, updateHash = true) => {
        const tabList = board.querySelector('[role="tablist"]');
        const tabs = Array.from((tabList || board).querySelectorAll('[data-tab-target]'));
        const panels = Array.from(board.querySelectorAll('[data-tab-panel]'));
        const panel = panels.find((item) => item.dataset.tabPanel === target);

        if (!panel) {
            return false;
        }

        tabs.forEach((tab) => {
            const active = tab.dataset.tabTarget === target;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.setAttribute('tabindex', active ? '0' : '-1');
        });

        panels.forEach((item) => {
            const active = item === panel;
            item.classList.toggle('is-active', active);
            item.classList.toggle('active', active);
            item.hidden = !active;
        });

        if (updateHash) {
            history.replaceState(null, '', `#${target}`);
        }

        return true;
    };

    document.querySelectorAll('.sc-board').forEach((board) => {
        const tabList = board.querySelector('[role="tablist"]');
        const tabs = Array.from((tabList || board).querySelectorAll('[data-tab-target]'));
        const panels = Array.from(board.querySelectorAll('[data-tab-panel]'));

        if (!tabs.length || !panels.length) {
            return;
        }

        tabs.forEach((tab, index) => {
            tab.setAttribute('tabindex', tab.classList.contains('active') ? '0' : '-1');
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                activateBoardTab(board, tab.dataset.tabTarget || '');
            });
            tab.addEventListener('keydown', (event) => {
                if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) {
                    return;
                }
                event.preventDefault();
                let nextIndex = index;
                if (event.key === 'ArrowRight') {
                    nextIndex = (index + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft') {
                    nextIndex = (index - 1 + tabs.length) % tabs.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = tabs.length - 1;
                }
                tabs[nextIndex].focus();
                activateBoardTab(board, tabs[nextIndex].dataset.tabTarget || '');
            });
        });

        const hashValue = window.location.hash.replace('#', '');
        const hashTarget = hashAliases[hashValue] || hashValue;
        if (hashTarget) {
            activateBoardTab(board, hashTarget, false);
        } else {
            const activeTab = tabs.find((tab) => tab.classList.contains('active')) || tabs[0];
            activateBoardTab(board, activeTab.dataset.tabTarget || '', false);
        }
    });

    document.querySelectorAll('[data-tab-target]').forEach((externalTrigger) => {
        if (externalTrigger.closest('.sc-board')) {
            return;
        }

        externalTrigger.addEventListener('click', (event) => {
            event.preventDefault();
            const target = externalTrigger.dataset.tabTarget || '';
            const board = document.querySelector('.sc-board');
            if (!board || !activateBoardTab(board, target)) {
                return;
            }
            const activeTab = board.querySelector(`[data-tab-target="${target.replace(/"/g, '\\"')}"]`);
            if (activeTab instanceof HTMLElement) {
                activeTab.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            } else {
                board.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    runSafely('grafico do organizador', initOrganizerChart);
    runSafely('campos de pagamento', initPaymentToggle);
    runSafely('copia e cola PIX', initPixCopy);
    runSafely('abas de campeonato', initCompetitionTabs);
});
