document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("freightShareByTypeYearChart");

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
            type: "donut",
            height: 320,
        },

        series: values,
        labels: labels,

        colors: ["#0e7efd", "#17c666", "#ffbc00", "#3454d1"],

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
                    return new Intl.NumberFormat("pt-BR", {
                        style: "currency",
                        currency: "BRL"
                    }).format(value);
                }
            }
        }
    };

    new ApexCharts(container, options).render();
});