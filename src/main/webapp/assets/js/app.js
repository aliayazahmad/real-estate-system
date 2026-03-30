document.addEventListener("DOMContentLoaded", function () {
    renderCharts();
});

function renderCharts() {
    var charts = document.querySelectorAll("[data-chart='bars']");
    charts.forEach(function (chart) {
        var rawSeries = chart.getAttribute("data-series") || "";
        var rows = rawSeries.split("|").filter(Boolean).map(function (item) {
            var pair = item.split(":");
            return {
                label: pair[0] || "",
                value: Number(pair[1] || 0)
            };
        });

        if (!rows.length) {
            chart.innerHTML = "<p class='chart-empty'>No data available yet.</p>";
            return;
        }

        var maxValue = rows.reduce(function (current, row) {
            return Math.max(current, row.value);
        }, 0) || 1;

        var html = "<div class='chart-bars'>";
        rows.forEach(function (row) {
            var width = Math.max(8, Math.round((row.value / maxValue) * 100));
            html += [
                "<div class='chart-bar'>",
                "<div class='mini-row'><span>", escapeHtml(row.label), "</span><strong>", row.value, "</strong></div>",
                "<div class='chart-track'><div class='chart-fill' style='width:", width, "%'></div></div>",
                "</div>"
            ].join("");
        });
        html += "</div>";
        chart.innerHTML = html;
    });
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#39;");
}
