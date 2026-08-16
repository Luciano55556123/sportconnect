document.addEventListener('DOMContentLoaded', () => {
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

    const paymentToggle = document.querySelector('.js-payment-toggle');
    if (paymentToggle) {
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
    }

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
});
