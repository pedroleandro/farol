document.addEventListener("DOMContentLoaded", function () {

    const clientSelect = document.getElementById("client_id");
    if (clientSelect) {
        new Choices(clientSelect, {
            searchEnabled: true,
            searchPlaceholderValue: "Digite para buscar o cliente...",
            noResultsText: "Nenhum cliente encontrado",
            noChoicesText: "Nenhum cliente cadastrado",
            itemSelectText: "",
            shouldSort: false,
        });
    }

    const moneyInput = document.querySelector(".money-input");
    if (moneyInput) {
        const formatMoney = (rawDigits) => {
            if (!rawDigits) return "";
            const numberValue = parseInt(rawDigits, 10) / 100;
            return numberValue.toLocaleString("pt-BR", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        if (moneyInput.value) {
            const onlyDigits = moneyInput.value.replace(/\D/g, "");
            moneyInput.value = formatMoney(onlyDigits);
        }

        moneyInput.addEventListener("input", function (event) {
            const onlyDigits = event.target.value.replace(/\D/g, "");
            event.target.value = formatMoney(onlyDigits);
        });
    }
});