document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("freightByTypeChart");
    if (!container) {
        return;
    }

    const labels = JSON.parse(container.dataset.labels || "[]");
    const values = JSON.parse(container.dataset.values || "[]");

    if (!values.length) {
        container.innerHTML = '<p class="text-muted fst-italic mb-0">Nenhum valor de frete registrado ainda.</p>';
        return;
    }

    const options = {
        chart: {
            type: "bar",
            height: 320,
            toolbar: { show: false },
        },
        series: [{
            name: "Valor de frete",
            data: values,
        }],
        xaxis: {
            categories: labels,
            labels: {
                formatter: function (value) {
                    return "R$ " + Number(value).toLocaleString("pt-BR");
                },
            },
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                horizontal: true,
                barHeight: "45%",
            },
        },
        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return "R$ " + value.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
            },
        },
        colors: ["#0e7efd"],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: { height: 260 },
                xaxis: {
                    labels: {
                        style: { fontSize: "10px" },
                        formatter: function (value) {
                            return "R$ " + (Number(value) / 1000).toFixed(0) + "k";
                        },
                    },
                },
                dataLabels: { style: { fontSize: "10px" } },
            },
        }],
    };

    new ApexCharts(container, options).render();
});