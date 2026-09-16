/**
 * Gráfico "Valor de frete por tipo de veículo" — rosca, dados
 * vindos dos atributos data-labels / data-values do container
 * #freightByVehicleTypeChart.
 */
document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("freightByVehicleTypeChart");
    if (!container) {
        return;
    }

    const labels = JSON.parse(container.dataset.labels || "[]");
    const values = JSON.parse(container.dataset.values || "[]");

    if (!values.length) {
        container.innerHTML = '<p class="text-muted fst-italic mb-0">Nenhum veículo registrado neste mês.</p>';
        return;
    }

    const options = {
        chart: {
            type: "donut",
            height: 300,
        },
        series: values,
        labels: labels,
        colors: ["#0e7efd", "#17c666", "#ffbc00", "#3454d1", "#ea4d4d", "#8b5cf6"],
        legend: {
            position: "bottom",
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toFixed(1) + "%";
            },
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return "R$ " + Number(value).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
                },
            },
        },
    };

    new ApexCharts(container, options).render();
});