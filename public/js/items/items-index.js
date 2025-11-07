document.addEventListener("DOMContentLoaded", function () {
    const catEl = document.getElementById("category_search");
    if (catEl && !catEl.dataset.tsInit) {
        catEl.dataset.tsInit = "1";
        new TomSelect("#category_search", {
            valueField: "id",
            create: false,
            labelField: "text",
            plugins: ["remove_button"],
            searchField: "text",
            maxOptions: 9999,
            placeholder: "Semua kategori",
            allowEmptyOption: false,
            onChange: function (value) {
                document.getElementById("itemCategoryId").value = value;
                performSearch();
            },
        });
    }

    let currentPage = 1; // Simpan page aktif (default 1)

    // Ubah fungsi loadPage agar simpan page aktif
    async function loadPage(url) {
        const itemContainer = document.getElementById("item-container");
        const paginationContainer = document.getElementById(
            "pagination-container"
        );

        const overlay = document.createElement("div");
        overlay.className =
            "loading-overlay d-flex flex-column justify-content-center align-items-center";
        overlay.innerHTML = `
            <div class="loader-overlay text-center py-4">
                <dotlottie-wc src="https://lottie.host/bfcdecd5-f791-4410-a25e-4e1ac854a90d/b6lBLjfRT3.json"
                    style="width:100%;max-width:200px;height:auto;margin:auto;" autoplay loop></dotlottie-wc>
                <p style="color:white;text-shadow:2px 2px 6px rgba(0,0,0,0.8);">Mohon tunggu...</p>
            </div>`;
        itemContainer.appendChild(overlay);

        try {
            // Simpan URL yang dimuat
            localStorage.setItem("last_item_list_url", url);
            sessionStorage.setItem("last_item_list_url", url);

            // Ambil nomor page dari URL
            const urlObj = new URL(url);
            currentPage = parseInt(urlObj.searchParams.get("page") || 1);

            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            itemContainer.innerHTML =
                doc.querySelector("#item-container").innerHTML;
            paginationContainer.innerHTML = doc.querySelector(
                "#pagination-container"
            ).innerHTML;

            const totalElement = doc.querySelector("[data-total]");
            if (totalElement) {
                const totalCount = totalElement.dataset.total || 0;
                const originalCount = totalElement.dataset.original || 0;
                const headerTotal = document.querySelector(
                    "#header-total-items"
                );
                if (headerTotal) {
                    headerTotal.textContent = `${Number(
                        originalCount
                    ).toLocaleString("id-ID")}`;
                }
            }

            window.scrollTo({ top: 0, behavior: "smooth" });
        } catch (err) {
            console.error(err);
            overlay.innerHTML = `<div class="text-danger mt-3">Gagal memuat data.</div>`;
        } finally {
            overlay.remove();
        }
    }

    // Tombol Export PDF
    document.addEventListener("click", function (e) {
        const btn = e.target.closest("#btn-export-pdf");
        if (!btn) return;
        e.preventDefault();

        const form = document.getElementById("filter-form");
        const params = new URLSearchParams(new FormData(form)).toString();

        // Ambil URL export dari atribut data
        const baseUrl = btn.dataset.exportUrl;

        // Tambahkan page terakhir yang dimuat
        const pdfUrl = `${baseUrl}?${params}&page=${currentPage}`;
        window.open(pdfUrl, "_blank");
    });

    // 🔹 Klik pagination pakai AJAX
    document.addEventListener("click", function (e) {
        const link = e.target.closest(".page-link-ajax");
        if (!link) return;
        e.preventDefault();
        loadPage(link.href);
    });

    // 🔹 Filter form submit (tanpa reload)
    document
        .getElementById("filter-form")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            const url =
                "{{ route('items.index') }}?" +
                new URLSearchParams(new FormData(this)).toString();
            loadPage(url);
        });

    // 🔹 Simpan kondisi awal ketika pertama kali buka halaman
    if (!localStorage.getItem("last_item_list_url")) {
        localStorage.setItem("last_item_list_url", window.location.href);
    }
    if (!sessionStorage.getItem("last_item_list_url")) {
        sessionStorage.setItem("last_item_list_url", window.location.href);
    }
});

//Format Rupiah
function formatRupiah(el) {
    if (!el.value) return;
    const value = el.value.replace(/\D/g, "");
    el.value = new Intl.NumberFormat("id-ID").format(value);
}
