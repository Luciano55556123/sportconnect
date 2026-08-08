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
});
