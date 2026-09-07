document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("ordersByDayChart");
    if (!container) {
        return;
    }

    const labels = JSON.parse(container.dataset.labels || "[]");
    const values = JSON.parse(container.dataset.values || "[]");

    if (!values.length) {
        container.innerHTML = '<p class="text-muted fst-italic mb-0">Nenhum pedido cadastrado neste mês.</p>';
        return;
    }

    const options = {
        chart: {
            type: "bar",
            height: 300,
            toolbar: { show: false },
        },
        series: [{
            name: "Pedidos",
            data: values,
        }],
        xaxis: {
            categories: labels,
            title: { text: "Dia do mês" },
            labels: { style: { fontSize: "10px" } },
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: "55%",
            },
        },
        dataLabels: {
            enabled: false,
        },
        colors: ["#0e7efd"],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: { height: 240 },
                xaxis: { labels: { style: { fontSize: "8px" } } },
            },
        }],
    };

    new ApexCharts(container, options).render();
});