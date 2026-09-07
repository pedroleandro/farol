function initTableSearchPagination({tableId, searchInputId, noResultsId, perPage = 10}) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const input = document.getElementById(searchInputId);
    const noResults = document.getElementById(noResultsId);
    const tbody = table.querySelector("tbody");
    const allRows = Array.from(tbody.querySelectorAll("tr"));

    let currentPage = 1;

    const pager = document.createElement("div");
    pager.className = "d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 gap-2";
    table.insertAdjacentElement("afterend", pager);

    function getFiltered() {
        const term = (input?.value || "").trim().toLowerCase();
        if (!term) return allRows;
        return allRows.filter((row) => row.textContent.toLowerCase().includes(term));
    }

    function goToPage(page, totalPages) {
        currentPage = Math.min(Math.max(page, 1), totalPages);
        render();
    }

    function makePageButton(label, page, totalPages, options = {}) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-sm " + (options.active ? "btn-primary" : "btn-outline-secondary");
        btn.textContent = label;
        btn.disabled = !!options.disabled;
        if (!options.disabled) {
            btn.addEventListener("click", () => goToPage(page, totalPages));
        }
        return btn;
    }

    function makeEllipsis() {
        const span = document.createElement("span");
        span.className = "px-1 text-muted align-self-center";
        span.textContent = "…";
        return span;
    }

    function getPageNumbers(current, total, delta = 1) {
        const range = [1, total];
        for (let i = current - delta; i <= current + delta; i++) {
            if (i > 1 && i < total) range.push(i);
        }

        const sorted = [...new Set(range)]
            .filter((n) => n >= 1 && n <= total)
            .sort((a, b) => a - b);

        const withDots = [];
        let last = null;

        sorted.forEach((page) => {
            if (last !== null) {
                if (page - last === 2) {
                    withDots.push(last + 1);
                } else if (page - last > 2) {
                    withDots.push("...");
                }
            }
            withDots.push(page);
            last = page;
        });

        return withDots;
    }

    function render() {
        const filtered = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        currentPage = Math.min(currentPage, totalPages);

        allRows.forEach((row) => (row.style.display = "none"));

        const start = (currentPage - 1) * perPage;
        filtered.slice(start, start + perPage).forEach((row) => (row.style.display = ""));

        if (noResults) {
            noResults.classList.toggle("d-none", filtered.length > 0);
        }

        pager.innerHTML = "";
        if (filtered.length === 0) {
            return;
        }

        const info = document.createElement("span");
        info.className = "text-muted small order-2 order-sm-1";
        info.textContent = `${filtered.length} registro${filtered.length !== 1 ? "s" : ""}`;
        pager.appendChild(info);

        const nav = document.createElement("div");
        nav.className = "d-flex flex-wrap align-items-center justify-content-center gap-1 order-1 order-sm-2";

        nav.appendChild(makePageButton("«", currentPage - 1, totalPages, {disabled: currentPage === 1}));

        getPageNumbers(currentPage, totalPages).forEach((page) => {
            if (page === "...") {
                nav.appendChild(makeEllipsis());
            } else {
                nav.appendChild(makePageButton(String(page), page, totalPages, {active: page === currentPage}));
            }
        });

        nav.appendChild(makePageButton("»", currentPage + 1, totalPages, {disabled: currentPage === totalPages}));

        pager.appendChild(nav);
    }

    if (input) {
        input.addEventListener("input", () => {
            currentPage = 1;
            render();
        });
    }

    render();
}