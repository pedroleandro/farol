document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("ordersByStatusChart");
    if (!container) {
        return;
    }

    const labels = JSON.parse(container.dataset.labels || "[]");
    const values = JSON.parse(container.dataset.values || "[]");

    if (!values.length) {
        container.innerHTML = '<p class="text-muted fst-italic mb-0">Nenhum pedido cadastrado ainda.</p>';
        return;
    }

    const options = {
        chart: {
            type: "donut",
            height: 320,
        },
        series: values,
        labels: labels,
        colors: ["#17c666", "#0e7efd", "#ffbc00", "#3454d1", "#ea4d4d"],
        legend: {
            position: "bottom",
        },
        dataLabels: {
            enabled: true,
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: { height: 260 },
                legend: { fontSize: "12px" },
            },
        }],
    };

    new ApexCharts(container, options).render();
});