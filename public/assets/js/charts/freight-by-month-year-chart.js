document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("freightByMonthYearChart");
    if (!container) {
        return;
    }

    const labels = JSON.parse(container.dataset.labels || "[]");
    const values = JSON.parse(container.dataset.values || "[]");

    if (!values.length) {
        container.innerHTML = '<p class="text-muted fst-italic mb-0">Nenhum valor de frete registrado neste ano.</p>';
        return;
    }

    const options = {
        chart: {
            type: "bar",
            height: 320,
            toolbar: { show: false },
        },
        series: [{
            name: "Gasto com frete",
            data: values,
        }],
        xaxis: {
            categories: labels,
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return "R$ " + Number(val).toLocaleString("pt-BR");
                },
            },
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "R$ " + Number(val).toLocaleString("pt-BR", { minimumFractionDigits: 2 });
                },
            },
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
        colors: ["#3454d1"],
        responsive: [{
            breakpoint: 480,
            options: { chart: { height: 240 } },
        }],
    };

    new ApexCharts(container, options).render();
});