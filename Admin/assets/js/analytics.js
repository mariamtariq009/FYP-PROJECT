let kmChart, polChart, pieChart, vehicleChart;

function getVal(id) {
    return document.getElementById(id).value;
}

function formatMoney(n) {
    return Number(n || 0).toLocaleString('en-PK', { maximumFractionDigits: 0 });
}

function loadData() {
    const from = getVal('from');
    const to = getVal('to');
    const vehicle = getVal('vehicle');

    const params = new URLSearchParams();
    if (from) params.set('from', from);
    if (to) params.set('to', to);
    if (vehicle) params.set('vehicle', vehicle);

    fetch('api/get_data.php?' + params.toString(), { credentials: 'same-origin' })
        .then(res => {
            if (!res.ok) throw new Error('Failed to load analytics');
            return res.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);

            document.getElementById('total').innerText = formatMoney(data.total);
            document.getElementById('pol').innerText = formatMoney(data.pol);
            document.getElementById('repair').innerText = formatMoney(data.repair);
            document.getElementById('logs').innerText = data.logs ?? 0;

            const kmLabels = (data.km || []).map(x => x.log_date);
            const kmValues = (data.km || []).map(x => Number(x.km || 0));

            if (kmChart) kmChart.destroy();
            kmChart = new Chart(document.getElementById('kmChart'), {
                type: 'line',
                data: {
                    labels: kmLabels,
                    datasets: [{
                        label: 'KM',
                        data: kmValues,
                        borderColor: '#0d6efd',
                        tension: 0.4,
                        fill: false
                    }]
                }
            });

            const polLabels = (data.polGraph || []).map(x => x.fuel_date);
            const polValues = (data.polGraph || []).map(x => Number(x.liters || 0));

            if (polChart) polChart.destroy();
            polChart = new Chart(document.getElementById('polChart'), {
                type: 'line',
                data: {
                    labels: polLabels,
                    datasets: [{
                        label: 'Liters',
                        data: polValues,
                        borderColor: '#198754',
                        tension: 0.4
                    }]
                }
            });

            if (pieChart) pieChart.destroy();
            pieChart = new Chart(document.getElementById('pieChart'), {
                type: 'doughnut',
                data: {
                    labels: ['POL', 'Repair'],
                    datasets: [{ data: [data.pol, data.repair], backgroundColor: ['#0d6efd', '#dc3545'] }]
                }
            });

            const vLabels = (data.vehicleType || []).map(x => x.type);
            const vValues = (data.vehicleType || []).map(x => Number(x.total || 0));

            if (vehicleChart) vehicleChart.destroy();
            vehicleChart = new Chart(document.getElementById('vehicleChart'), {
                type: 'bar',
                data: {
                    labels: vLabels,
                    datasets: [{ label: 'Count', data: vValues, backgroundColor: '#6c757d' }]
                }
            });
        })
        .catch(err => console.error(err));
}

window.addEventListener('DOMContentLoaded', loadData);
